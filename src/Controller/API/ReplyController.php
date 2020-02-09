<?php
/**
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpo.st
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://gnu.org/licenses/>
 */

namespace qpost\Controller\API;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\NonUniqueResultException;
use qpost\Constants\FeedEntryType;
use qpost\Entity\FeedEntry;
use qpost\Service\APIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function is_null;
use function is_numeric;

class ReplyController extends AbstractController {
	/**
	 * @Route("/api/replies", methods={"GET"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 * @throws NonUniqueResultException
	 */
	public function replies(APIService $apiService) {
		$response = $apiService->validate(false);
		if (!is_null($response)) return $response;

		$parameters = $apiService->parameters();

		if ($parameters->has("feedEntry")) {
			$feedEntryId = $parameters->get("feedEntry");

			if (is_numeric($feedEntryId)) {
				$entityManager = $apiService->getEntityManager();
				$feedEntryRepository = $entityManager->getRepository(FeedEntry::class);
				$feedEntry = $feedEntryRepository->findOneBy([
					"id" => $feedEntryId
				]);

				if ($feedEntry && $apiService->mayView($feedEntry)) {
					$page = 1;
					if ($parameters->has("page")) {
						$page = $parameters->get("page");

						if (is_numeric($page)) {
							if ($page < 1) {
								return $apiService->json(["error" => "'page' has to be at least 1."], 400);
							}
						} else {
							return $apiService->json(["error" => "'page' has to be an integer."], 400);
						}
					}

					$itemsPerPage = 30;

					/**
					 * @var FeedEntry[] $feedEntries
					 */
					$feedEntries = $feedEntryRepository->createQueryBuilder("f")
						->addSelect("CASE WHEN f.user = :user THEN 1 ELSE 0 END AS HIDDEN isSameCreator")
						->setParameter("user", $feedEntry->getUser())
						->where("f.type = :reply")
						->setParameter("reply", FeedEntryType::REPLY, Type::STRING)
						->andWhere("f.parent = :parent")
						->setParameter("parent", $feedEntry)
						->setFirstResult(($page - 1) * $itemsPerPage)
						->setMaxResults($itemsPerPage)
						->addOrderBy("isSameCreator", "DESC")
						->addOrderBy("f.time", "ASC")
						->getQuery()
						->useQueryCache(true)
						->getResult();

					$replyBatches = [];

					foreach ($feedEntries as $reply) {
						if (!$apiService->mayView($reply)) continue;
						$replyBatch = [$apiService->serialize($reply)];

						while (count($replyBatch) < 5) {
							$replyBuilder = $feedEntryRepository->createQueryBuilder("f")
								->innerJoin("f.user", "u");

							if ($reply->getReplyCount() > 1) {
								$replyBuilder = $replyBuilder->where("f.user = :user")
									->setParameter("user", $reply->getUser());
							}

							$replyBuilder = $replyBuilder->andWhere("f.type = :reply")
								->setParameter("reply", FeedEntryType::REPLY, Type::STRING)
								->andWhere("f.parent = :parent")
								->setParameter("parent", $reply)
								->orderBy("f.time", "ASC")
								->setMaxResults(1);

							$reply = $replyBuilder->getQuery()
								->useQueryCache(true)
								->getOneOrNullResult();

							if (!$reply || !$apiService->mayView($reply)) break;
							$replyBatch[] = $apiService->serialize($reply);
						}

						$replyBatches[] = $replyBatch;
					}

					return $apiService->json(["results" => $replyBatches]);
				} else {
					return $apiService->json(["error" => "The requested resource could not be found."], 404);
				}
			} else {
				return $apiService->json(["error" => "'feedEntry' has to be an integer."], 400);
			}
		} else {
			return $apiService->json(["error" => "'feedEntry' is required."], 400);
		}
	}
}
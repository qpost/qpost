<?php
/**
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpostapp.com
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
use qpost\Constants\APIParameterType;
use qpost\Constants\FeedEntryType;
use qpost\Entity\FeedEntry;
use qpost\Exception\InvalidParameterIntegerRangeException;
use qpost\Exception\InvalidParameterTypeException;
use qpost\Exception\MissingParameterException;
use qpost\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function count;
use function intval;

/**
 * @Route("/api")
 */
class ReplyController extends APIController {
	/**
	 * @Route("/replies", methods={"GET"})
	 *
	 * @return Response|null
	 * @throws NonUniqueResultException
	 * @throws ResourceNotFoundException
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws MissingParameterException
	 */
	public function replies() {
		$parameters = $this->parameters();
		$feedEntry = $this->feedEntry("feedEntry");
		$feedEntryRepository = $this->entityManager->getRepository(FeedEntry::class);

		if ($feedEntry->getType() !== FeedEntryType::POST && $feedEntry->getType() !== FeedEntryType::REPLY) {
			throw new ResourceNotFoundException();
		}

		$this->validateParameterType("page", APIParameterType::INTEGER);
		$this->validateParameterIntegerRange("page", 1);

		$page = $parameters->has("page") ? intval($parameters->get("page")) : 1;

		$itemsPerPage = 30;

		/**
		 * @var FeedEntry[] $feedEntries
		 */
		$feedEntries = $this->filterFeedEntries($feedEntryRepository->createQueryBuilder("f")
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
			->getResult());

		$replyBatches = [];

		foreach ($feedEntries as $reply) {
			$replyBatch = [$this->apiService->serialize($reply)];

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

				if (!$reply || !$this->apiService->mayView($reply)) break;
				$replyBatch[] = $this->apiService->serialize($reply);
			}

			$replyBatches[] = $replyBatch;
		}

		return $this->apiService->json($replyBatches);
	}
}
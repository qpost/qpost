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

use qpost\Constants\MiscConstants;
use qpost\Entity\User;
use qpost\Service\APIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function is_int;
use function is_null;
use function is_numeric;

class FollowersYouKnowController extends AbstractController {
	/**
	 * @Route("/api/followersyouknow", methods={"GET"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function followersYouKnow(APIService $apiService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$user = $apiService->getUser();
		$parameters = $apiService->parameters();

		if ($parameters->has("target")) {
			$offset = 0;
			if ($parameters->has("offset")) {
				$offset = $parameters->get("offset");

				if (is_numeric($offset) && ($offset = (int)$offset) && is_int($offset)) {
					if ($offset < 0) {
						return $apiService->json(["error" => "'offset' has to be at least 0."], 400);
					}
				} else {
					return $apiService->json(["error" => "'offset' has to be an integer."], 400);
				}
			}

			$limit = 30;
			if ($parameters->has("limit")) {
				$limit = $parameters->get("limit");

				if (is_numeric($limit) && ($limit = (int)$limit) && is_int($limit)) {
					if ($limit < 0 || $limit > 30) {
						return $apiService->json(["error" => "'limit' has to be between 1 and 30."], 400);
					}
				} else {
					return $apiService->json(["error" => "'limit' has to be an integer."], 400);
				}
			}

			$entityManager = $apiService->getEntityManager();
			$userRepository = $entityManager->getRepository(User::class);

			$target = $userRepository->findOneBy([
				"id" => $parameters->get("target")
			]);

			if ($target && $apiService->mayView($target)) {
				/**
				 * @var User[] $users
				 */
				$users = $userRepository->createQueryBuilder("u")
					->innerJoin("u.following", "t")
					->innerJoin("u.followers", "f")
					->where("t.receiver = :target")
					->andWhere("f.sender = :user")
					->setParameter("target", $target)
					->setParameter("user", $user)
					->setFirstResult($offset)
					->setMaxResults($limit)
					->setCacheable(true)
					->getQuery()
					->useQueryCache(true)
					->setResultCacheLifetime(MiscConstants::RESULT_CACHE_LIFETIME)
					->useResultCache(true)
					->getResult();

				$results = [];

				foreach ($users as $u) {
					$results[] = $apiService->serialize($u);
				}

				return $apiService->json(["results" => $results]);
			} else {
				return $apiService->json(["error" => "The requested resource could not be found."], 404);
			}
		} else {
			return $apiService->json(["error" => "'target' is required."], 400);
		}
	}
}
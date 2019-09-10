<?php
/**
 * Copyright (C) 2018-2019 Gigadrive - All rights reserved.
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
use Doctrine\ORM\QueryBuilder;
use qpost\Constants\FeedEntryType;
use qpost\Constants\PrivacyLevel;
use qpost\Entity\FeedEntry;
use qpost\Entity\User;
use qpost\Service\APIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function array_push;
use function is_null;
use function is_numeric;

class FeedController extends AbstractController {
	/**
	 * @Route("/api/feed", methods={"GET"})
	 *
	 * @return Response
	 */
	public function feed(APIService $apiService) {
		$entityManager = $apiService->getEntityManager();

		$parameters = $apiService->getRequest()->query;
		if ($parameters->has("max") && !$parameters->has("user")) {
			// Load older posts on home feed
			$response = $apiService->validate(true);
			if (!is_null($response)) return $response;

			$user = $apiService->getUser();

			if (is_numeric($parameters->get("max"))) {
				$results = [];

				/**
				 * @var FeedEntry[] $feedEntries
				 */
				$feedEntries = $this->homeFeedQuery($apiService, $user)
					->andWhere("f.id < :id")
					->setParameter("id", $parameters->get("max"), Type::INTEGER)
					->getQuery()
					->getResult();

				foreach ($feedEntries as $feedEntry) {
					if (!is_null($user)/* && !$feedEntry->mayView($user)*/) continue; // TODO: mayView check
					array_push($results, $apiService->serialize($feedEntry));
				}

				return $apiService->json(["results" => $results]);
			} else {
				return $apiService->json(["error" => "'max' has to be an integer."], 400);
			}
		} else if (!$parameters->has("max") && !$parameters->has("user")) {
			// Load first posts on home feed
			$response = $apiService->validate(true);
			if (!is_null($response)) return $response;

			$user = $apiService->getUser();

			$results = [];

			/**
			 * @var FeedEntry[] $feedEntries
			 */
			$feedEntries = $this->homeFeedQuery($apiService, $user)
				->getQuery()
				->getResult();

			foreach ($feedEntries as $feedEntry) {
				if (!is_null($user)/* && !$feedEntry->mayView($user)*/) continue; // TODO: mayView check
				array_push($results, $apiService->serialize($feedEntry));
			}

			return $apiService->json(["results" => $results]);
		} else if ($parameters->has("max") && $parameters->has("user")) {
			// Load older posts on profile page
			$response = $apiService->validate(false);
			if (!is_null($response)) return $response;

			if (is_numeric($parameters->get("max"))) {
				/**
				 * @var User $user
				 */
				$user = $entityManager->getRepository(User::class)->findOneBy([
					"id" => $parameters->get("user")
				]);

				if (!is_null($user)) { // TODO: Add mayView check
					$results = [];

					/**
					 * @var FeedEntry[] $feedEntries
					 */
					$feedEntries = $this->profileFeedQuery($apiService, $user)
						->andWhere("f.id < :id")
						->setParameter("id", $parameters->get("max"), Type::INTEGER)
						->getQuery()
						->getResult();

					foreach ($feedEntries as $feedEntry) {
						if (!is_null($user)/* && !$feedEntry->mayView($user)*/) continue; // TODO: mayView check
						array_push($results, $apiService->serialize($feedEntry));
					}

					return $apiService->json(["results" => $results]);
				} else {
					return $apiService->json(["error" => "The requested user could not be found."], 404);
				}
			} else {
				return $apiService->json(["error" => "'max' has to be an integer."], 400);
			}
		} else if (!$parameters->has("max") && $parameters->has("user")) {
			// Load first posts on profile page
			$response = $apiService->validate(false);
			if (!is_null($response)) return $response;

			/**
			 * @var User $user
			 */
			$user = $entityManager->getRepository(User::class)->findOneBy([
				"id" => $parameters->get("user")
			]);

			if (!is_null($user)) { // TODO: Add mayView check
				$results = [];

				/**
				 * @var FeedEntry[] $feedEntries
				 */
				$feedEntries = $this->profileFeedQuery($apiService, $user)
					->getQuery()
					->getResult();

				foreach ($feedEntries as $feedEntry) {
					if (!is_null($user)/* && !$feedEntry->mayView($user)*/) continue; // TODO: mayView check
					array_push($results, $apiService->serialize($feedEntry));
				}

				return $apiService->json(["results" => $results]);
			} else {
				return $apiService->json(["error" => "The requested user could not be found."], 404);
			}
		} else {
			return $apiService->json(["error" => "Bad request"], 400);
		}
	}

	private function homeFeedQuery(APIService $apiService, User $currentUser): QueryBuilder {
		return $apiService->getEntityManager()->getRepository(FeedEntry::class)->createQueryBuilder("f")
			->innerJoin("f.user", "u")
			->where("u.privacyLevel != :closed")
			->setParameter("closed", PrivacyLevel::CLOSED, Type::STRING)
			->andWhere("f.parent is null")
			->andWhere("f.type = :post or f.type = :share")
			->setParameter("post", FeedEntryType::POST, Type::STRING)
			->setParameter("share", FeedEntryType::SHARE, Type::STRING)
			->andWhere("exists (select 1 from qpost\Entity\Follower ff where ff.receiver = :to) or f.user = :to")
			->setParameter("to", $currentUser)
			->orderBy("f.time", "DESC")
			->setMaxResults(30)
			->setCacheable(false);
	}

	private function profileFeedQuery(APIService $apiService, User $user): QueryBuilder {
		return $apiService->getEntityManager()->getRepository(FeedEntry::class)->createQueryBuilder("f")
			->where("(f.parent is null and f.type = :post) or (f.parent is not null and f.type = :share) or (f.type = :newFollowing)")
			->setParameter("post", FeedEntryType::POST, Type::STRING)
			->setParameter("share", FeedEntryType::SHARE, Type::STRING)
			->setParameter("newFollowing", FeedEntryType::NEW_FOLLOWING, Type::STRING)
			->andWhere("f.user = :user")
			->setParameter("user", $user)
			->orderBy("f.time", "DESC")
			->setMaxResults(30)
			->setCacheable(false);
	}
}

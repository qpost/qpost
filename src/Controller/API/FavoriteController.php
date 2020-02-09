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

use DateTime;
use Doctrine\DBAL\Types\Type;
use Exception;
use qpost\Constants\FeedEntryType;
use qpost\Constants\NotificationType;
use qpost\Entity\Favorite;
use qpost\Entity\FeedEntry;
use qpost\Entity\Notification;
use qpost\Entity\User;
use qpost\Service\APIService;
use qpost\Util\Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function array_push;
use function is_null;
use function is_numeric;

class FavoriteController extends AbstractController {
	/**
	 * @Route("/api/favorite", methods={"POST"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 * @throws Exception
	 */
	public function favorite(APIService $apiService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$parameters = $apiService->parameters();
		$user = $apiService->getUser();

		if ($parameters->has("post")) {
			$postId = $parameters->get("post");

			if (!Util::isEmpty($postId)) {
				if (is_numeric($postId)) {
					$entityManager = $apiService->getEntityManager();

					$feedEntry = $entityManager->getRepository(FeedEntry::class)->getEntryById($postId);

					if (!is_null($feedEntry) && ($feedEntry->getType() === FeedEntryType::POST || $feedEntry->getType() === FeedEntryType::REPLY) && $apiService->mayView($feedEntry)) {
						$owner = $feedEntry->getUser();

						$favorite = $entityManager->getRepository(Favorite::class)->findOneBy([
							"user" => $user,
							"feedEntry" => $feedEntry
						]);

						if (is_null($favorite)) {
							$favorite = (new Favorite())
								->setUser($user)
								->setFeedEntry($feedEntry)
								->setTime(new DateTime("now"));

							if ($owner->getId() !== $user->getId() && $apiService->maySendNotifications($owner, $user)) {
								$notification = (new Notification())
									->setUser($owner)
									->setReferencedUser($user)
									->setReferencedFeedEntry($feedEntry)
									->setType(NotificationType::FAVORITE)
									->setTime(new DateTime("now"));
							}

							$entityManager->persist($favorite);

							if (isset($notification)) {
								$entityManager->persist($notification);
							}

							$entityManager->flush();

							return $apiService->json(["result" => $apiService->serialize($favorite)]);
						} else {
							return $apiService->json(["error" => "You have already favorited this post."], 409);
						}
					} else {
						return $apiService->json(["error" => "The requested post could not be found."], 404);
					}
				} else {
					return $apiService->json(["error" => "'post' has to be an integer."], 400);
				}
			} else {
				return $apiService->json(["error" => "'post' is required."], 400);
			}
		} else {
			return $apiService->json(["error" => "'post' is required."], 400);
		}
	}

	/**
	 * @Route("/api/favorite", methods={"DELETE"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function unfavorite(APIService $apiService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$parameters = $apiService->parameters();
		$user = $apiService->getUser();

		if ($parameters->has("post")) {
			$postId = $parameters->get("post");

			if (!Util::isEmpty($postId)) {
				if (is_numeric($postId)) {
					$entityManager = $apiService->getEntityManager();

					$feedEntry = $entityManager->getRepository(FeedEntry::class)->getEntryById($postId);

					if (!is_null($feedEntry) && ($feedEntry->getType() === FeedEntryType::POST || $feedEntry->getType() === FeedEntryType::REPLY)) {
						$favorite = $entityManager->getRepository(Favorite::class)->findOneBy([
							"user" => $user,
							"feedEntry" => $feedEntry
						]);

						if (!is_null($favorite)) {
							$entityManager->remove($favorite);

							$notification = $entityManager->getRepository(Notification::class)->findOneBy([
								"user" => $feedEntry->getUser(),
								"referencedFeedEntry" => $feedEntry,
								"referencedUser" => $user,
								"type" => NotificationType::FAVORITE
							]);

							if (!is_null($notification)) {
								$entityManager->remove($notification);
							}

							$entityManager->flush();

							return $apiService->json(["result" => [
								"feedEntry" => $apiService->serialize($feedEntry)
							]]);
						} else {
							return $apiService->json(["error" => "The requested resource could not be found."], 404);
						}
					} else {
						return $apiService->json(["error" => "The requested post could not be found."], 404);
					}
				} else {
					return $apiService->json(["error" => "'post' has to be an integer."], 400);
				}
			} else {
				return $apiService->json(["error" => "'post' is required."], 400);
			}
		} else {
			return $apiService->json(["error" => "'post' is required."], 400);
		}
	}

	/**
	 * @Route("/api/favorites", methods={"GET"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function favorites(APIService $apiService) {
		$response = $apiService->validate(false);
		if (!is_null($response)) return $response;

		$parameters = $apiService->parameters();

		if ($parameters->has("user")) {
			$entityManager = $apiService->getEntityManager();

			/**
			 * @var User $user
			 */
			$user = $entityManager->getRepository(User::class)->findOneBy([
				"id" => $parameters->get("user")
			]);

			if ($user && $apiService->mayView($user)) {
				$max = null;
				if ($parameters->has("max")) {
					$max = $parameters->get("max");
					if (!is_numeric($max)) {
						return $apiService->json(["error" => "'max' has to be an integer."], 400);
					}
				}

				$results = [];

				$builder = $entityManager->getRepository(Favorite::class)->createQueryBuilder("f")
					->where("f.user = :user")
					->setParameter("user", $user)
					->orderBy("f.time", "DESC")
					->setMaxResults(30)
					->setCacheable(false);

				if ($max) {
					$builder->andWhere("f.id < :id")
						->setParameter("id", $max, Type::INTEGER);
				}

				/**
				 * @var Favorite[] $favorites
				 */
				$favorites = $builder
					->getQuery()
					->useQueryCache(true)
					->getResult();

				foreach ($favorites as $favorite) {
					if (!$apiService->mayView($favorite->getFeedEntry())) continue;
					array_push($results, $apiService->serialize($favorite));
				}

				return $apiService->json(["results" => $results]);
			} else {
				return $apiService->json(["error" => "The requested resource could not be found."], 404);
			}
		} else {
			return $apiService->json(["error" => "'user' is required."], 400);
		}
	}
}

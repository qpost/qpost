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

use DateTime;
use Exception;
use qpost\Constants\FeedEntryType;
use qpost\Constants\NotificationType;
use qpost\Entity\Favorite;
use qpost\Entity\FeedEntry;
use qpost\Entity\Notification;
use qpost\Service\APIService;
use qpost\Util\Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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

					$feedEntry = $entityManager->getRepository(FeedEntry::class)->findOneBy([
						"id" => $postId,
						"type" => FeedEntryType::POST
					]);

					if (!is_null($feedEntry)) {
						$favorite = $entityManager->getRepository(Favorite::class)->findOneBy([
							"user" => $user,
							"feedEntry" => $feedEntry
						]);

						if (is_null($favorite)) {
							$favorite = (new Favorite())
								->setUser($user)
								->setFeedEntry($feedEntry)
								->setTime(new DateTime("now"));

							$notification = (new Notification())
								->setUser($feedEntry->getUser())
								->setReferencedUser($user)
								->setReferencedFeedEntry($feedEntry)
								->setType(NotificationType::FAVORITE)
								->setTime(new DateTime("now"));

							$entityManager->persist($favorite);
							$entityManager->persist($notification);

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

					$feedEntry = $entityManager->getRepository(FeedEntry::class)->findOneBy([
						"id" => $postId,
						"type" => FeedEntryType::POST
					]);

					if (!is_null($feedEntry)) {
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
}

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

use DateTime;
use Exception;
use qpost\Constants\FeedEntryType;
use qpost\Constants\NotificationType;
use qpost\Constants\PrivacyLevel;
use qpost\Entity\FeedEntry;
use qpost\Entity\Notification;
use qpost\Service\APIService;
use qpost\Util\Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function is_null;
use function is_numeric;

class ShareController extends AbstractController {
	/**
	 * @Route("/api/share", methods={"POST"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 * @throws Exception
	 */
	public function share(APIService $apiService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$parameters = $apiService->parameters();
		$user = $apiService->getUser();
		$token = $apiService->getToken();

		if ($parameters->has("post")) {
			$postId = $parameters->get("post");

			if (!Util::isEmpty($postId)) {
				if (is_numeric($postId)) {
					$entityManager = $apiService->getEntityManager();

					$feedEntry = $entityManager->getRepository(FeedEntry::class)->findOneBy([
						"id" => $postId
					]);

					if (!is_null($feedEntry) && ($feedEntry->getType() === FeedEntryType::POST || $feedEntry->getType() === FeedEntryType::REPLY) && $apiService->mayView($feedEntry)) {
						$owner = $feedEntry->getUser();

						$share = $entityManager->getRepository(FeedEntry::class)->findOneBy([
							"user" => $user,
							"parent" => $feedEntry,
							"type" => FeedEntryType::SHARE
						]);

						if (is_null($share)) {
							if ($owner->getPrivacyLevel() === PrivacyLevel::PUBLIC) {
								$share = (new FeedEntry())
									->setUser($user)
									->setParent($feedEntry)
									->setType(FeedEntryType::SHARE)
									->setToken($token)
									->setTime(new DateTime("now"));

								if ($apiService->maySendNotifications($owner, $user)) {
									$notification = (new Notification())
										->setUser($owner)
										->setReferencedUser($user)
										->setReferencedFeedEntry($feedEntry)
										->setType(NotificationType::SHARE)
										->setTime(new DateTime("now"));
								}

								$entityManager->persist($share);
								$entityManager->persist($notification);

								$entityManager->flush();

								return $apiService->json(["result" => $apiService->serialize($share)]);
							} else {
								return $apiService->json(["error" => "You can not share this post at this time."], 403);
							}
						} else {
							return $apiService->json(["error" => "You have already shared this post."], 409);
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
	 * @Route("/api/share", methods={"DELETE"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function unshare(APIService $apiService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$parameters = $apiService->parameters();
		$user = $apiService->getUser();
		$token = $apiService->getToken();

		if ($parameters->has("post")) {
			$postId = $parameters->get("post");

			if (!Util::isEmpty($postId)) {
				if (is_numeric($postId)) {
					$entityManager = $apiService->getEntityManager();

					$feedEntry = $entityManager->getRepository(FeedEntry::class)->findOneBy([
						"id" => $postId
					]);

					if (!is_null($feedEntry) && ($feedEntry->getType() === FeedEntryType::POST || $feedEntry->getType() === FeedEntryType::REPLY)) {
						$share = $entityManager->getRepository(FeedEntry::class)->findOneBy([
							"user" => $user,
							"parent" => $feedEntry,
							"type" => FeedEntryType::SHARE
						]);

						if (!is_null($share)) {
							$entityManager->remove($share);

							$notification = $entityManager->getRepository(Notification::class)->findOneBy([
								"user" => $feedEntry->getUser(),
								"referencedFeedEntry" => $feedEntry,
								"referencedUser" => $user,
								"type" => NotificationType::SHARE
							]);

							if (!is_null($notification)) {
								$entityManager->remove($notification);
							}

							$entityManager->flush();

							return $apiService->json(["result" => [
								"parent" => $apiService->serialize($feedEntry)
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
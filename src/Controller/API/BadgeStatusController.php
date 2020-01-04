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

use Exception;
use qpost\Entity\Notification;
use qpost\Service\APIService;
use qpost\Util\Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function is_null;
use function is_string;

class BadgeStatusController extends AbstractController {
	/**
	 * @Route("/api/badgestatus", methods={"GET"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 * @throws Exception
	 */
	public function info(APIService $apiService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$user = $apiService->getUser();
		$entityManager = $apiService->getEntityManager();

		return $apiService->json([
			"notifications" => $entityManager->getRepository(Notification::class)->getUnseenNotificationsCount($user),
			"messages" => 0 // TODO
		]);
	}

	/**
	 * @Route("/api/badgestatus", methods={"DELETE"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function clear(APIService $apiService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$parameters = $apiService->parameters();
		$user = $apiService->getUser();

		if ($parameters->has("type")) {
			$type = $parameters->get("type");

			if (!Util::isEmpty($type)) {
				if (is_string($type)) {
					$entityManager = $apiService->getEntityManager();

					switch ($type) {
						case "notifications":
							/**
							 * @var Notification[] $notifications
							 */
							$notifications = $entityManager->getRepository(Notification::class)->getUnseenNotifcations($user);

							foreach ($notifications as $notification) {
								$notification->setSeen(true)
									->setNotified(true);
								$entityManager->persist($notification);
							}

							$entityManager->flush();

							return $apiService->noContent();

							break;
						case "messages":
							// TODO

							return $apiService->noContent();

							break;
						default:
							return $apiService->json(["error" => "'type' has to be either 'notifications' or 'messages'."], 400);

							break;
					}
				} else {
					return $apiService->json(["error" => "'type' has to be a string."], 400);
				}
			} else {
				return $apiService->json(["error" => "'type' is required."], 400);
			}
		} else {
			return $apiService->json(["error" => "'type' is required."], 400);
		}
	}
}

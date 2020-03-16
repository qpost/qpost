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

use qpost\Constants\APIParameterType;
use qpost\Entity\Notification;
use qpost\Exception\InvalidParameterTypeException;
use qpost\Exception\InvalidTokenException;
use qpost\Exception\MissingParameterException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class BadgeStatusController extends APIController {
	/**
	 * @Route("/badgestatus", methods={"GET"})
	 *
	 * @return Response|null
	 * @throws InvalidTokenException
	 */
	public function info() {
		$this->validateAuth();
		$user = $this->apiService->getUser();

		return $this->apiService->json([
			"notifications" => $this->entityManager->getRepository(Notification::class)->getUnseenNotificationsCount($user),
			"messages" => 0 // TODO
		]);
	}

	/**
	 * @Route("/badgestatus", methods={"DELETE"})
	 *
	 * @return Response|null
	 * @throws InvalidParameterTypeException
	 * @throws MissingParameterException
	 * @throws InvalidTokenException
	 */
	public function clear() {
		$this->validateAuth();
		$this->validateParameterType("type", APIParameterType::STRING);

		$user = $this->getUser();
		$type = $this->parameters()->get("type");

		switch ($type) {
			case "notifications":
				/**
				 * @var Notification[] $notifications
				 */
				$notifications = $this->entityManager->getRepository(Notification::class)->getUnseenNotifcations($user);

				foreach ($notifications as $notification) {
					$notification->setSeen(true)
						->setNotified(true);

					$this->entityManager->persist($notification);
				}

				$this->entityManager->flush();

				return $this->response();

				break;
			case "messages":
				// TODO

				return $this->response();

				break;
			default:
				return $this->error("'type' has to be either 'notifications' or 'messages'.", Response::HTTP_BAD_REQUEST);

				break;
		}
	}
}

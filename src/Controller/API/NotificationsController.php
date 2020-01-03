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

use qpost\Entity\Notification;
use qpost\Service\APIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationsController extends AbstractController {
	/**
	 * @Route("/api/notifications", methods={"GET"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function info(APIService $apiService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$entityManager = $apiService->getEntityManager();
		$user = $apiService->getUser();

		$parameters = $apiService->parameters();

		$query = $entityManager->getRepository(Notification::class)->createQueryBuilder("n")
			->where("n.user = :user")
			->setParameter("user", $user)
			->orderBy("n.time", "DESC");

		if ($parameters->has("max")) {
			$max = $parameters->get("max");

			if (is_numeric($max) && $max > 0) {
				$query->andWhere("n.id < :max")
					->setParameter("max", $max);
			} else {
				return $apiService->json(["error" => "'max' has to be an integer."], 400);
			}
		}

		$query->setMaxResults(30);

		/**
		 * @var Notification[] $notifications
		 */
		$notifications = $query->getQuery()->useQueryCache(true)->getResult();

		$results = [];
		foreach ($notifications as $notification) {
			$referencedUser = $notification->getReferencedUser();
			if ($referencedUser && $apiService->mayView($referencedUser)) {
				$results[] = $apiService->serialize($notification);
			}

			$notification->setSeen(true);
			$entityManager->persist($notification);
		}

		$entityManager->flush();

		return $apiService->json(["results" => $results]);
	}
}
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
use qpost\Constants\NotificationType;
use qpost\Entity\Follower;
use qpost\Entity\FollowRequest;
use qpost\Entity\Notification;
use qpost\Service\APIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function array_push;
use function is_int;
use function is_null;
use function is_numeric;

class FollowRequestController extends AbstractController {
	/**
	 * @Route("/api/followRequest", methods={"GET"})
	 *
	 * @param APIService $apiService
	 * @return Response
	 */
	public function requests(APIService $apiService): Response {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$user = $apiService->getUser();
		$parameters = $apiService->parameters();

		$max = null;
		if ($parameters->has("max")) {
			$max = $parameters->get("max");
			if (!is_numeric($max)) {
				return $apiService->json(["error" => "'max' has to be an integer."], 400);
			}
		}

		$entityManager = $apiService->getEntityManager();
		$results = [];

		$builder = $entityManager->getRepository(FollowRequest::class)->createQueryBuilder("r")
			->where("r.receiver = :user")
			->setParameter("user", $user)
			->orderBy("r.time", "DESC")
			->setMaxResults(30)
			->setCacheable(false);

		if ($max) {
			$builder->andWhere("r.id < :id")
				->setParameter("id", $max, Type::INTEGER);
		}

		/**
		 * @var FollowRequest[] $requests
		 */
		$requests = $builder
			->getQuery()
			->useQueryCache(true)
			->getResult();

		foreach ($requests as $request) {
			array_push($results, $apiService->serialize($request));
		}

		return $apiService->json(["results" => $results]);
	}

	/**
	 * @Route("/api/followRequest", methods={"DELETE"})
	 *
	 * @param APIService $apiService
	 * @return Response
	 * @throws Exception
	 */
	public function delete(APIService $apiService): Response {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$user = $apiService->getUser();
		$parameters = $apiService->parameters();

		if ($parameters->has("id")) {
			$id = $parameters->get("id");

			if (is_int($id)) {
				if ($parameters->has("action")) {
					$action = $parameters->get("action");

					if ($action === "accept" || $action === "decline") {
						$accept = $action === "accept";
						$entityManager = $apiService->getEntityManager();

						$followRequest = $entityManager->getRepository(FollowRequest::class)->findOneBy([
							"id" => $id
						]);

						if ($followRequest) {
							$from = $followRequest->getSender();
							$to = $followRequest->getReceiver();

							if ($accept) {
								// create follower data
								$entityManager->persist((new Follower())
									->setSender($from)
									->setReceiver($to)
									->setTime(new DateTime("now")));

								// create notification
								$entityManager->persist((new Notification())
									->setUser($to)
									->setType(NotificationType::NEW_FOLLOWER)
									->setReferencedUser($from)
									->setSeen(false)
									->setNotified(false)
									->setTime(new DateTime("now")));
							}

							$entityManager->remove($followRequest);
							$entityManager->flush();

							return $apiService->noContent();
						} else {
							return $apiService->json(["error" => "The requested resource could not be found."], 404);
						}
					} else {
						return $apiService->json(["error" => "'action' has to be 'accept' or 'decline'."], 400);
					}
				} else {
					return $apiService->json(["error" => "'action' is required."], 400);
				}
			} else {
				return $apiService->json(["error" => "'id' has to be an integer."], 400);
			}
		} else {
			return $apiService->json(["error" => "'id' is required."], 400);
		}
	}
}
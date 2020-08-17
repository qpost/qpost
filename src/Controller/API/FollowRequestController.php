<?php
/*
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
use Doctrine\DBAL\Types\Type;
use qpost\Constants\APIParameterType;
use qpost\Constants\NotificationType;
use qpost\Entity\Follower;
use qpost\Entity\FollowRequest;
use qpost\Entity\Notification;
use qpost\Exception\InvalidParameterIntegerRangeException;
use qpost\Exception\InvalidParameterTypeException;
use qpost\Exception\InvalidTokenException;
use qpost\Exception\MissingParameterException;
use qpost\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function is_null;

/**
 * @Route("/api")
 */
class FollowRequestController extends APIController {
	/**
	 * @Route("/followRequest", methods={"GET"})
	 *
	 * @return Response
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws MissingParameterException
	 * @throws InvalidTokenException
	 */
	public function requests(): Response {
		$this->validateAuth();
		$user = $this->getUser();
		$max = $this->max();

		$builder = $this->entityManager->getRepository(FollowRequest::class)->createQueryBuilder("r")
			->where("r.receiver = :user")
			->setParameter("user", $user)
			->orderBy("r.time", "DESC")
			->setMaxResults(30)
			->setCacheable(false);

		if ($max) {
			$builder->andWhere("r.id < :id")
				->setParameter("id", $max, Type::INTEGER);
		}

		return $this->response($builder
			->getQuery()
			->useQueryCache(true)
			->getResult());
	}

	/**
	 * @Route("/followRequest", methods={"DELETE"})
	 *
	 * @return Response
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws InvalidTokenException
	 * @throws MissingParameterException
	 * @throws ResourceNotFoundException
	 */
	public function delete(): Response {
		$this->validateAuth();
		$user = $this->getUser();
		$parameters = $this->parameters();

		$this->validateParameterType("id", APIParameterType::INTEGER);
		$this->validateParameterIntegerRange("id", 0);
		$this->validateParameterType("action", APIParameterType::STRING);

		$id = $parameters->get("id");
		$action = $parameters->get("action");

		if ($action !== "accept" && $action !== "decline") {
			return $this->error("'action' has to be 'accept' or 'decline'.", Response::HTTP_BAD_REQUEST);
		}

		$accept = $action === "accept";

		$followRequest = $this->entityManager->getRepository(FollowRequest::class)->findOneBy([
			"id" => $id,
			"receiver" => $user
		]);

		if (is_null($followRequest)) {
			throw new ResourceNotFoundException();
		}

		$from = $followRequest->getSender();
		$to = $followRequest->getReceiver();

		$notification = $this->entityManager->getRepository(Notification::class)->findOneBy([
			"referencedFollowRequest" => $followRequest
		]);

		if ($notification) {
			$this->entityManager->remove($notification);
		}

		$newNotification = null;

		if ($accept) {
			// create follower data
			$this->entityManager->persist((new Follower())
				->setSender($from)
				->setReceiver($to)
				->setTime(new DateTime("now")));

			// create notification
			$newNotification = (new Notification())
				->setUser($to)
				->setType(NotificationType::NEW_FOLLOWER)
				->setReferencedUser($from)
				->setSeen(false)
				->setNotified(false)
				->setTime(new DateTime("now"));

			$this->entityManager->persist($newNotification);
		}

		$this->entityManager->remove($followRequest);
		$this->entityManager->flush();

		if (!is_null($newNotification)) {
			$this->messengerService->sendPushNotificationMessage($newNotification);
		}

		return $this->response();
	}
}
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
use qpost\Constants\FeedEntryType;
use qpost\Constants\NotificationType;
use qpost\Constants\PrivacyLevel;
use qpost\Entity\FeedEntry;
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
class ShareController extends APIController {
	/**
	 * @Route("/share", methods={"POST"})
	 *
	 * @return Response|null
	 * @throws ResourceNotFoundException
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws MissingParameterException
	 * @throws InvalidTokenException
	 */
	public function share() {
		$this->validateAuth();
		$user = $this->getUser();
		$token = $this->apiService->getToken();

		$feedEntry = $this->feedEntry("post");

		if ($feedEntry->getType() !== FeedEntryType::POST && $feedEntry->getType() !== FeedEntryType::REPLY) {
			throw new ResourceNotFoundException();
		}

		$owner = $feedEntry->getUser();

		$share = $this->entityManager->getRepository(FeedEntry::class)->findOneBy([
			"user" => $user,
			"parent" => $feedEntry,
			"type" => FeedEntryType::SHARE
		]);

		if (!is_null($share)) {
			return $this->error("You have already shared this post.", Response::HTTP_CONFLICT);
		}

		if ($owner->getId() === $user->getId()) {
			return $this->error("You can not share your own posts.", Response::HTTP_FORBIDDEN);
		}

		if ($owner->getPrivacyLevel() !== PrivacyLevel::PUBLIC) {
			return $this->error("You can not share this post at this time.", Response::HTTP_FORBIDDEN);
		}

		$share = (new FeedEntry())
			->setUser($user)
			->setParent($feedEntry)
			->setType(FeedEntryType::SHARE)
			->setToken($token)
			->setTime(new DateTime("now"));

		if ($this->apiService->maySendNotifications($owner, $user)) {
			$notification = (new Notification())
				->setUser($owner)
				->setReferencedUser($user)
				->setReferencedFeedEntry($feedEntry)
				->setType(NotificationType::SHARE)
				->setTime(new DateTime("now"));
		}

		$this->entityManager->persist($share);

		if (isset($notification)) {
			$this->entityManager->persist($notification);
		}

		$this->entityManager->flush();

		if (isset($notification)) {
			$this->messengerService->sendPushNotificationMessage($notification);
		}

		return $this->response($share);
	}

	/**
	 * @Route("/share", methods={"DELETE"})
	 *
	 * @return Response|null
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws MissingParameterException
	 * @throws ResourceNotFoundException
	 * @throws InvalidTokenException
	 */
	public function unshare() {
		$this->validateAuth();
		$user = $this->getUser();
		$feedEntry = $this->feedEntry("post");

		if ($feedEntry->getType() !== FeedEntryType::POST && $feedEntry->getType() !== FeedEntryType::REPLY) {
			throw new ResourceNotFoundException();
		}

		$share = $this->entityManager->getRepository(FeedEntry::class)->findOneBy([
			"user" => $user,
			"parent" => $feedEntry,
			"type" => FeedEntryType::SHARE
		]);

		if (is_null($share)) {
			throw new ResourceNotFoundException();
		}

		$this->entityManager->remove($share);

		$notification = $this->entityManager->getRepository(Notification::class)->findOneBy([
			"user" => $feedEntry->getUser(),
			"referencedFeedEntry" => $feedEntry,
			"referencedUser" => $user,
			"type" => NotificationType::SHARE
		]);

		if (!is_null($notification)) {
			$this->entityManager->remove($notification);
		}

		$this->entityManager->flush();

		return $this->response([
			"parent" => $feedEntry
		]);
	}
}
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
use qpost\Constants\FeedEntryType;
use qpost\Constants\NotificationType;
use qpost\Entity\Favorite;
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
class FavoriteController extends APIController {
	/**
	 * @Route("/favorite", methods={"POST"})
	 *
	 * @return Response|null
	 * @throws ResourceNotFoundException
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws MissingParameterException
	 * @throws InvalidTokenException
	 */
	public function favorite() {
		$this->validateAuth();
		$user = $this->getUser();
		$feedEntry = $this->feedEntry("post");

		if ($feedEntry->getType() !== FeedEntryType::POST && $feedEntry->getType() !== FeedEntryType::REPLY) {
			throw new ResourceNotFoundException();
		}

		$owner = $feedEntry->getUser();

		$favorite = $this->entityManager->getRepository(Favorite::class)->findOneBy([
			"user" => $user,
			"feedEntry" => $feedEntry
		]);

		if (is_null($favorite)) {
			$favorite = (new Favorite())
				->setUser($user)
				->setFeedEntry($feedEntry)
				->setTime(new DateTime("now"));

			if ($owner->getId() !== $user->getId() && $this->apiService->maySendNotifications($owner, $user)) {
				$notification = (new Notification())
					->setUser($owner)
					->setReferencedUser($user)
					->setReferencedFeedEntry($feedEntry)
					->setType(NotificationType::FAVORITE)
					->setTime(new DateTime("now"));
			}

			$this->entityManager->persist($favorite);

			if (isset($notification)) {
				$this->entityManager->persist($notification);
			}

			$this->entityManager->flush();

			if (isset($notification)) {
				$this->messengerService->sendPushNotificationMessage($notification);
			}

			return $this->response($favorite);
		} else {
			return $this->error("You have already favorited this post.", Response::HTTP_CONFLICT);
		}
	}

	/**
	 * @Route("/favorite", methods={"DELETE"})
	 *
	 * @return Response|null
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws MissingParameterException
	 * @throws ResourceNotFoundException
	 * @throws InvalidTokenException
	 */
	public function unfavorite() {
		$this->validateAuth();
		$user = $this->getUser();
		$feedEntry = $this->feedEntry("post");

		if ($feedEntry->getType() !== FeedEntryType::POST && $feedEntry->getType() !== FeedEntryType::REPLY) {
			throw new ResourceNotFoundException();
		}

		$favorite = $this->entityManager->getRepository(Favorite::class)->findOneBy([
			"user" => $user,
			"feedEntry" => $feedEntry
		]);

		if (!is_null($favorite)) {
			$this->entityManager->remove($favorite);

			$notification = $this->entityManager->getRepository(Notification::class)->findOneBy([
				"user" => $feedEntry->getUser(),
				"referencedFeedEntry" => $feedEntry,
				"referencedUser" => $user,
				"type" => NotificationType::FAVORITE
			]);

			if (!is_null($notification)) {
				$this->entityManager->remove($notification);
			}

			$this->entityManager->flush();

			return $this->response($feedEntry);
		} else {
			throw new ResourceNotFoundException();
		}
	}

	/**
	 * @Route("/favorites", methods={"GET"})
	 *
	 * @return Response|null
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws MissingParameterException
	 * @throws ResourceNotFoundException
	 */
	public function favorites() {
		$user = $this->user();
		$max = $this->max();

		$builder = $this->entityManager->getRepository(Favorite::class)->createQueryBuilder("f")
			->where("f.user = :user")
			->setParameter("user", $user)
			->orderBy("f.time", "DESC")
			->setMaxResults(30)
			->setCacheable(false);

		if ($max) {
			$builder->andWhere("f.id < :id")
				->setParameter("id", $max, Type::INTEGER);
		}

		return $this->response(
			$this->filterFavorites(
				$favorites = $builder
					->getQuery()
					->useQueryCache(true)
					->getResult()
			)
		);
	}
}

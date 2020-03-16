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

use qpost\Entity\Notification;
use qpost\Exception\InvalidParameterIntegerRangeException;
use qpost\Exception\InvalidParameterTypeException;
use qpost\Exception\InvalidTokenException;
use qpost\Exception\MissingParameterException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function is_null;

/**
 * @Route("/api")
 */
class NotificationsController extends APIController {
	/**
	 * @Route("/notifications", methods={"GET"})
	 *
	 * @return Response|null
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws MissingParameterException
	 * @throws InvalidTokenException
	 */
	public function info() {
		$this->validateAuth();
		$user = $this->getUser();

		$query = $this->entityManager->getRepository(Notification::class)->createQueryBuilder("n")
			->where("n.user = :user")
			->setParameter("user", $user)
			->orderBy("n.time", "DESC");

		$max = $this->max();
		if (!is_null($max)) {
			$query->andWhere("n.id < :max")
				->setParameter("max", $max);
		}

		$query->setMaxResults(30);

		/**
		 * @var Notification[] $notifications
		 */
		$notifications = $query
			->getQuery()
			->useQueryCache(true)
			->getResult();

		foreach ($notifications as $notification) {
			$notification->setSeen(true);
			$this->entityManager->persist($notification);
		}

		$this->entityManager->flush();

		return $this->response(
			$this->filterNotifications($notifications)
		);
	}
}
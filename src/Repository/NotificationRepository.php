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

namespace qpost\Repository;

use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Type;
use Doctrine\Persistence\ManagerRegistry;
use qpost\Entity\Notification;
use qpost\Entity\User;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Notification::class);
	}

	public function getUnseenNotificationsCount(User $user): int {
		return $this->createQueryBuilder("n")
			->select("count(n.id)")
			->where("n.user = :user")
			->setParameter("user", $user)
			->andWhere("n.seen = :seen")
			->setParameter("seen", false, Type::BOOLEAN)
			->getQuery()
			->useQueryCache(true)
			->getSingleScalarResult();
	}

	/**
	 * @param User $user
	 * @return Notification[]
	 */
	public function getUnseenNotifcations(User $user): array {
		return $this->createQueryBuilder("n")
			->where("n.user = :user")
			->setParameter("user", $user)
			->andWhere("n.seen = :seen")
			->setParameter("seen", false, Type::BOOLEAN)
			->getQuery()
			->useQueryCache(true)
			->getResult();
	}

	public function deleteStaleNotifications(): int {
		return $this->createQueryBuilder("n")
			->delete()
			->where("n.seen = true")
			->andWhere("n.time < :limit")
			->setParameter("limit", new DateTime("-2 month"))
			->getQuery()
			->execute();
	}
}

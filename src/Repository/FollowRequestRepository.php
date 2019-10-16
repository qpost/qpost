<?php
/**
 * Copyright (C) 2018-2019 Gigadrive - All rights reserved.
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

namespace qpost\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use qpost\Entity\FollowRequest;
use qpost\Entity\User;

/**
 * @method FollowRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method FollowRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method FollowRequest[]    findAll()
 * @method FollowRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FollowRequestRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, FollowRequest::class);
	}

	/**
	 * @param User $sender
	 * @param User $receiver
	 * @return bool
	 */
	public function hasSentFollowRequest(User $sender, User $receiver): bool {
		return $this->count([
				"sender" => $sender,
				"receiver" => $receiver
			]) > 0;
	}

	// /**
	//  * @return FollowRequest[] Returns an array of FollowRequest objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('f')
			->andWhere('f.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('f.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/

	/*
	public function findOneBySomeField($value): ?FollowRequest
	{
		return $this->createQueryBuilder('f')
			->andWhere('f.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
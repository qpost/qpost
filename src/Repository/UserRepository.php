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
use Doctrine\DBAL\Types\Type;
use qpost\Entity\User;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, User::class);
	}

	/**
	 * Checks whether a specific username is still available.
	 *
	 * @param string $username The username to look for.
	 * @return bool
	 */
	public function isUsernameAvailable(string $username): bool {
		return $this->count(["username" => $username]) === 0;
	}

	/**
	 * Checks whether a specific email address is still available.
	 *
	 * @param string $email The email address to look for.
	 * @return bool
	 */
	public function isEmailAvailable(string $email): bool {
		return $this->count(["email" => $email]) === 0;
	}

	/**
	 * Gets a user by it's username (case-insenstive).
	 *
	 * @param string $username
	 * @return User|null
	 */
	public function getUserByUsername(string $username): ?User {
		return $this->createQueryBuilder("u")
			->where("upper(u.username) = upper(:username)")
			->setParameter("username", $username, Type::STRING)
			->setCacheable(true)
			->getQuery()
			->getOneOrNullResult();
	}

	// /**
	//  * @return User[] Returns an array of User objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('u')
			->andWhere('u.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('u.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/

	/*
	public function findOneBySomeField($value): ?User
	{
		return $this->createQueryBuilder('u')
			->andWhere('u.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}

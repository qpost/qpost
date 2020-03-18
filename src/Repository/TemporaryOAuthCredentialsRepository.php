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

namespace qpost\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use qpost\Entity\TemporaryOAuthCredentials;
use qpost\Entity\User;

/**
 * @method TemporaryOAuthCredentials|null find($id, $lockMode = null, $lockVersion = null)
 * @method TemporaryOAuthCredentials|null findOneBy(array $criteria, array $orderBy = null)
 * @method TemporaryOAuthCredentials[]    findAll()
 * @method TemporaryOAuthCredentials[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TemporaryOAuthCredentialsRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, TemporaryOAuthCredentials::class);
	}

	public function getTemporaryCredentialsByUser(User $user): ?TemporaryOAuthCredentials {
		return $this->createQueryBuilder("c")
			->where("c.user = :user")
			->setParameter("user", $user)
			->orderBy("c.time", "DESC")
			->setMaxResults(1)
			->getQuery()
			->useQueryCache(true)
			->getOneOrNullResult();
	}
}

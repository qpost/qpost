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

namespace qpost\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use qpost\Constants\MiscConstants;
use qpost\Entity\Follower;
use qpost\Entity\User;

/**
 * @method Follower|null find($id, $lockMode = null, $lockVersion = null)
 * @method Follower|null findOneBy(array $criteria, array $orderBy = null)
 * @method Follower[]    findAll()
 * @method Follower[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FollowerRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Follower::class);
	}

	/**
	 * @param User $sender
	 * @param User $receiver
	 * @return bool
	 */
	public function isFollowing(User $sender, User $receiver): bool {
		return $this->count([
				"sender" => $sender,
				"receiver" => $receiver
			]) > 0;
	}

	public function getFollowingCount(User $user): int {
		return $this->createQueryBuilder("f")
			->select("count(f.id)")
			->where("f.sender = :user")
			->setParameter("user", $user)
			->getQuery()
			->useQueryCache(true)
			->useResultCache(true)
			->setResultCacheLifetime(MiscConstants::RESULT_CACHE_LIFETIME_SHORT)
			->getSingleScalarResult();
	}

	public function getFollowerCount(User $user): int {
		return $this->createQueryBuilder("f")
			->select("count(f.id)")
			->where("f.receiver = :user")
			->setParameter("user", $user)
			->getQuery()
			->useQueryCache(true)
			->useResultCache(true)
			->setResultCacheLifetime(MiscConstants::RESULT_CACHE_LIFETIME_SHORT)
			->getSingleScalarResult();
	}
}

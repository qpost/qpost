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
use Doctrine\DBAL\Types\Type;
use qpost\Constants\MiscConstants;
use qpost\Entity\Block;
use qpost\Entity\User;

/**
 * @method Block|null find($id, $lockMode = null, $lockVersion = null)
 * @method Block|null findOneBy(array $criteria, array $orderBy = null)
 * @method Block[]    findAll()
 * @method Block[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlockRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Block::class);
	}

	/**
	 * @param User $user
	 * @param User $target
	 * @return bool
	 */
	public function isBlocked(User $user, User $target): bool {
		return ($user->getId() === $target->getId()) ? false : $this->createQueryBuilder("b")
				->select("count(b.id)")
				->where("b.user = :user")
				->setParameter("user", $user)
				->andWhere("b.target = :target")
				->setParameter("target", $target)
				->getQuery()
				->useQueryCache(true)
				->useResultCache(true)
				->setResultCacheLifetime(MiscConstants::RESULT_CACHE_LIFETIME_SHORT)
				->getSingleScalarResult() > 0;
	}

	public function getBlocks(User $user, ?int $max = null): array {
		$builder = $this->createQueryBuilder("b")
			->where("b.user = :user")
			->setParameter("user", $user)
			->orderBy("b.time", "DESC")
			->setMaxResults(30)
			->setCacheable(false);

		if (!is_null($max)) {
			$builder->andWhere("b.id < :id")
				->setParameter("id", $max, Type::INTEGER);
		}

		return $builder
			->getQuery()
			->useQueryCache(true)
			->getResult();
	}
}

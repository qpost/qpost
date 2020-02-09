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
use qpost\Entity\TrendingHashtagData;

/**
 * @method TrendingHashtagData|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrendingHashtagData|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrendingHashtagData[]    findAll()
 * @method TrendingHashtagData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrendingHashtagDataRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, TrendingHashtagData::class);
	}

	/**
	 * @param int $limit
	 * @return TrendingHashtagData[]
	 */
	public function getTrends(int $limit = 10): array {
		return $this->createQueryBuilder("t")
			->orderBy("t.postsThisWeek", "DESC")
			->setMaxResults($limit)
			->getQuery()
			->useQueryCache(true)
			->useResultCache(true)
			->setResultCacheLifetime(MiscConstants::RESULT_CACHE_LIFETIME)
			->getResult();
	}
}

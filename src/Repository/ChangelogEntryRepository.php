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

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Gigadrive\Bundle\SymfonyExtensionsBundle\Constants\CacheConstants;
use qpost\Entity\ChangelogEntry;

/**
 * @method ChangelogEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChangelogEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChangelogEntry[]    findAll()
 * @method ChangelogEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChangelogEntryRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, ChangelogEntry::class);
	}

	/**
	 * Fetches the latest changelog entries
	 * @param int $limit The maximum amount of entries to fetch (default: 5)
	 * @return ChangelogEntry[]
	 * @author Mehdi Baaboura <mbaaboura@gigadrivegroup.com>
	 */
	public function getLatest(int $limit = 5): array {
		return $this->createQueryBuilder("e")
			->orderBy("e.time", "DESC")
			->setMaxResults($limit)
			->getQuery()
			->useQueryCache(true)
			->enableResultCache(CacheConstants::RESULT_CACHE_LIFETIME)
			->getResult();
	}
}

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
use Doctrine\DBAL\Types\Type;
use Doctrine\Persistence\ManagerRegistry;
use qpost\Constants\MiscConstants;
use qpost\Entity\LinkedAccount;
use qpost\Entity\User;

/**
 * @method LinkedAccount|null find($id, $lockMode = null, $lockVersion = null)
 * @method LinkedAccount|null findOneBy(array $criteria, array $orderBy = null)
 * @method LinkedAccount[]    findAll()
 * @method LinkedAccount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinkedAccountRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, LinkedAccount::class);
	}

	/**
	 * @param User $user
	 * @return LinkedAccount[]|null
	 */
	public function getProfileLinkedAccounts(User $user): ?array {
		return $this->createQueryBuilder("a")
			->where("a.user = :user")
			->setParameter("user", $user)
			->andWhere("a.onProfile = :onProfile")
			->setParameter("onProfile", true, Type::BOOLEAN)
			->getQuery()
			->useQueryCache(true)
			->enableResultCache(MiscConstants::RESULT_CACHE_LIFETIME_SHORT)
			->getResult();
	}
}

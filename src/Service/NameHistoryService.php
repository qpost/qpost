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

namespace qpost\Service;

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use qpost\Entity\User;
use qpost\Entity\UsernameHistoryEntry;
use function is_null;

class NameHistoryService {
	/**
	 * @var EntityManagerInterface $entityManager
	 */
	private $entityManager;

	/**
	 * @var LoggerInterface $logger
	 */
	private $logger;

	public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger) {
		$this->entityManager = $entityManager;
		$this->logger = $logger;
	}

	/**
	 * @param User $user
	 * @return UsernameHistoryEntry[]
	 */
	public function getNameHistory(User $user): array {
		$results = $this->entityManager->getRepository(UsernameHistoryEntry::class)->getEntriesByUser($user);
		if (!$results) $results = [];

		// Generate original name log entry
		if (count($results) === 0) {
			$originalName = $this->createEntry($user, $user->getUsername(), $user->getCreationIP(), $user->getTime());

			$this->entityManager->persist($originalName);
			$this->entityManager->flush();

			$results[] = $originalName;
		}

		return $results;
	}

	/**
	 * @param User $user
	 * @param string $name
	 * @param string|null $ip
	 * @param DateTimeInterface|null $time
	 * @return UsernameHistoryEntry
	 */
	public function createEntry(User $user, string $name, ?string $ip, ?DateTimeInterface $time = null): UsernameHistoryEntry {
		if (is_null($time)) {
			$time = new DateTime("now");
		}

		$entry = (new UsernameHistoryEntry())
			->setUser($user)
			->setName($name)
			->setIp($ip)
			->setTime($time);

		$this->entityManager->persist($entry);
		$this->entityManager->flush();

		return $entry;
	}

	/**
	 * @param User $user
	 * @param string $name
	 * @param DateTimeInterface|null $time
	 * @return bool
	 */
	public function isLogged(User $user, string $name, ?DateTimeInterface $time = null): bool {
		try {
			$repository = $this->entityManager->getRepository(UsernameHistoryEntry::class);

			$q = $repository->createQueryBuilder("e")
				->select("count(e.id)")
				->where("e.user = :user")
				->setParameter("user", $user)
				->andWhere("e.name = :name")
				->setParameter("name", $name, Type::STRING);

			if ($time) {
				$q = $q->andWhere("e.time = :time")
					->setParameter("time", $time, Type::DATETIME);
			}

			return $q->getQuery()
					->useQueryCache(true)
					->getSingleScalarResult() > 0;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * @return EntityManagerInterface
	 */
	public function getEntityManager(): EntityManagerInterface {
		return $this->entityManager;
	}

	/**
	 * @return LoggerInterface
	 */
	public function getLogger(): LoggerInterface {
		return $this->logger;
	}
}
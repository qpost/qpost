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

namespace qpost\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use qpost\Entity\FeedEntry;
use qpost\Entity\User;

class DataDeletionService {
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
	 * @deprecated
	 * @author Mehdi Baaboura <mbaaboura@gigadrivegroup.com>
	 */
	public function deleteUser(User $user) {
		$entityManager = $this->entityManager;

		$entityManager->remove($user);
		$entityManager->flush();
	}

	/**
	 * @param FeedEntry $feedEntry
	 * @deprecated
	 */
	public function deleteFeedEntry(FeedEntry $feedEntry) {
		$entityManager = $this->entityManager;

		$entityManager->remove($feedEntry);
		$entityManager->flush();
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
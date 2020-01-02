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

namespace qpost\Command;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use qpost\Entity\User;
use qpost\Service\DataDeletionService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function count;

class DeleteStaleUsersCommand extends Command {
	protected static $defaultName = "qpost:delete-stale-users";

	private $logger;
	private $entityManager;
	private $dataDeletionService;

	public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, DataDeletionService $dataDeletionService) {
		parent::__construct(null);

		$this->logger = $logger;
		$this->entityManager = $entityManager;
		$this->dataDeletionService = $dataDeletionService;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		/**
		 * @var User[] $users
		 */
		$users = $this->entityManager->getRepository(User::class)->createQueryBuilder("u")
			->where("u.emailActivated = false")
			->andWhere("u.time < :limit")
			->setParameter("limit", new DateTime("-14 days"))
			->getQuery()
			->getResult();

		$output->writeln("Results: " . count($users));

		foreach ($users as $user) {
			$output->writeln("Deleting: #" . $user->getId() . " - @" . $user->getUsername());
			$this->dataDeletionService->deleteUser($user);
		}

		$output->writeln("Done.");
	}
}
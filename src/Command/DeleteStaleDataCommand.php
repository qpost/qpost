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

namespace qpost\Command;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use qpost\Entity\FeedEntry;
use qpost\Entity\Notification;
use qpost\Entity\TemporaryOAuthCredentials;
use qpost\Entity\User;
use qpost\Service\DataDeletionService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteStaleDataCommand extends Command {
	protected static $defaultName = "qpost:delete-stale-data";

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
		$this->deleteStaleUsers($output);
		$this->deleteStaleShares($output);
		$this->deleteStaleNotifications($output);
		$this->deleteStaleCredentials($output);

		$output->writeln("Done.");

		return 0;
	}

	private function deleteStaleUsers(OutputInterface $output): void {
		$this->separator($output);
		$output->writeln("Deleting stale users...");

		$result = $this->entityManager->getRepository(User::class)->deleteStaleUsers();
		$output->writeln("Deleted: " . $result);
	}

	private function deleteStaleShares(OutputInterface $output): void {
		$this->separator($output);
		$output->writeln("Deleting stale shares...");

		$result = $this->entityManager->getRepository(FeedEntry::class)->deleteStaleShares();
		$output->writeln("Deleted: " . $result);
	}

	private function deleteStaleNotifications(OutputInterface $output): void {
		$this->separator($output);
		$output->writeln("Deleting stale notifications...");

		$result = $this->entityManager->getRepository(Notification::class)->deleteStaleNotifications();
		$output->writeln("Deleted: " . $result);
	}

	private function deleteStaleCredentials(OutputInterface $output): int {
		$this->separator($output);
		$output->writeln("Deleting stale temporary OAuth credentials...");

		$result = $this->entityManager->getRepository(TemporaryOAuthCredentials::class)->deleteStaleCredentials();
		$output->writeln("Deleted: " . $result);
	}

	private function separator(OutputInterface $output): void {
		$output->writeln("-----------------------");
	}
}
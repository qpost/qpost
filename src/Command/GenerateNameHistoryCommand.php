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

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use qpost\Entity\User;
use qpost\Service\NameHistoryService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateNameHistoryCommand extends Command {
	protected static $defaultName = "qpost:generate-name-history";

	private $logger;
	private $entityManager;
	private $nameHistoryService;

	public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, NameHistoryService $nameHistoryService) {
		parent::__construct(null);

		$this->logger = $logger;
		$this->entityManager = $entityManager;
		$this->nameHistoryService = $nameHistoryService;
	}

	protected function configure() {
		$this
			->setDescription("Add a short description for your command");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		/**
		 * @var User[] $users
		 */
		$users = $this->entityManager->getRepository(User::class)->findAll();

		$output->writeln("Results found: " . count($users));

		foreach ($users as $user) {
			$output->writeln("Updating user #" . $user->getId() . " (@" . $user->getUsername() . ")");

			$this->nameHistoryService->getNameHistory($user);
		}

		$output->writeln("Done.");
	}
}

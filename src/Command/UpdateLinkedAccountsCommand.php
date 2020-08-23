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

namespace qpost\Command;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use qpost\Constants\LinkedAccountService;
use qpost\Entity\LinkedAccount;
use qpost\Service\OAuth\ThirdPartyIntegrationManagerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function count;

class UpdateLinkedAccountsCommand extends Command {
	protected static $defaultName = "qpost:update-linked-accounts";

	private $logger;
	private $entityManager;
	private $integrationManagerService;

	public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, ThirdPartyIntegrationManagerService $integrationManagerService) {
		parent::__construct(null);

		$this->logger = $logger;
		$this->entityManager = $entityManager;
		$this->integrationManagerService = $integrationManagerService;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		/**
		 * @var LinkedAccount[] $accounts
		 */
		$accounts = $this->entityManager->getRepository(LinkedAccount::class)->createQueryBuilder("a")
			->orderBy("a.lastUpdate", "ASC")
			->setMaxResults(10)
			->getQuery()
			->getResult();

		$output->writeln("Results found: " . count($accounts));

		foreach ($accounts as $account) {
			if (!LinkedAccountService::isEnabled($account->getService())) {
				$output->writeln("Skipping #" . $account->getId() . " (" . $account->getService() . " by @" . $account->getUser()->getUsername() . ") due to disabled service.");
				continue;
			}

			$output->writeln("Updating #" . $account->getId() . " (" . $account->getService() . " by @" . $account->getUser()->getUsername() . ").");

			$this->integrationManagerService->getIntegrationService($account->getService())->updateIdentification($account);
		}

		$output->writeln("Done.");

		return 0;
	}
}
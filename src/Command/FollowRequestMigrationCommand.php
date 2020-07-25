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

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use qpost\Constants\NotificationType;
use qpost\Entity\FollowRequest;
use qpost\Entity\Notification;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FollowRequestMigrationCommand extends Command {
	protected static $defaultName = "qpost:follow-request-migration";

	private $logger;
	private $entityManager;

	public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager) {
		parent::__construct(null);

		$this->logger = $logger;
		$this->entityManager = $entityManager;
	}

	protected function configure() {
		$this
			->setDescription("Creates notifications for all follow request that don't have an associated notification.")
			->setHelp("Creates notifications for all follow request that don't have an associated notification.");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		foreach ($this->entityManager->getRepository(FollowRequest::class)->findAll() as $followRequest) {
			$notification = $this->entityManager->getRepository(Notification::class)->findOneBy([
				"referencedFollowRequest" => $followRequest
			]);

			if (!$notification) {
				$notification = (new Notification())
					->setUser($followRequest->getReceiver())
					->setReferencedUser($followRequest->getSender())
					->setReferencedFollowRequest($followRequest)
					->setType(NotificationType::FOLLOW_REQUEST)
					->setTime(new DateTime("now"));

				$this->entityManager->persist($notification);

				$output->writeln("Creating notification for request #" . $followRequest->getId());
			} else {
				$output->writeln("Skipping request #" . $followRequest->getId());
			}
		}

		$this->entityManager->flush();

		$output->writeln("Done.");

		return 0;
	}
}
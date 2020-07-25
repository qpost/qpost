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
use qpost\Entity\Notification;
use qpost\Service\PushNotificationService;
use qpost\Service\PushSubscriptionService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PushNotificationsWorkerCommand extends Command {
	protected static $defaultName = "qpost:push-notifications-worker";

	/**
	 * @var LoggerInterface $logger
	 */
	private $logger;

	/**
	 * @var EntityManagerInterface $entityManager
	 */
	private $entityManager;

	/**
	 * @var PushSubscriptionService $subscriptionService
	 */
	private $subscriptionService;

	/**
	 * @var PushNotificationService $notificationService
	 */
	private $notificationService;

	public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, PushSubscriptionService $subscriptionService, PushNotificationService $notificationService) {
		parent::__construct(null);

		$this->logger = $logger;
		$this->entityManager = $entityManager;
		$this->subscriptionService = $subscriptionService;
		$this->notificationService = $notificationService;
	}

	protected function configure() {
		$this
			->setDescription("Goes through the database and sends all push notifications, that are currently queued.")
			->setHelp("Goes through the database and sends all push notifications, that are currently queued.");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$output->writeln("Loading queued notifications...");

		$notificationRepository = $this->entityManager->getRepository(Notification::class);

		/**
		 * @var Notification[] $notifications
		 */
		$notifications = $notificationRepository->createQueryBuilder("n")
			->where("n.seen = false")
			->andWhere("n.notified = false")
			->orderBy("n.time", "ASC")
			->setMaxResults(15)
			->getQuery()
			->getResult();

		$output->writeln("Results: " . count($notifications));

		foreach ($notifications as $notification) {
			$output->writeln("Sending notification #" . $notification->getId() . " - " . $notification->getType() . " - @" . $notification->getUser()->getUsername());

			$this->notificationService->createPushNotification($notification);

			$output->writeln("Notification sent.");
		}

		$output->writeln("Done.");

		return 0;
	}
}
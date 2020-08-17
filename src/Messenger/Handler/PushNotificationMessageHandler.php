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

namespace qpost\Messenger\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use qpost\Entity\Notification;
use qpost\Messenger\Message\PushNotificationMessage;
use qpost\Service\PushNotificationService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use function get_class;

class PushNotificationMessageHandler implements MessageHandlerInterface {
	/**
	 * @var LoggerInterface $logger
	 */
	private $logger;

	/**
	 * @var EntityManagerInterface $entityManager
	 */
	private $entityManager;

	/**
	 * @var PushNotificationService $notificationService
	 */
	private $notificationService;

	public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, PushNotificationService $notificationService) {
		$this->logger = $logger;
		$this->entityManager = $entityManager;
		$this->notificationService = $notificationService;
	}

	public function __invoke(PushNotificationMessage $message) {
		$this->logger->info("Handling " . get_class($message) . ": " . $message->getNotificationId());

		$notification = $this->entityManager->getRepository(Notification::class)->findOneBy(["id" => $message->getNotificationId(), "notified" => false]);

		$this->notificationService->createPushNotification($notification);
	}
}
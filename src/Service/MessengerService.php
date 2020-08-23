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

use qpost\Entity\Notification;
use qpost\Messenger\Message\PushNotificationMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class MessengerService {
	/**
	 * @var MessageBusInterface $messageBus
	 */
	private $messageBus;

	public function __construct(MessageBusInterface $messageBus) {
		$this->messageBus = $messageBus;
	}

	/**
	 * @param Notification|int $notification
	 * @author Mehdi Baaboura <mbaaboura@gigadrivegroup.com>
	 */
	public function sendPushNotificationMessage($notification): void {
		if ($notification instanceof Notification) {
			$notification = $notification->getId();
		}

		$this->sendMessage((new PushNotificationMessage())->setNotificationId($notification));
	}

	/**
	 * @param $message
	 * @author Mehdi Baaboura <mbaaboura@gigadrivegroup.com>
	 */
	protected function sendMessage($message): void {
		$this->messageBus->dispatch($message);
	}
}
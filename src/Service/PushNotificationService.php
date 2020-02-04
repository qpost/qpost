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

namespace qpost\Service;

use BenTools\WebPushBundle\Model\Message\PushNotification;
use BenTools\WebPushBundle\Sender\PushMessageSender;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use qpost\Constants\NotificationType;
use qpost\Entity\Notification;
use qpost\Entity\PushSubscription;
use qpost\Factory\HttpClientFactory;
use qpost\Util\Util;
use function count;
use function substr;

class PushNotificationService {
	/**
	 * @var EntityManagerInterface $entityManager
	 */
	private $entityManager;

	/**
	 * @var LoggerInterface $logger
	 */
	private $logger;

	/**
	 * @var PushSubscriptionService $subscriptionServices
	 */
	private $subscriptionService;

	/**
	 * @var PushMessageSender $sender
	 */
	private $sender;

	public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, PushSubscriptionService $subscriptionService, PushMessageSender $sender) {
		$this->entityManager = $entityManager;
		$this->logger = $logger;
		$this->subscriptionService = $subscriptionService;
		$this->sender = $sender;
	}

	public function createPushNotification(Notification $notification): void {
		$user = $notification->getUser();

		$title = null;
		$body = null;
		$icon = null;

		$referencedUser = $notification->getReferencedUser();
		$referencedFeedEntry = $notification->getReferencedFeedEntry();

		switch ($notification->getType()) {
			case NotificationType::NEW_FOLLOWER:
				$bio = $referencedUser->getBio();
				if (is_null($bio)) $bio = "";

				$title = $referencedUser->getDisplayName() . " (@" . $referencedUser->getUsername() . ") is now following you.";
				$icon = $referencedUser->getAvatarURL();
				$body = $bio;
				break;
			case NotificationType::MENTION:
				$title = $referencedUser->getDisplayName() . " (@" . $referencedUser->getUsername() . ") mentioned you.";
				$icon = $referencedUser->getAvatarURL();
				$body = $referencedFeedEntry->getText();
				break;
			case NotificationType::FAVORITE:
				$title = $referencedUser->getDisplayName() . " (@" . $referencedUser->getUsername() . ") favorited your post.";
				$icon = $referencedUser->getAvatarURL();
				$body = $referencedFeedEntry->getText();
				break;
			case NotificationType::SHARE:
				$title = $referencedUser->getDisplayName() . " (@" . $referencedUser->getUsername() . ") shared your post.";
				$icon = $referencedUser->getAvatarURL();
				$body = $referencedFeedEntry->getText();
				break;
			case NotificationType::REPLY:
				$title = $referencedUser->getDisplayName() . " (@" . $referencedUser->getUsername() . ") replied to your post.";
				$icon = $referencedUser->getAvatarURL();
				$body = $referencedFeedEntry->getText();
				breaK;
			case NotificationType::FOLLOW_REQUEST:
				$title = $referencedUser->getDisplayName() . " (@" . $referencedUser->getUsername() . ") requested to follow you.";
				$icon = $referencedUser->getAvatarURL();
				$body = "";
				break;
		}

		if (!is_null($title) && !is_null($body) && !is_null($icon)) {
			/**
			 * @var PushSubscription[] $subscriptions
			 */
			$subscriptions = $this->subscriptionService->findByUser($user);

			if (count($subscriptions) > 0) {
				$pushNotification = new PushNotification($title, [
					PushNotification::BODY => $body,
					PushNotification::ICON => $icon
				]);

				try {
					$subscriptionsToSend = [];

					foreach ($subscriptions as $subscription) {
						$url = "https://fcm.googleapis.com/fcm/send";
						$data = $subscription->getSubscription();
						$endpoint = $data["endpoint"];

						if (isset($data["GCM"]) && Util::startsWith($endpoint, $url)) {
							$url = "https://fcm.googleapis.com/fcm/send";
							$httpClient = HttpClientFactory::create();

							$token = substr($endpoint, strlen($url) + 1);

							$httpResponse = $httpClient->request("POST", $url, [
								"json" => [
									"to" => $token,
									"notification" => [
										"title" => $title,
										"body" => $body,
										"icon" => $icon
									],
								],
								"headers" => [
									"Authorization" => "key=" . $_ENV["FIREBASE_SERVER_KEY"]
								]
							]);
						} else {
							$subscriptionsToSend[] = $subscription;
						}
					}

					if (count($subscriptionsToSend) > 0) {
						$responses = $this->sender->push($pushNotification->createMessage(), $subscriptionsToSend);

						// Delete expired subscriptions
						foreach ($responses as $response) {
							if ($response->isExpired()) {
								$this->subscriptionService->delete($response->getSubscription());
							}
						}
					}
				} catch (Exception $e) {
					$this->logger->error("An error occurred while sending push notifications.", [
						"notification" => $notification,
						"user" => $user,
						"exception" => $e
					]);
				}
			}
		}

		$notification->setNotified(true);

		$this->entityManager->persist($notification);
		$this->entityManager->flush();
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
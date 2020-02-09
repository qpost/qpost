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

use BenTools\WebPushBundle\Model\Subscription\UserSubscriptionInterface;
use BenTools\WebPushBundle\Model\Subscription\UserSubscriptionManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use qpost\Entity\PushSubscription;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use function hash;

// https://github.com/bpolaszek/webpush-bundle/blob/master/doc/02%20-%20The%20UserSubscription%20Manager.md
class PushSubscriptionService implements UserSubscriptionManagerInterface {
	/**
	 * @var EntityManagerInterface $entityManager
	 */
	private $entityManager;

	/**
	 * @var LoggerInterface $logger
	 */
	private $logger;

	/**
	 * @var TokenService $tokenService
	 */
	private $tokenService;

	public function __construct(RegistryInterface $registry, LoggerInterface $logger, TokenService $tokenService) {
		$this->entityManager = $registry->getEntityManager();
		$this->logger = $logger;
		$this->tokenService = $tokenService;
	}

	/**
	 * @inheritDoc
	 */
	public function factory(UserInterface $user, string $subscriptionHash, array $subscription, array $options = []): UserSubscriptionInterface {
		return (new PushSubscription())
			->setUser($user)
			->setSubscriptionHash($subscriptionHash)
			->setSubscription($subscription)
			->setToken($this->tokenService->getCurrentToken());
	}

	/**
	 * @inheritDoc
	 */
	public function hash(string $endpoint, UserInterface $user): string {
		return hash("sha256", $endpoint);
	}

	/**
	 * @inheritDoc
	 */
	public function getUserSubscription(UserInterface $user, string $subscriptionHash): ?UserSubscriptionInterface {
		/**
		 * @var PushSubscription $subscription
		 */
		$subscription = $this->entityManager->getRepository(PushSubscription::class)->findOneBy([
			"subscriptionHash" => $subscriptionHash
		]);

		// overwrite to avoid ghost subscriptions
		if ($subscription && $subscription->getUser()->getUsername() === $user->getUsername()) {
			$subscription->setUser($user);
		}

		return $subscription;
	}

	/**
	 * @inheritDoc
	 */
	public function findByUser(UserInterface $user): iterable {
		return $this->entityManager->getRepository(PushSubscription::class)->findBy([
			"user" => $user
		]);
	}

	/**
	 * @inheritDoc
	 */
	public function findByHash(string $subscriptionHash): iterable {
		return $this->entityManager->getRepository(PushSubscription::class)->findBy([
			"subscriptionHash" => $subscriptionHash
		]);
	}

	/**
	 * @inheritDoc
	 */
	public function save(UserSubscriptionInterface $userSubscription): void {
		$this->entityManager->persist($userSubscription);
		$this->entityManager->flush();
	}

	/**
	 * @inheritDoc
	 */
	public function delete(UserSubscriptionInterface $userSubscription): void {
		$this->entityManager->remove($userSubscription);
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
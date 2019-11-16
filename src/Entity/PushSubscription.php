<?php
/**
 * Copyright (C) 2018-2019 Gigadrive - All rights reserved.
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

namespace qpost\Entity;

use BenTools\WebPushBundle\Model\Subscription\UserSubscriptionInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="qpost\Repository\PushSubscriptionRepository")
 */
// https://github.com/bpolaszek/webpush-bundle/blob/master/doc/01%20-%20The%20UserSubscription%20Class.md
class PushSubscription implements UserSubscriptionInterface {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\User", inversedBy="pushSubscriptions")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $user;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $subscriptionHash;

	/**
	 * @ORM\Column(type="json")
	 */
	private $subscription = [];

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\Token", inversedBy="pushSubscriptions")
	 */
	private $token;

	public function getId(): ?int {
		return $this->id;
	}

	public function getUser(): UserInterface {
		return $this->user;
	}

	public function setUser(?UserInterface $user): self {
		$this->user = $user;

		return $this;
	}

	public function getSubscriptionHash(): string {
		return $this->subscriptionHash;
	}

	public function setSubscriptionHash(string $subscriptionHash): self {
		$this->subscriptionHash = $subscriptionHash;

		return $this;
	}

	public function getSubscription(): ?array {
		return $this->subscription;
	}

	public function setSubscription(array $subscription): self {
		$this->subscription = $subscription;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getEndpoint(): string {
		return $this->subscription["endpoint"];
	}

	/**
	 * @inheritDoc
	 */
	public function getPublicKey(): string {
		return $this->subscription["keys"]["p256dh"];
	}

	/**
	 * @inheritDoc
	 */
	public function getAuthToken(): string {
		return $this->subscription["keys"]["auth"];
	}

	/**
	 * Content-encoding (default: aesgcm)
	 *
	 * @return string
	 */
	public function getContentEncoding(): string {
		return $this->subscription["content-encoding"] ?? "aesgcm";
	}

	public function getToken(): ?Token {
		return $this->token;
	}

	public function setToken(?Token $token): self {
		$this->token = $token;

		return $this;
	}
}

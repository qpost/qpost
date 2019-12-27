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

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="qpost\Repository\LinkedAccountRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(columns={"user_id","service"})})
 */
class LinkedAccount {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @Serializer\Exclude()
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\User", inversedBy="linkedAccounts")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $user;

	/**
	 * @ORM\Column(type="string", length=24)
	 */
	private $service;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $linkedUserId;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $linkedUserName;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $linkedUserAvatar;

	/**
	 * @Serializer\Exclude()
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $clientId;

	/**
	 * @Serializer\Exclude()
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $clientSecret;

	/**
	 * @Serializer\Exclude()
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $accessToken;

	/**
	 * @Serializer\Exclude()
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $refreshToken;

	/**
	 * @Serializer\Exclude()
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	private $expiry;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	public function getId(): ?int {
		return $this->id;
	}

	public function getUser(): ?User {
		return $this->user;
	}

	public function setUser(?User $user): self {
		$this->user = $user;

		return $this;
	}

	public function getService(): ?string {
		return $this->service;
	}

	public function setService(string $service): self {
		$this->service = $service;

		return $this;
	}

	public function getLinkedUserId(): ?string {
		return $this->linkedUserId;
	}

	public function setLinkedUserId(?string $linkedUserId): self {
		$this->linkedUserId = $linkedUserId;

		return $this;
	}

	public function getLinkedUserName(): ?string {
		return $this->linkedUserName;
	}

	public function setLinkedUserName(?string $linkedUserName): self {
		$this->linkedUserName = $linkedUserName;

		return $this;
	}

	public function getLinkedUserAvatar(): ?string {
		return $this->linkedUserAvatar;
	}

	public function setLinkedUserAvatar(?string $linkedUserAvatar): self {
		$this->linkedUserAvatar = $linkedUserAvatar;

		return $this;
	}

	public function getClientId(): ?string {
		return $this->clientId;
	}

	public function setClientId(?string $clientId): self {
		$this->clientId = $clientId;

		return $this;
	}

	public function getClientSecret(): ?string {
		return $this->clientSecret;
	}

	public function setClientSecret(?string $clientSecret): self {
		$this->clientSecret = $clientSecret;

		return $this;
	}

	public function getAccessToken(): ?string {
		return $this->accessToken;
	}

	public function setAccessToken(?string $accessToken): self {
		$this->accessToken = $accessToken;

		return $this;
	}

	public function getRefreshToken(): ?string {
		return $this->refreshToken;
	}

	public function setRefreshToken(?string $refreshToken): self {
		$this->refreshToken = $refreshToken;

		return $this;
	}

	public function getExpiry(): ?DateTimeInterface {
		return $this->expiry;
	}

	public function setExpiry(?DateTimeInterface $expiry): self {
		$this->expiry = $expiry;

		return $this;
	}

	public function getTime(): ?DateTimeInterface {
		return $this->time;
	}

	public function setTime(DateTimeInterface $time): self {
		$this->time = $time;

		return $this;
	}
}

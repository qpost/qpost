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

/**
 * Represents the Gigadrive account data of a user.
 *
 * @ORM\Entity(repositoryClass="qpost\Repository\UserGigadriveDataRepository")
 * @ORM\Table(indexes={@ORM\Index(columns={"token"})})
 */
class UserGigadriveData {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="integer", unique=true)
	 */
	private $accountId;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $token;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $joinDate;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $lastUpdate;

	/**
	 * @ORM\OneToOne(targetEntity="qpost\Entity\User", mappedBy="gigadriveData", cascade={"persist", "remove"})
	 */
	private $user;

	/**
	 * The id of this object.
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * The Gigadrive account id of this user.
	 *
	 * @return int|null
	 */
	public function getAccountId(): ?int {
		return $this->accountId;
	}

	/**
	 * @param int $accountId
	 * @return UserGigadriveData
	 */
	public function setAccountId(int $accountId): self {
		$this->accountId = $accountId;

		return $this;
	}

	/**
	 * The Gigadrive API token of this user.
	 *
	 * @return string|null
	 */
	public function getToken(): ?string {
		return $this->token;
	}

	/**
	 * @param string $token
	 * @return UserGigadriveData
	 */
	public function setToken(string $token): self {
		$this->token = $token;

		return $this;
	}

	/**
	 * The time at which the Gigadrive account of this user was created.
	 *
	 * @return DateTimeInterface|null
	 */
	public function getJoinDate(): ?DateTimeInterface {
		return $this->joinDate;
	}

	/**
	 * @param DateTimeInterface $joinDate
	 * @return UserGigadriveData
	 */
	public function setJoinDate(DateTimeInterface $joinDate): self {
		$this->joinDate = $joinDate;

		return $this;
	}

	/**
	 * The date at which this data was last updated.
	 *
	 * @return DateTimeInterface|null
	 */
	public function getLastUpdate(): ?DateTimeInterface {
		return $this->lastUpdate;
	}

	/**
	 * @param DateTimeInterface $lastUpdate
	 * @return UserGigadriveData
	 */
	public function setLastUpdate(DateTimeInterface $lastUpdate): self {
		$this->lastUpdate = $lastUpdate;

		return $this;
	}

	/**
	 * The user that owns this data.
	 *
	 * @return User|null
	 */
	public function getUser(): ?User {
		return $this->user;
	}

	/**
	 * @param User|null $user
	 * @return UserGigadriveData
	 */
	public function setUser(?User $user): self {
		$this->user = $user;

		// set (or unset) the owning side of the relation if necessary
		$newGigadriveData = $user === null ? null : $this;
		if ($newGigadriveData !== $user->getGigadriveData()) {
			$user->setGigadriveData($newGigadriveData);
		}

		return $this;
	}
}

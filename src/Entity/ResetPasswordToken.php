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

namespace qpost\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="qpost\Repository\ResetPasswordTokenRepository")
 */
class ResetPasswordToken {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue(strategy="CUSTOM")
	 * @ORM\CustomIdGenerator(class="qpost\Database\UniqueIdGenerator")
	 * @ORM\Column(type="string", length=128)
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\User")
	 * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
	 */
	private $user;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $active = true;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	private $time_accessed;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	public function getId(): ?string {
		return $this->id;
	}

	public function getUser(): ?User {
		return $this->user;
	}

	public function setUser(?User $user): self {
		$this->user = $user;

		return $this;
	}

	public function getActive(): ?bool {
		return $this->active;
	}

	public function setActive(bool $active): self {
		$this->active = $active;

		return $this;
	}

	public function getTimeAccessed(): ?DateTimeInterface {
		return $this->time_accessed;
	}

	public function setTimeAccessed(?DateTimeInterface $time_accessed): self {
		$this->time_accessed = $time_accessed;

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

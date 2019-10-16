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
 * Represents the data of a suspension of a user.
 *
 * @ORM\Entity(repositoryClass="qpost\Repository\SuspensionRepository")
 * @ORM\Table(indexes={@ORM\Index(columns={"active"})})
 */
class Suspension {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\User", inversedBy="suspensions")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $target;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $reason;

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\User", inversedBy="createdSuspensions")
	 */
	private $staff;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $active;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * The id of this suspension object.
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * The target user of this suspension.
	 *
	 * @return User|null
	 */
	public function getTarget(): ?User {
		return $this->target;
	}

	/**
	 * @param User|null $target
	 * @return Suspension
	 */
	public function setTarget(?User $target): self {
		$this->target = $target;

		return $this;
	}

	/**
	 * The reason of this suspension.
	 *
	 * @return string|null
	 */
	public function getReason(): ?string {
		return $this->reason;
	}

	/**
	 * @param string|null $reason
	 * @return Suspension
	 */
	public function setReason(?string $reason): self {
		$this->reason = $reason;

		return $this;
	}

	/**
	 * The staff user that created this suspension.
	 *
	 * @return User|null
	 */
	public function getStaff(): ?User {
		return $this->staff;
	}

	/**
	 * @param User|null $staff
	 * @return Suspension
	 */
	public function setStaff(?User $staff): self {
		$this->staff = $staff;

		return $this;
	}

	/**
	 * Whether or not this suspension is currently active.
	 *
	 * @return bool|null
	 */
	public function isActive(): ?bool {
		return $this->active;
	}

	/**
	 * @param bool $active
	 * @return Suspension
	 */
	public function setActive(bool $active): self {
		$this->active = $active;

		return $this;
	}

	/**
	 * @return DateTimeInterface|null
	 */
	public function getTime(): ?DateTimeInterface {
		return $this->time;
	}

	/**
	 * @param DateTimeInterface $time
	 * @return Suspension
	 */
	public function setTime(DateTimeInterface $time): self {
		$this->time = $time;

		return $this;
	}
}

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

namespace qpost\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents the data of a user blocking another user.
 *
 * @ORM\Entity(repositoryClass="qpost\Repository\BlockRepository")
 */
class Block {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\User", inversedBy="blocking", fetch="EAGER")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $user;

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\User", inversedBy="blockedBy", fetch="EAGER")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $target;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * The id of this block object.
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * The user that created this block.
	 *
	 * @return User|null
	 */
	public function getUser(): ?User {
		return $this->user;
	}

	/**
	 * @param User|null $user
	 * @return Block
	 */
	public function setUser(?User $user): self {
		$this->user = $user;

		return $this;
	}

	/**
	 * The user that was blocked.
	 *
	 * @return User|null
	 */
	public function getTarget(): ?User {
		return $this->target;
	}

	/**
	 * @param User|null $target
	 * @return Block
	 */
	public function setTarget(?User $target): self {
		$this->target = $target;

		return $this;
	}

	/**
	 * The time of when this block object was created.
	 *
	 * @return DateTimeInterface|null
	 */
	public function getTime(): ?DateTimeInterface {
		return $this->time;
	}

	/**
	 * @param DateTimeInterface $time
	 * @return Block
	 */
	public function setTime(DateTimeInterface $time): self {
		$this->time = $time;

		return $this;
	}
}

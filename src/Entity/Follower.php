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
 * Represents the data of a user following another.
 *
 * @ORM\Entity(repositoryClass="qpost\Repository\FollowerRepository")
 */
class Follower {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\User", inversedBy="following", fetch="EAGER")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $sender;

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\User", inversedBy="followers", fetch="EAGER")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $receiver;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * The id of this follower object.
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * The user that follows and created this object.
	 *
	 * @return User|null
	 */
	public function getSender(): ?User {
		return $this->sender;
	}

	/**
	 * @param User|null $sender
	 * @return Follower
	 */
	public function setSender(?User $sender): self {
		$this->sender = $sender;

		return $this;
	}

	/**
	 * The user that is being followed.
	 *
	 * @return User|null
	 */
	public function getReceiver(): ?User {
		return $this->receiver;
	}

	/**
	 * @param User|null $receiver
	 * @return Follower
	 */
	public function setReceiver(?User $receiver): self {
		$this->receiver = $receiver;

		return $this;
	}

	/**
	 * The time of when this follower object was created.
	 *
	 * @return DateTimeInterface|null
	 */
	public function getTime(): ?DateTimeInterface {
		return $this->time;
	}

	/**
	 * @param DateTimeInterface $time
	 * @return Follower
	 */
	public function setTime(DateTimeInterface $time): self {
		$this->time = $time;

		return $this;
	}
}

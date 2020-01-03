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
use qpost\Constants\NotificationType;

/**
 * Represents the data of a notification.
 *
 * @ORM\Entity(repositoryClass="qpost\Repository\NotificationRepository")
 * @ORM\Table(indexes={@ORM\Index(columns={"seen"}),@ORM\Index(columns={"notified"})})
 */
class Notification {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\User", inversedBy="notifications", fetch="EAGER")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $user;

	/**
	 * @ORM\Column(type="string", length=32)
	 */
	private $type;

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\User", fetch="EAGER")
	 */
	private $referencedUser;

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\FeedEntry", fetch="EAGER")
	 */
	private $referencedFeedEntry;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $seen = false;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $notified = false;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * The id of this notification.
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * The user that received this notification.
	 *
	 * @return User|null
	 */
	public function getUser(): ?User {
		return $this->user;
	}

	/**
	 * @param User|null $user
	 * @return Notification
	 */
	public function setUser(?User $user): self {
		$this->user = $user;

		return $this;
	}

	/**
	 * The type of this notification.
	 * @return string|null
	 * @see NotificationType
	 *
	 */
	public function getType(): ?string {
		return $this->type;
	}

	/**
	 * @param string $type
	 * @return Notification
	 */
	public function setType(string $type): self {
		$this->type = $type;

		return $this;
	}

	/**
	 * The user that is referenced in this notification.
	 *
	 * @return User|null
	 */
	public function getReferencedUser(): ?User {
		return $this->referencedUser;
	}

	/**
	 * @param User|null $referencedUser
	 * @return Notification
	 */
	public function setReferencedUser(?User $referencedUser): self {
		$this->referencedUser = $referencedUser;

		return $this;
	}

	/**
	 * The feed entry that is referenced in this notification.
	 *
	 * @return FeedEntry|null
	 */
	public function getReferencedFeedEntry(): ?FeedEntry {
		return $this->referencedFeedEntry;
	}

	/**
	 * @param FeedEntry|null $referencedFeedEntry
	 * @return Notification
	 */
	public function setReferencedFeedEntry(?FeedEntry $referencedFeedEntry): self {
		$this->referencedFeedEntry = $referencedFeedEntry;

		return $this;
	}

	/**
	 * Whether or not the user has seen this notification.
	 *
	 * @return bool|null
	 */
	public function isSeen(): ?bool {
		return $this->seen;
	}

	/**
	 * @param bool $seen
	 * @return Notification
	 */
	public function setSeen(bool $seen): self {
		$this->seen = $seen;

		return $this;
	}

	/**
	 * Whether or not the user was notified of this notification.
	 *
	 * @return bool|null
	 */
	public function getNotified(): ?bool {
		return $this->notified;
	}

	/**
	 * @param bool $notified
	 * @return Notification
	 */
	public function setNotified(bool $notified): self {
		$this->notified = $notified;

		return $this;
	}

	/**
	 * The time at which this object was created.
	 *
	 * @return DateTimeInterface|null
	 */
	public function getTime(): ?DateTimeInterface {
		return $this->time;
	}

	/**
	 * @param DateTimeInterface $time
	 * @return Notification
	 */
	public function setTime(DateTimeInterface $time): self {
		$this->time = $time;

		return $this;
	}
}

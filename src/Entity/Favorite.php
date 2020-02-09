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
 * Represents the data of a user "favoriting" a post.
 *
 * @ORM\Entity(repositoryClass="qpost\Repository\FavoriteRepository")
 */
class Favorite {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\User", inversedBy="favorites", fetch="EAGER")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $user;

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\FeedEntry", inversedBy="favorites", fetch="EAGER")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $feedEntry;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * The id of this favorite object.
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * The user that created this favorite object.
	 *
	 * @return User|null
	 */
	public function getUser(): ?User {
		return $this->user;
	}

	/**
	 * @param User|null $user
	 * @return Favorite
	 */
	public function setUser(?User $user): self {
		$this->user = $user;

		return $this;
	}

	/**
	 * The feed entry that was favorited.
	 *
	 * @return FeedEntry|null
	 */
	public function getFeedEntry(): ?FeedEntry {
		return $this->feedEntry;
	}

	/**
	 * @param FeedEntry|null $feedEntry
	 * @return Favorite
	 */
	public function setFeedEntry(?FeedEntry $feedEntry): self {
		$this->feedEntry = $feedEntry;

		return $this;
	}

	/**
	 * The time of when this favorite object was created.
	 *
	 * @return DateTimeInterface|null
	 */
	public function getTime(): ?DateTimeInterface {
		return $this->time;
	}

	/**
	 * @param DateTimeInterface $time
	 * @return Favorite
	 */
	public function setTime(DateTimeInterface $time): self {
		$this->time = $time;

		return $this;
	}
}

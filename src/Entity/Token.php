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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents the data of an authorization token for a user.
 *
 * @ORM\Entity(repositoryClass="qpost\Repository\TokenRepository")
 * @ORM\Table(indexes={@ORM\Index(columns={"expiry"})})
 */
class Token {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue(strategy="CUSTOM")
	 * @ORM\CustomIdGenerator(class="qpost\Database\UniqueIdGenerator")
	 * @ORM\Column(type="string")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\User", inversedBy="tokens")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $user;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $lastIP;

	/**
	 * @ORM\Column(type="text")
	 */
	private $userAgent;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $lastAccessTime;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $expiry;

	/**
	 * @ORM\OneToMany(targetEntity="qpost\Entity\FeedEntry", mappedBy="token")
	 */
	private $feedEntries;

	public function __construct() {
		$this->feedEntries = new ArrayCollection();
	}

	/**
	 * The id of this token.
	 *
	 * @return string|null
	 */
	public function getId(): ?string {
		return $this->id;
	}

	/**
	 * The user that owns this token.
	 *
	 * @return User|null
	 */
	public function getUser(): ?User {
		return $this->user;
	}

	/**
	 * @param User|null $user
	 * @return Token
	 */
	public function setUser(?User $user): self {
		$this->user = $user;

		return $this;
	}

	/**
	 * The last IP that this token was used from.
	 *
	 * @return string|null
	 */
	public function getLastIP(): ?string {
		return $this->lastIP;
	}

	/**
	 * @param string $lastIP
	 * @return Token
	 */
	public function setLastIP(string $lastIP): self {
		$this->lastIP = $lastIP;

		return $this;
	}

	/**
	 * The user agent that this token was last used with.
	 *
	 * @return string|null
	 */
	public function getUserAgent(): ?string {
		return $this->userAgent;
	}

	/**
	 * @param string $userAgent
	 * @return Token
	 */
	public function setUserAgent(string $userAgent): self {
		$this->userAgent = $userAgent;

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
	 * @return Token
	 */
	public function setTime(DateTimeInterface $time): self {
		$this->time = $time;

		return $this;
	}

	/**
	 * The last time this token was used.
	 *
	 * @return DateTimeInterface|null
	 */
	public function getLastAccessTime(): ?DateTimeInterface {
		return $this->lastAccessTime;
	}

	/**
	 * @param DateTimeInterface $lastAccessTime
	 * @return Token
	 */
	public function setLastAccessTime(DateTimeInterface $lastAccessTime): self {
		$this->lastAccessTime = $lastAccessTime;

		return $this;
	}

	/**
	 * The time at which this token expires.
	 *
	 * @return DateTimeInterface|null
	 */
	public function getExpiry(): ?DateTimeInterface {
		return $this->expiry;
	}

	/**
	 * @param DateTimeInterface $expiry
	 * @return Token
	 */
	public function setExpiry(DateTimeInterface $expiry): self {
		$this->expiry = $expiry;

		return $this;
	}

	/**
	 * The feed entries that were created with this token.
	 *
	 * @return Collection|FeedEntry[]
	 */
	public function getFeedEntries(): Collection {
		return $this->feedEntries;
	}

	/**
	 * @param FeedEntry $feedEntry
	 * @return Token
	 */
	public function addFeedEntry(FeedEntry $feedEntry): self {
		if (!$this->feedEntries->contains($feedEntry)) {
			$this->feedEntries[] = $feedEntry;
			$feedEntry->setToken($this);
		}

		return $this;
	}

	/**
	 * @param FeedEntry $feedEntry
	 * @return Token
	 */
	public function removeFeedEntry(FeedEntry $feedEntry): self {
		if ($this->feedEntries->contains($feedEntry)) {
			$this->feedEntries->removeElement($feedEntry);
			// set the owning side to null (unless already changed)
			if ($feedEntry->getToken() === $this) {
				$feedEntry->setToken(null);
			}
		}

		return $this;
	}
}

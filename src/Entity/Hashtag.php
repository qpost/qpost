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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="qpost\Repository\HashtagRepository")
 */
class Hashtag {
	/**
	 * @ORM\Id()
	 * @ORM\Column(type="string", length=64)
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\User")
	 * @Serializer\Exclude()
	 */
	private $creator;

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\FeedEntry")
	 * @Serializer\Exclude()
	 */
	private $creatingEntry;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * @ORM\ManyToMany(targetEntity="qpost\Entity\FeedEntry", mappedBy="hashtags", fetch="EXTRA_LAZY")
	 * @Serializer\Exclude()
	 */
	private $feedEntries;

	/**
	 * @ORM\OneToOne(targetEntity="qpost\Entity\TrendingHashtagData", inversedBy="hashtag", cascade={"persist", "remove"}, fetch="EAGER")
	 * @Serializer\Exclude()
	 */
	private $trendingData;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 * @Serializer\Exclude()
	 */
	private $blacklisted = false;

	public function __construct() {
		$this->feedEntries = new ArrayCollection();
	}

	public function getId(): ?string {
		return $this->id;
	}

	public function setId(?string $id): self {
		$this->id = $id;

		return $this;
	}

	public function getCreator(): ?User {
		return $this->creator;
	}

	public function setCreator(?User $creator): self {
		$this->creator = $creator;

		return $this;
	}

	public function getCreatingEntry(): ?FeedEntry {
		return $this->creatingEntry;
	}

	public function setCreatingEntry(?FeedEntry $creatingEntry): self {
		$this->creatingEntry = $creatingEntry;

		return $this;
	}

	public function getTime(): ?DateTimeInterface {
		return $this->time;
	}

	public function setTime(DateTimeInterface $time): self {
		$this->time = $time;

		return $this;
	}

	/**
	 * @return Collection|FeedEntry[]
	 */
	public function getFeedEntries(): Collection {
		return $this->feedEntries;
	}

	public function addFeedEntry(FeedEntry $feedEntry): self {
		if (!$this->feedEntries->contains($feedEntry)) {
			$this->feedEntries[] = $feedEntry;
			$feedEntry->addHashtag($this);
		}

		return $this;
	}

	public function removeFeedEntry(FeedEntry $feedEntry): self {
		if ($this->feedEntries->contains($feedEntry)) {
			$this->feedEntries->removeElement($feedEntry);
			$feedEntry->removeHashtag($this);
		}

		return $this;
	}

	public function getTrendingData(): ?TrendingHashtagData {
		return $this->trendingData;
	}

	public function setTrendingData(?TrendingHashtagData $trendingData): self {
		$this->trendingData = $trendingData;

		return $this;
	}

	public function getBlacklisted(): ?bool {
		return $this->blacklisted;
	}

	public function setBlacklisted(?bool $blacklisted): self {
		$this->blacklisted = $blacklisted;

		return $this;
	}
}

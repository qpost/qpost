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
 * @ORM\Entity(repositoryClass="qpost\Repository\TrendingHashtagDataRepository")
 */
class TrendingHashtagData {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $postsThisWeek;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * @ORM\OneToOne(targetEntity="qpost\Entity\Hashtag", mappedBy="trendingData", cascade={"persist", "remove"})
	 */
	private $hashtag;

	public function getId(): ?int {
		return $this->id;
	}

	public function getPostsThisWeek(): ?int {
		return $this->postsThisWeek;
	}

	public function setPostsThisWeek(int $postsThisWeek): self {
		$this->postsThisWeek = $postsThisWeek;

		return $this;
	}

	public function getTime(): ?DateTimeInterface {
		return $this->time;
	}

	public function setTime(DateTimeInterface $time): self {
		$this->time = $time;

		return $this;
	}

	public function getHashtag(): ?Hashtag {
		return $this->hashtag;
	}

	public function setHashtag(?Hashtag $hashtag): self {
		$this->hashtag = $hashtag;

		// set (or unset) the owning side of the relation if necessary
		$newTrendingData = null === $hashtag ? null : $this;
		if ($hashtag->getTrendingData() !== $newTrendingData) {
			$hashtag->setTrendingData($newTrendingData);
		}

		return $this;
	}
}

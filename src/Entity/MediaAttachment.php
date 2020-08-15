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
use qpost\Repository\MediaAttachmentRepository;

/**
 * @ORM\Entity(repositoryClass=MediaAttachmentRepository::class)
 */
class MediaAttachment {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity=FeedEntry::class, inversedBy="mediaAttachments")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $feedEntry;

	/**
	 * @ORM\ManyToOne(targetEntity=MediaFile::class, inversedBy="mediaAttachments")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $mediaFile;

	/**
	 * @ORM\Column(type="integer")
	 */
	private $position;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	public function getId(): ?int {
		return $this->id;
	}

	public function getFeedEntry(): ?FeedEntry {
		return $this->feedEntry;
	}

	public function setFeedEntry(?FeedEntry $feedEntry): self {
		$this->feedEntry = $feedEntry;

		return $this;
	}

	public function getMediaFile(): ?MediaFile {
		return $this->mediaFile;
	}

	public function setMediaFile(?MediaFile $mediaFile): self {
		$this->mediaFile = $mediaFile;

		return $this;
	}

	public function getPosition(): ?int {
		return $this->position;
	}

	public function setPosition(int $position): self {
		$this->position = $position;

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

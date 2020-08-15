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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use qpost\Constants\MediaFileType;

/**
 * Represents the data of a file that was uploaded as an attachment.
 *
 * @ORM\Entity(repositoryClass="qpost\Repository\MediaFileRepository")
 * @ORM\Table(indexes={@ORM\Index(columns={"type"})})
 */
class MediaFile {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue(strategy="CUSTOM")
	 * @ORM\CustomIdGenerator(class="qpost\Database\UniqueIdGenerator")
	 * @ORM\Column(type="string")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=255, unique=true)
	 */
	private $sha256;

	/**
	 * @ORM\Column(type="string", length=255, unique=true)
	 */
	private $url;

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\User", inversedBy="uploadedFiles")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 * @Serializer\Exclude()
	 */
	private $originalUploader;

	/**
	 * @ORM\Column(type="string", length=32)
	 */
	private $type;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * @ORM\OneToMany(targetEntity=MediaAttachment::class, mappedBy="mediaFile", orphanRemoval=true)
	 */
	private $mediaAttachments;

	public function __construct() {
		$this->mediaAttachments = new ArrayCollection();
	}

	/**
	 * The id of this media file object.
	 *
	 * @return string|null
	 */
	public function getId(): ?string {
		return $this->id;
	}

	/**
	 * @param string|null $id
	 * @return $this
	 */
	public function setId(?string $id): self {
		$this->id = $id;

		return $this;
	}

	/**
	 * The hash of this file.
	 *
	 * @return string|null
	 */
	public function getSHA256(): ?string {
		return $this->sha256;
	}

	/**
	 * @param string $sha256
	 * @return MediaFile
	 */
	public function setSHA256(string $sha256): self {
		$this->sha256 = $sha256;

		return $this;
	}

	/**
	 * The URL at which this file is located.
	 *
	 * @return string|null
	 */
	public function getURL(): ?string {
		return $this->url;
	}

	/**
	 * @param string $url
	 * @return MediaFile
	 */
	public function setURL(string $url): self {
		$this->url = $url;

		return $this;
	}

	/**
	 * The user that first uploaded this file.
	 *
	 * @return User|null
	 */
	public function getOriginalUploader(): ?User {
		return $this->originalUploader;
	}

	/**
	 * @param User|null $originalUploader
	 * @return MediaFile
	 */
	public function setOriginalUploader(?User $originalUploader): self {
		$this->originalUploader = $originalUploader;

		return $this;
	}

	/**
	 * The type of this file.
	 * @return string|null
	 * @see MediaFileType
	 *
	 */
	public function getType(): ?string {
		return $this->type;
	}

	/**
	 * @param string $type
	 * @return MediaFile
	 */
	public function setType(string $type): self {
		$this->type = $type;

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
	 * @return MediaFile
	 */
	public function setTime(DateTimeInterface $time): self {
		$this->time = $time;

		return $this;
	}

	/**
	 * @return Collection|MediaAttachment[]
	 */
	public function getMediaAttachments(): Collection {
		return $this->mediaAttachments;
	}

	public function addMediaAttachment(MediaAttachment $mediaAttachment): self {
		if (!$this->mediaAttachments->contains($mediaAttachment)) {
			$this->mediaAttachments[] = $mediaAttachment;
			$mediaAttachment->setMediaFile($this);
		}

		return $this;
	}

	public function removeMediaAttachment(MediaAttachment $mediaAttachment): self {
		if ($this->mediaAttachments->contains($mediaAttachment)) {
			$this->mediaAttachments->removeElement($mediaAttachment);
			// set the owning side to null (unless already changed)
			if ($mediaAttachment->getMediaFile() === $this) {
				$mediaAttachment->setMediaFile(null);
			}
		}

		return $this;
	}
}

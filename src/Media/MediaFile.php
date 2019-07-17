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

namespace qpost\Media;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use qpost\Account\User;
use qpost\Feed\FeedEntry;

/**
 * Class MediaFile
 * @package qpost\Media
 *
 * @ORM\Entity
 */
class MediaFile {
	/**
	 * @access private
	 * @var string $id
	 *
	 * @ORM\Id()
	 * @ORM\GeneratedValue(strategy="CUSTOM")
	 * @ORM\CustomIdGenerator(class="qpost\Database\UniqueIdGenerator")
	 * @ORM\Column(type="string")
	 */
	private $id;

	/**
	 * @access private
	 * @var string $sha256
	 *
	 * @ORM\Column(type="string", unique=true)
	 */
	private $sha256;

	/**
	 * @access private
	 * @var string $url
	 *
	 * @ORM\Column(type="string")
	 */
	private $url;

	/**
	 * @access private
	 * @var User|null $originalUploader
	 *
	 * @ORM\ManyToOne(targetEntity="qpost\Account\User")
	 *
	 * @Serializer\Exclude()
	 */
	private $originalUploader;

	/**
	 * @access private
	 * @var string $type
	 *
	 * @ORM\Column(type="string")
	 */
	private $type;

	/**
	 * @access private
	 * @var DateTime $time
	 *
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * @access private
	 * @var FeedEntry[] $posts
	 *
	 * @ORM\ManyToMany(targetEntity="qpost\Feed\FeedEntry", mappedBy="attachments", cascade={"persist"})
	 *
	 * @Serializer\Exclude()
	 */
	private $posts;

	public function __construct() {
		$this->posts = new ArrayCollection();
	}

	/**
	 * Returns the media object's id
	 *
	 * @access public
	 * @return string
	 */
	public function getId(): string {
		return $this->id;
	}

	/**
	 * @param string $id
	 * @return MediaFile
	 */
	public function setId(string $id): self {
		$this->id = $id;
		return $this;
	}

	/**
	 * Returns the sha256 hash of the media object
	 *
	 * @access public
	 * @return string
	 */
	public function getSHA256(): string {
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
	 * Returns the URL on the Gigadrive CDN
	 *
	 * @access public
	 * @return string
	 */
	public function getURL(): string {
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
	 * Returns the user object of the original uploader, null if not available
	 *
	 * @access public
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
	 * Returns the media file type
	 *
	 * @access public
	 * @return string
	 */
	public function getType(){
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
	 * @return DateTime
	 */
	public function getTime(): DateTime {
		return $this->time;
	}

	/**
	 * @param DateTime $time
	 * @return MediaFile
	 */
	public function setTime(DateTime $time): self {
		$this->time = $time;
		return $this;
	}

	/**
	 * Returns an array of the IDs of the attachments
	 *
	 * @access public
	 * @return Collection|FeedEntry[]
	 */
	/*public function getPosts(): Collection {
		return $this->posts;
	}*/

	/**
	 * @param FeedEntry $entry
	 * @return MediaFile
	 */
	public function addPost(FeedEntry $entry): self {
		if (!$this->posts->contains($entry)) {
			$this->posts[] = $entry;
		}

		return $this;
	}

	/**
	 * @param FeedEntry $entry
	 * @return MediaFile
	 */
	public function removePost(FeedEntry $entry): self {
		if ($this->posts->contains($entry)) {
			$this->posts->removeElement($entry);
		}

		return $this;
	}
}
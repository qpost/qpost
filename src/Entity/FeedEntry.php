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
use qpost\Constants\FeedEntryType;
use qpost\Service\APIService;
use function is_null;

/**
 * Represents the data of a created feed entry (post, share, new follower, ...)
 * @see FeedEntryType
 *
 * @ORM\Entity(repositoryClass="qpost\Repository\FeedEntryRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(columns={"type"}),
 *     @ORM\Index(columns={"nsfw"}),
 *     @ORM\Index(columns={"time"})
 * })
 */
class FeedEntry {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\User", inversedBy="feedEntries", fetch="EAGER")
	 * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
	 */
	private $user;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $text;

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\User", fetch="EAGER")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	private $referencedUser;

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\FeedEntry", inversedBy="children", fetch="EAGER")
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 * @Serializer\Exclude()
	 */
	private $parent;

	/**
	 * @ORM\OneToMany(targetEntity="qpost\Entity\FeedEntry", mappedBy="parent", fetch="EXTRA_LAZY")
	 * @Serializer\Exclude()
	 */
	private $children;

	/**
	 * @ORM\ManyToOne(targetEntity="qpost\Entity\Token", inversedBy="feedEntries", fetch="EXTRA_LAZY")
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 * @Serializer\Exclude()
	 */
	private $token;

	/**
	 * @ORM\Column(type="string", length=32)
	 */
	private $type;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $nsfw = false;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * @Serializer\Exclude()
	 */
	private $replyCount = null;

	/**
	 * @Serializer\Exclude()
	 */
	private $shareCount = null;

	/**
	 * @Serializer\Exclude()
	 */
	private $favoriteCount = null;

	/**
	 * @Serializer\Exclude()
	 */
	private $shared = null;

	/**
	 * @Serializer\Exclude()
	 */
	private $attachments = null;

	/**
	 * @Serializer\Exclude()
	 */
	private $favorited = null;

	/**
	 * @ORM\OneToMany(targetEntity="qpost\Entity\Favorite", mappedBy="feedEntry", orphanRemoval=true, cascade={"remove"}, fetch="EXTRA_LAZY")
	 * @Serializer\Exclude()
	 */
	private $favorites;

	/**
	 * @ORM\ManyToMany(targetEntity="qpost\Entity\Hashtag", inversedBy="feedEntries", fetch="EXTRA_LAZY")
	 * @Serializer\Exclude()
	 */
	private $hashtags;

	/**
	 * @ORM\OneToMany(targetEntity=MediaAttachment::class, mappedBy="feedEntry", orphanRemoval=true)
	 * @Serializer\Exclude()
	 */
	private $mediaAttachments;

	public function __construct() {
		$this->children = new ArrayCollection();
		$this->favorites = new ArrayCollection();
		$this->hashtags = new ArrayCollection();
		$this->mediaAttachments = new ArrayCollection();
	}

	/**
	 * The id of this feed entry object.
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * @param int|null $id
	 * @return $this
	 */
	public function setId(?int $id): self {
		$this->id = $id;

		return $this;
	}

	/**
	 * The user that created this feed entry object.
	 *
	 * @return User|null
	 */
	public function getUser(): ?User {
		return $this->user;
	}

	/**
	 * @param User|null $user
	 * @return FeedEntry
	 */
	public function setUser(?User $user): self {
		$this->user = $user;

		return $this;
	}

	/**
	 * The text of this feed entry object.
	 *
	 * @return string|null
	 */
	public function getText(): ?string {
		return $this->text;
	}

	/**
	 * @param string|null $text
	 * @return FeedEntry
	 */
	public function setText(?string $text): self {
		$this->text = $text;

		return $this;
	}

	/**
	 * The user that was referenced in this feed entry.
	 *
	 * @return User|null
	 */
	public function getReferencedUser(): ?User {
		return $this->referencedUser;
	}

	/**
	 * @param User|null $referencedUser
	 * @return FeedEntry
	 */
	public function setReferencedUser(?User $referencedUser): self {
		$this->referencedUser = $referencedUser;

		return $this;
	}

	/**
	 * The children of this feed entry.
	 *
	 * @return Collection|self[]
	 */
	public function getChildren(): Collection {
		return $this->children;
	}

	/**
	 * @param FeedEntry $child
	 * @return FeedEntry
	 */
	public function addChild(FeedEntry $child): self {
		if (!$this->children->contains($child)) {
			$this->children[] = $child;
			$child->setParent($this);
		}

		return $this;
	}

	/**
	 * @param FeedEntry $child
	 * @return FeedEntry
	 */
	public function removeChild(FeedEntry $child): self {
		if ($this->children->contains($child)) {
			$this->children->removeElement($child);
			// set the owning side to null (unless already changed)
			if ($child->getParent() === $this) {
				$child->setParent(null);
			}
		}

		return $this;
	}

	/**
	 * The parent of this feed entry.
	 *
	 * @return FeedEntry|null
	 * @Serializer\VirtualProperty()
	 * @Serializer\SerializedName("parent")
	 */
	public function getParent(): ?self {
		$parent = $this->parent;
		$apiService = APIService::$instance;

		if (!is_null($apiService)) {
			if (!$apiService->mayView($parent)) {
				return null;
			}
		}

		return $parent;
	}

	/**
	 * @param self|null $parent
	 * @return FeedEntry
	 */
	public function setParent(?self $parent): self {
		$this->parent = $parent;

		return $this;
	}

	/**
	 * The token that was used to create this feed entry.
	 *
	 * @return Token|null
	 */
	public function getToken(): ?Token {
		return $this->token;
	}

	/**
	 * @param Token|null $token
	 * @return FeedEntry
	 */
	public function setToken(?Token $token): self {
		$this->token = $token;

		return $this;
	}

	/**
	 * The type of this feed entry.
	 * @return string|null
	 * @see FeedEntryType
	 *
	 */
	public function getType(): ?string {
		return $this->type;
	}

	/**
	 * @param string $type
	 * @return FeedEntry
	 */
	public function setType(string $type): self {
		$this->type = $type;

		return $this;
	}

	/**
	 * Whether this feed entry was marked as not-safe-for-work.
	 *
	 * @return bool|null
	 */
	public function isNSFW(): ?bool {
		return $this->nsfw;
	}

	/**
	 * @param bool $nsfw
	 * @return FeedEntry
	 */
	public function setNSFW(bool $nsfw): self {
		$this->nsfw = $nsfw;

		return $this;
	}

	/**
	 * The time of when this feed entry object was created.
	 *
	 * @return DateTimeInterface|null
	 */
	public function getTime(): ?DateTimeInterface {
		return $this->time;
	}

	/**
	 * @param DateTimeInterface $time
	 * @return FeedEntry
	 */
	public function setTime(DateTimeInterface $time): self {
		$this->time = $time;

		return $this;
	}

	/**
	 * @return int
	 * @Serializer\VirtualProperty()
	 */
	public function getReplyCount(): int {
		if (!is_null($this->replyCount)) {
			return $this->replyCount;
		}

		if ($this->type == FeedEntryType::POST || $this->type == FeedEntryType::REPLY) {
			$i = 0;

			foreach ($this->getChildren() as $child) {
				if ($child->getType() === FeedEntryType::REPLY) $i++;
			}

			return $i;
		}

		return 0;
	}

	/**
	 * @param int|null $replyCount
	 * @return $this
	 */
	public function setReplyCount(?int $replyCount): self {
		$this->replyCount = $replyCount;

		return $this;
	}

	/**
	 * @return int
	 * @Serializer\VirtualProperty()
	 */
	public function getShareCount(): int {
		if (!is_null($this->shareCount)) {
			return $this->shareCount;
		}

		if ($this->type == FeedEntryType::POST || $this->type == FeedEntryType::REPLY) {
			$i = 0;

			foreach ($this->getChildren() as $child) {
				if ($child->getType() === FeedEntryType::SHARE) $i++;
			}

			return $i;
		}

		return 0;
	}

	/**
	 * @param int|null $shareCount
	 * @return $this
	 */
	public function setShareCount(?int $shareCount): self {
		$this->shareCount = $shareCount;

		return $this;
	}

	/**
	 * @return int
	 * @Serializer\VirtualProperty()
	 */
	public function getFavoriteCount(): int {
		if (!is_null($this->favoriteCount)) {
			return $this->favoriteCount;
		}

		return $this->getFavorites()->count();
	}

	/**
	 * @param int|null $favoriteCount
	 * @return $this
	 */
	public function setFavoriteCount(?int $favoriteCount): self {
		$this->favoriteCount = $favoriteCount;

		return $this;
	}

	/**
	 * @return bool
	 * @Serializer\VirtualProperty()
	 * @Serializer\SerializedName("favorited")
	 */
	public function isFavorited(): bool {
		if (!is_null($this->favorited)) {
			return $this->favorited;
		}

		$apiService = APIService::$instance;

		if (!is_null($apiService) && $apiService->isAuthorized()) {
			return $apiService->getEntityManager()->getRepository(Favorite::class)->count([
					"feedEntry" => $this,
					"user" => $apiService->getUser()
				]) > 0;
		}

		return false;
	}

	/**
	 * @param bool|null $favorited
	 * @return $this
	 */
	public function setFavorited(?bool $favorited): self {
		$this->favorited = $favorited;

		return $this;
	}

	/**
	 * @return bool
	 * @Serializer\VirtualProperty()
	 * @Serializer\SerializedName("shared")
	 */
	public function isShared(): bool {
		if (!is_null($this->shared)) {
			return $this->shared;
		}

		$apiService = APIService::$instance;

		if (!is_null($apiService) && $apiService->isAuthorized()) {
			return $apiService->getEntityManager()->getRepository(FeedEntry::class)->count([
					"type" => FeedEntryType::SHARE,
					"parent" => $this,
					"user" => $apiService->getUser()
				]) > 0;
		}

		return false;
	}

	/**
	 * @param bool|null $shared
	 * @return $this
	 */
	public function setShared(?bool $shared): self {
		$this->shared = $shared;

		return $this;
	}

	/**
	 * @return MediaFile[]
	 * @Serializer\VirtualProperty()
	 * @Serializer\SerializedName("attachments")
	 * @author Mehdi Baaboura <mbaaboura@gigadrivegroup.com>
	 */
	public function getAttachments(): array {
		if (!is_null($this->attachments)) {
			return $this->attachments;
		}

		$apiService = APIService::$instance;

		if (!is_null($apiService)) {
			$this->attachments = $apiService->getEntityManager()->getRepository(MediaFile::class)->getFilesForFeedEntry($this);
			return $this->attachments;
		}

		return [];
	}

	/**
	 * The favorites of this feed entry.
	 *
	 * @return Collection|Favorite[]
	 */
	public function getFavorites(): Collection {
		return $this->favorites;
	}

	/**
	 * @param Favorite $favorite
	 * @return FeedEntry
	 */
	public function addFavorite(Favorite $favorite): self {
		if (!$this->favorites->contains($favorite)) {
			$this->favorites[] = $favorite;
			$favorite->setFeedEntry($this);
		}

		return $this;
	}

	/**
	 * @param Favorite $favorite
	 * @return FeedEntry
	 */
	public function removeFavorite(Favorite $favorite): self {
		if ($this->favorites->contains($favorite)) {
			$this->favorites->removeElement($favorite);
			// set the owning side to null (unless already changed)
			if ($favorite->getFeedEntry() === $this) {
				$favorite->setFeedEntry(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection|Hashtag[]
	 */
	public function getHashtags(): Collection {
		return $this->hashtags;
	}

	public function addHashtag(Hashtag $hashtag): self {
		if (!$this->hashtags->contains($hashtag)) {
			$this->hashtags[] = $hashtag;
		}

		return $this;
	}

	public function removeHashtag(Hashtag $hashtag): self {
		if ($this->hashtags->contains($hashtag)) {
			$this->hashtags->removeElement($hashtag);
		}

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
			$mediaAttachment->setFeedEntry($this);
		}

		return $this;
	}

	public function removeMediaAttachment(MediaAttachment $mediaAttachment): self {
		if ($this->mediaAttachments->contains($mediaAttachment)) {
			$this->mediaAttachments->removeElement($mediaAttachment);
			// set the owning side to null (unless already changed)
			if ($mediaAttachment->getFeedEntry() === $this) {
				$mediaAttachment->setFeedEntry(null);
			}
		}

		return $this;
	}
}

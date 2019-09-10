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
use JMS\Serializer\Annotation as Serializer;
use qpost\Constants\FeedEntryType;
use qpost\Constants\PrivacyLevel;
use function count;
use function is_null;

/**
 * Represents a user.
 *
 * @ORM\Entity(repositoryClass="qpost\Repository\UserRepository")
 * @ORM\Table(indexes={@ORM\Index(columns={"display_name"}),@ORM\Index(columns={"email"}),@ORM\Index(columns={"birthday"}),@ORM\Index(columns={"privacy_level"}),@ORM\Index(columns={"verified"})})
 */
class User {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=24)
	 */
	private $displayName;

	/**
	 * @ORM\Column(type="string", length=16, unique=true)
	 */
	private $username;

	/**
	 * @ORM\Column(type="string", length=60, nullable=true)
	 * @Serializer\Exclude()
	 */
	private $password;

	/**
	 * @ORM\Column(type="string", length=50)
	 * @Serializer\Exclude()
	 */
	private $email;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Serializer\Exclude()
	 */
	private $avatar;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $bio;

	/**
	 * @ORM\Column(type="date", nullable=true)
	 */
	private $birthday;

	/**
	 * @ORM\Column(type="string", length=32)
	 */
	private $privacyLevel = PrivacyLevel::PUBLIC;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * @ORM\Column(type="boolean")
	 * @Serializer\Exclude()
	 */
	private $emailActivated = false;

	/**
	 * @ORM\Column(type="string", length=7, nullable=true)
	 * @Serializer\Exclude()
	 */
	private $emailActivationToken;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $verified = false;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 * @Serializer\Exclude()
	 */
	private $lastUsernameChange;

	/**
	 * @ORM\ManyToMany(targetEntity="qpost\Entity\UserFeaturedBox", mappedBy="users")
	 * @Serializer\Exclude()
	 */
	private $featuringBoxes;

	/**
	 * @ORM\OneToOne(targetEntity="qpost\Entity\UserFeaturedBox", inversedBy="user", cascade={"persist", "remove"})
	 * @Serializer\Exclude()
	 */
	private $featuredBox;

	/**
	 * @ORM\OneToOne(targetEntity="qpost\Entity\UserGigadriveData", inversedBy="user", cascade={"persist", "remove"})
	 * @Serializer\Exclude()
	 */
	private $gigadriveData;

	/**
	 * @ORM\OneToMany(targetEntity="qpost\Entity\Token", mappedBy="user", orphanRemoval=true)
	 * @Serializer\Exclude()
	 */
	private $tokens;

	/**
	 * @ORM\OneToMany(targetEntity="qpost\Entity\Follower", mappedBy="sender", orphanRemoval=true)
	 * @Serializer\Exclude()
	 */
	private $following;

	/**
	 * @ORM\OneToMany(targetEntity="qpost\Entity\Follower", mappedBy="receiver", orphanRemoval=true)
	 * @Serializer\Exclude()
	 */
	private $followers;

	/**
	 * @ORM\OneToMany(targetEntity="qpost\Entity\FollowRequest", mappedBy="sender", orphanRemoval=true)
	 * @Serializer\Exclude()
	 */
	private $sentRequests;

	/**
	 * @ORM\OneToMany(targetEntity="qpost\Entity\FollowRequest", mappedBy="receiver", orphanRemoval=true)
	 * @Serializer\Exclude()
	 */
	private $followRequests;

	/**
	 * @ORM\OneToMany(targetEntity="qpost\Entity\Suspension", mappedBy="target", orphanRemoval=true)
	 * @Serializer\Exclude()
	 */
	private $suspensions;

	/**
	 * @ORM\OneToMany(targetEntity="qpost\Entity\Suspension", mappedBy="staff")
	 * @Serializer\Exclude()
	 */
	private $createdSuspensions;

	/**
	 * @ORM\OneToMany(targetEntity="qpost\Entity\FeedEntry", mappedBy="user", orphanRemoval=true)
	 * @Serializer\Exclude()
	 */
	private $feedEntries;

	/**
	 * @ORM\OneToMany(targetEntity="qpost\Entity\Notification", mappedBy="user", orphanRemoval=true)
	 * @Serializer\Exclude()
	 */
	private $notifications;

	/**
	 * @ORM\OneToMany(targetEntity="qpost\Entity\Favorite", mappedBy="user", orphanRemoval=true)
	 * @Serializer\Exclude()
	 */
	private $favorites;

	/**
	 * @ORM\OneToMany(targetEntity="qpost\Entity\MediaFile", mappedBy="originalUploader")
	 * @Serializer\Exclude()
	 */
	private $uploadedFiles;

	/**
	 * @ORM\OneToMany(targetEntity="qpost\Entity\Block", mappedBy="user", orphanRemoval=true)
	 * @Serializer\Exclude()
	 */
	private $blocking;

	/**
	 * @ORM\OneToMany(targetEntity="qpost\Entity\Block", mappedBy="target", orphanRemoval=true)
	 * @Serializer\Exclude()
	 */
	private $blockedBy;

	public function __construct() {
		$this->featuringBoxes = new ArrayCollection();
		$this->tokens = new ArrayCollection();
		$this->following = new ArrayCollection();
		$this->followers = new ArrayCollection();
		$this->sentRequests = new ArrayCollection();
		$this->followRequests = new ArrayCollection();
		$this->suspensions = new ArrayCollection();
		$this->createdSuspensions = new ArrayCollection();
		$this->feedEntries = new ArrayCollection();
		$this->notifications = new ArrayCollection();
		$this->favorites = new ArrayCollection();
		$this->uploadedFiles = new ArrayCollection();
		$this->blocking = new ArrayCollection();
		$this->blockedBy = new ArrayCollection();
	}

	/**
	 * The id of this user.
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * The display name of this user.
	 *
	 * @return string|null
	 */
	public function getDisplayName(): ?string {
		return $this->displayName;
	}

	/**
	 * @param string $displayName
	 * @return User
	 */
	public function setDisplayName(string $displayName): self {
		$this->displayName = $displayName;

		return $this;
	}

	/**
	 * The username of this user.
	 *
	 * @return string|null
	 */
	public function getUsername(): ?string {
		return $this->username;
	}

	/**
	 * @param string $username
	 * @return User
	 */
	public function setUsername(string $username): self {
		$this->username = $username;

		return $this;
	}

	/**
	 * The password hash of this user.
	 *
	 * @return string|null
	 */
	public function getPassword(): ?string {
		return $this->password;
	}

	/**
	 * @param string|null $password
	 * @return User
	 */
	public function setPassword(?string $password): self {
		$this->password = $password;

		return $this;
	}

	/**
	 * The email address of this user.
	 *
	 * @return string|null
	 */
	public function getEmail(): ?string {
		return $this->email;
	}

	/**
	 * @param string $email
	 * @return User
	 */
	public function setEmail(string $email): self {
		$this->email = $email;

		return $this;
	}

	/**
	 * The URL of this user's avatar.
	 *
	 * @return string|null
	 */
	public function getAvatar(): ?string {
		return $this->avatar;
	}

	/**
	 * @param string|null $avatar
	 * @return User
	 */
	public function setAvatar(?string $avatar): self {
		$this->avatar = $avatar;

		return $this;
	}

	/**
	 * @return string
	 * @Serializer\VirtualProperty()
	 */
	public function getAvatarURL(): string {
		return !is_null($this->avatar) ? $this->avatar : $_ENV["DEFAULT_AVATAR_URL"];
	}

	/**
	 * The bio of this user.
	 *
	 * @return string|null
	 */
	public function getBio(): ?string {
		return $this->bio;
	}

	/**
	 * @param string|null $bio
	 * @return User
	 */
	public function setBio(?string $bio): self {
		$this->bio = $bio;

		return $this;
	}

	/**
	 * The birthday of this user.
	 *
	 * @return DateTimeInterface|null
	 */
	public function getBirthday(): ?DateTimeInterface {
		return $this->birthday;
	}

	/**
	 * @param DateTimeInterface|null $birthday
	 * @return User
	 */
	public function setBirthday(?DateTimeInterface $birthday): self {
		$this->birthday = $birthday;

		return $this;
	}

	/**
	 * The privacy level of this user.
	 * @return string|null
	 * @see PrivacyLevel
	 *
	 */
	public function getPrivacyLevel(): ?string {
		return $this->privacyLevel;
	}

	/**
	 * @param string $privacyLevel
	 * @return User
	 */
	public function setPrivacyLevel(string $privacyLevel): self {
		$this->privacyLevel = $privacyLevel;

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
	 * @return User
	 */
	public function setTime(DateTimeInterface $time): self {
		$this->time = $time;

		return $this;
	}

	/**
	 * Whether or not this user's email address was confirmed.
	 *
	 * @return bool|null
	 */
	public function isEmailActivated(): ?bool {
		return $this->emailActivated;
	}

	/**
	 * @param bool $emailActivated
	 * @return User
	 */
	public function setEmailActivated(bool $emailActivated): self {
		$this->emailActivated = $emailActivated;

		return $this;
	}

	/**
	 * The email activation token of this user.
	 *
	 * @return string|null
	 */
	public function getEmailActivationToken(): ?string {
		return $this->emailActivationToken;
	}

	/**
	 * @param string|null $emailActivationToken
	 * @return User
	 */
	public function setEmailActivationToken(?string $emailActivationToken): self {
		$this->emailActivationToken = $emailActivationToken;

		return $this;
	}

	/**
	 * Whether or not this user has a verification check mark.
	 *
	 * @return bool|null
	 */
	public function isVerified(): ?bool {
		return $this->verified;
	}

	/**
	 * @param bool $verified
	 * @return User
	 */
	public function setVerified(bool $verified): self {
		$this->verified = $verified;

		return $this;
	}

	/**
	 * The time at which this user last changed their username.
	 *
	 * @return DateTimeInterface|null
	 */
	public function getLastUsernameChange(): ?DateTimeInterface {
		return $this->lastUsernameChange;
	}

	/**
	 * @param DateTimeInterface|null $lastUsernameChange
	 * @return User
	 */
	public function setLastUsernameChange(?DateTimeInterface $lastUsernameChange): self {
		$this->lastUsernameChange = $lastUsernameChange;

		return $this;
	}

	/**
	 * The featured boxes in which this user is being displayed.
	 *
	 * @return Collection|UserFeaturedBox[]
	 */
	public function getFeaturingBoxes(): Collection {
		return $this->featuringBoxes;
	}

	/**
	 * @param UserFeaturedBox $featuringBox
	 * @return User
	 */
	public function addFeaturingBox(UserFeaturedBox $featuringBox): self {
		if (!$this->featuringBoxes->contains($featuringBox)) {
			$this->featuringBoxes[] = $featuringBox;
			$featuringBox->addUser($this);
		}

		return $this;
	}

	/**
	 * @param UserFeaturedBox $featuringBox
	 * @return User
	 */
	public function removeFeaturingBox(UserFeaturedBox $featuringBox): self {
		if ($this->featuringBoxes->contains($featuringBox)) {
			$this->featuringBoxes->removeElement($featuringBox);
			$featuringBox->removeUser($this);
		}

		return $this;
	}

	/**
	 * The featured box on this user's profile.
	 *
	 * @return UserFeaturedBox|null
	 */
	public function getFeaturedBox(): ?UserFeaturedBox {
		return $this->featuredBox;
	}

	/**
	 * @param UserFeaturedBox|null $featuredBox
	 * @return User
	 */
	public function setFeaturedBox(?UserFeaturedBox $featuredBox): self {
		$this->featuredBox = $featuredBox;

		return $this;
	}

	/**
	 * The Gigadrive account data of this user.
	 *
	 * @return UserGigadriveData|null
	 */
	public function getGigadriveData(): ?UserGigadriveData {
		return $this->gigadriveData;
	}

	/**
	 * @param UserGigadriveData|null $gigadriveData
	 * @return User
	 */
	public function setGigadriveData(?UserGigadriveData $gigadriveData): self {
		$this->gigadriveData = $gigadriveData;

		return $this;
	}

	/**
	 * The tokens of this user.
	 *
	 * @return Collection|Token[]
	 */
	public function getTokens(): Collection {
		return $this->tokens;
	}

	/**
	 * @param Token $token
	 * @return User
	 */
	public function addToken(Token $token): self {
		if (!$this->tokens->contains($token)) {
			$this->tokens[] = $token;
			$token->setUser($this);
		}

		return $this;
	}

	/**
	 * @param Token $token
	 * @return User
	 */
	public function removeToken(Token $token): self {
		if ($this->tokens->contains($token)) {
			$this->tokens->removeElement($token);
			// set the owning side to null (unless already changed)
			if ($token->getUser() === $this) {
				$token->setUser(null);
			}
		}

		return $this;
	}

	/**
	 * The users that are being followed by this user.
	 *
	 * @return Collection|Follower[]
	 */
	public function getFollowing(): Collection {
		return $this->following;
	}

	/**
	 * @param Follower $following
	 * @return User
	 */
	public function addFollowing(Follower $following): self {
		if (!$this->following->contains($following)) {
			$this->following[] = $following;
			$following->setSender($this);
		}

		return $this;
	}

	/**
	 * @param Follower $following
	 * @return User
	 */
	public function removeFollowing(Follower $following): self {
		if ($this->following->contains($following)) {
			$this->following->removeElement($following);
			// set the owning side to null (unless already changed)
			if ($following->getSender() === $this) {
				$following->setSender(null);
			}
		}

		return $this;
	}

	/**
	 * The users that are following this user.
	 *
	 * @return Collection|Follower[]
	 */
	public function getFollowers(): Collection {
		return $this->followers;
	}

	/**
	 * @param Follower $follower
	 * @return User
	 */
	public function addFollower(Follower $follower): self {
		if (!$this->followers->contains($follower)) {
			$this->followers[] = $follower;
			$follower->setReceiver($this);
		}

		return $this;
	}

	/**
	 * @param Follower $follower
	 * @return User
	 */
	public function removeFollower(Follower $follower): self {
		if ($this->followers->contains($follower)) {
			$this->followers->removeElement($follower);
			// set the owning side to null (unless already changed)
			if ($follower->getReceiver() === $this) {
				$follower->setReceiver(null);
			}
		}

		return $this;
	}

	/**
	 * The follow requests that were sent out by this user.
	 *
	 * @return Collection|FollowRequest[]
	 */
	public function getSentRequests(): Collection {
		return $this->sentRequests;
	}

	/**
	 * @param FollowRequest $sentRequest
	 * @return User
	 */
	public function addSentRequest(FollowRequest $sentRequest): self {
		if (!$this->sentRequests->contains($sentRequest)) {
			$this->sentRequests[] = $sentRequest;
			$sentRequest->setSender($this);
		}

		return $this;
	}

	/**
	 * @param FollowRequest $sentRequest
	 * @return User
	 */
	public function removeSentRequest(FollowRequest $sentRequest): self {
		if ($this->sentRequests->contains($sentRequest)) {
			$this->sentRequests->removeElement($sentRequest);
			// set the owning side to null (unless already changed)
			if ($sentRequest->getSender() === $this) {
				$sentRequest->setSender(null);
			}
		}

		return $this;
	}

	/**
	 * The follow requests that were received by this user.
	 *
	 * @return Collection|FollowRequest[]
	 */
	public function getFollowRequests(): Collection {
		return $this->followRequests;
	}

	/**
	 * @param FollowRequest $followRequest
	 * @return User
	 */
	public function addFollowRequest(FollowRequest $followRequest): self {
		if (!$this->followRequests->contains($followRequest)) {
			$this->followRequests[] = $followRequest;
			$followRequest->setReceiver($this);
		}

		return $this;
	}

	/**
	 * @param FollowRequest $followRequest
	 * @return User
	 */
	public function removeFollowRequest(FollowRequest $followRequest): self {
		if ($this->followRequests->contains($followRequest)) {
			$this->followRequests->removeElement($followRequest);
			// set the owning side to null (unless already changed)
			if ($followRequest->getReceiver() === $this) {
				$followRequest->setReceiver(null);
			}
		}

		return $this;
	}

	/**
	 * @return bool Whether or not this user is currently suspended.
	 * @Serializer\VirtualProperty()
	 * @Serializer\SerializedName("suspended")
	 */
	public function isSuspended(): bool {
		foreach ($this->getSuspensions() as $suspension) {
			if ($suspension->isActive()) return true;
		}

		return false;
	}

	/**
	 * The suspensions on this user's account.
	 *
	 * @return Collection|Suspension[]
	 */
	public function getSuspensions(): Collection {
		return $this->suspensions;
	}

	/**
	 * @param Suspension $suspension
	 * @return User
	 */
	public function addSuspension(Suspension $suspension): self {
		if (!$this->suspensions->contains($suspension)) {
			$this->suspensions[] = $suspension;
			$suspension->setTarget($this);
		}

		return $this;
	}

	/**
	 * @param Suspension $suspension
	 * @return User
	 */
	public function removeSuspension(Suspension $suspension): self {
		if ($this->suspensions->contains($suspension)) {
			$this->suspensions->removeElement($suspension);
			// set the owning side to null (unless already changed)
			if ($suspension->getTarget() === $this) {
				$suspension->setTarget(null);
			}
		}

		return $this;
	}

	/**
	 * The suspensions created by this user.
	 *
	 * @return Collection|Suspension[]
	 */
	public function getCreatedSuspensions(): Collection {
		return $this->createdSuspensions;
	}

	/**
	 * @param Suspension $createdSuspension
	 * @return User
	 */
	public function addCreatedSuspension(Suspension $createdSuspension): self {
		if (!$this->createdSuspensions->contains($createdSuspension)) {
			$this->createdSuspensions[] = $createdSuspension;
			$createdSuspension->setStaff($this);
		}

		return $this;
	}

	/**
	 * @param Suspension $createdSuspension
	 * @return User
	 */
	public function removeCreatedSuspension(Suspension $createdSuspension): self {
		if ($this->createdSuspensions->contains($createdSuspension)) {
			$this->createdSuspensions->removeElement($createdSuspension);
			// set the owning side to null (unless already changed)
			if ($createdSuspension->getStaff() === $this) {
				$createdSuspension->setStaff(null);
			}
		}

		return $this;
	}

	/**
	 * The feed entries that were created by this user.
	 *
	 * @return Collection|FeedEntry[]
	 */
	public function getFeedEntries(): Collection {
		return $this->feedEntries;
	}

	/**
	 * @param FeedEntry $feedEntry
	 * @return User
	 */
	public function addFeedEntry(FeedEntry $feedEntry): self {
		if (!$this->feedEntries->contains($feedEntry)) {
			$this->feedEntries[] = $feedEntry;
			$feedEntry->setUser($this);
		}

		return $this;
	}

	/**
	 * @param FeedEntry $feedEntry
	 * @return User
	 */
	public function removeFeedEntry(FeedEntry $feedEntry): self {
		if ($this->feedEntries->contains($feedEntry)) {
			$this->feedEntries->removeElement($feedEntry);
			// set the owning side to null (unless already changed)
			if ($feedEntry->getUser() === $this) {
				$feedEntry->setUser(null);
			}
		}

		return $this;
	}

	/**
	 * @return int
	 * @Serializer\VirtualProperty()
	 */
	public function getPostCount(): int {
		$i = 0;

		foreach ($this->getFeedEntries() as $feedEntry) {
			if ($feedEntry->getType() === FeedEntryType::POST && is_null($feedEntry->getParent())) {
				$i++;
			}
		}

		return $i;
	}

	/**
	 * @return int
	 * @Serializer\VirtualProperty()
	 */
	public function getReplyCount(): int {
		$i = 0;

		foreach ($this->getFeedEntries() as $feedEntry) {
			if ($feedEntry->getType() === FeedEntryType::POST && !is_null($feedEntry->getParent())) {
				$i++;
			}
		}

		return $i;
	}

	/**
	 * @return int
	 * @Serializer\VirtualProperty()
	 */
	public function getShareCount(): int {
		$i = 0;

		foreach ($this->getFeedEntries() as $feedEntry) {
			if ($feedEntry->getType() === FeedEntryType::SHARE && !is_null($feedEntry->getParent())) {
				$i++;
			}
		}

		return $i;
	}

	/**
	 * @return int
	 * @Serializer\VirtualProperty()
	 */
	public function getFollowingPostCount(): int {
		$i = 0;

		foreach ($this->getFeedEntries() as $feedEntry) {
			if ($feedEntry->getType() === FeedEntryType::NEW_FOLLOWING && !is_null($feedEntry->getReferencedUser())) {
				$i++;
			}
		}

		return $i;
	}

	/**
	 * @return int
	 * @Serializer\VirtualProperty()
	 */
	public function getTotalPostCount(): int {
		return count($this->getFeedEntries());
	}

	/**
	 * @return int
	 * @Serializer\VirtualProperty()
	 */
	public function getFollowingCount(): int {
		return count($this->getFollowing());
	}

	/**
	 * @return int
	 * @Serializer\VirtualProperty()
	 */
	public function getFollowerCount(): int {
		return count($this->getFollowers());
	}

	/**
	 * @return int
	 * @Serializer\VirtualProperty()
	 */
	public function getOpenRequestsCount(): int {
		return count($this->getFollowRequests());
	}

	/**
	 * The notifications on this user's account.
	 *
	 * @return Collection|Notification[]
	 */
	public function getNotifications(): Collection {
		return $this->notifications;
	}

	/**
	 * @param Notification $notification
	 * @return User
	 */
	public function addNotification(Notification $notification): self {
		if (!$this->notifications->contains($notification)) {
			$this->notifications[] = $notification;
			$notification->setUser($this);
		}

		return $this;
	}

	/**
	 * @param Notification $notification
	 * @return User
	 */
	public function removeNotification(Notification $notification): self {
		if ($this->notifications->contains($notification)) {
			$this->notifications->removeElement($notification);
			// set the owning side to null (unless already changed)
			if ($notification->getUser() === $this) {
				$notification->setUser(null);
			}
		}

		return $this;
	}

	/**
	 * The favorites on this user's account.
	 *
	 * @return Collection|Favorite[]
	 */
	public function getFavorites(): Collection {
		return $this->favorites;
	}

	/**
	 * @param Favorite $favorite
	 * @return User
	 */
	public function addFavorite(Favorite $favorite): self {
		if (!$this->favorites->contains($favorite)) {
			$this->favorites[] = $favorite;
			$favorite->setUser($this);
		}

		return $this;
	}

	/**
	 * @param Favorite $favorite
	 * @return User
	 */
	public function removeFavorite(Favorite $favorite): self {
		if ($this->favorites->contains($favorite)) {
			$this->favorites->removeElement($favorite);
			// set the owning side to null (unless already changed)
			if ($favorite->getUser() === $this) {
				$favorite->setUser(null);
			}
		}

		return $this;
	}

	/**
	 * The files that were uploaded by this user.
	 *
	 * @return Collection|MediaFile[]
	 */
	public function getUploadedFiles(): Collection {
		return $this->uploadedFiles;
	}

	/**
	 * @param MediaFile $uploadedFile
	 * @return User
	 */
	public function addUploadedFile(MediaFile $uploadedFile): self {
		if (!$this->uploadedFiles->contains($uploadedFile)) {
			$this->uploadedFiles[] = $uploadedFile;
			$uploadedFile->setOriginalUploader($this);
		}

		return $this;
	}

	/**
	 * @param MediaFile $uploadedFile
	 * @return User
	 */
	public function removeUploadedFile(MediaFile $uploadedFile): self {
		if ($this->uploadedFiles->contains($uploadedFile)) {
			$this->uploadedFiles->removeElement($uploadedFile);
			// set the owning side to null (unless already changed)
			if ($uploadedFile->getOriginalUploader() === $this) {
				$uploadedFile->setOriginalUploader(null);
			}
		}

		return $this;
	}

	/**
	 * The blocks that were created by this user.
	 *
	 * @return Collection|Block[]
	 */
	public function getBlocking(): Collection {
		return $this->blocking;
	}

	/**
	 * @param Block $blocking
	 * @return User
	 */
	public function addBlocking(Block $blocking): self {
		if (!$this->blocking->contains($blocking)) {
			$this->blocking[] = $blocking;
			$blocking->setUser($this);
		}

		return $this;
	}

	/**
	 * @param Block $blocking
	 * @return User
	 */
	public function removeBlocking(Block $blocking): self {
		if ($this->blocking->contains($blocking)) {
			$this->blocking->removeElement($blocking);
			// set the owning side to null (unless already changed)
			if ($blocking->getUser() === $this) {
				$blocking->setUser(null);
			}
		}

		return $this;
	}

	/**
	 * The blocks that target this user.
	 *
	 * @return Collection|Block[]
	 */
	public function getBlockedBy(): Collection {
		return $this->blockedBy;
	}

	/**
	 * @param Block $blockedBy
	 * @return User
	 */
	public function addBlockedBy(Block $blockedBy): self {
		if (!$this->blockedBy->contains($blockedBy)) {
			$this->blockedBy[] = $blockedBy;
			$blockedBy->setTarget($this);
		}

		return $this;
	}

	/**
	 * @param Block $blockedBy
	 * @return User
	 */
	public function removeBlockedBy(Block $blockedBy): self {
		if ($this->blockedBy->contains($blockedBy)) {
			$this->blockedBy->removeElement($blockedBy);
			// set the owning side to null (unless already changed)
			if ($blockedBy->getTarget() === $this) {
				$blockedBy->setTarget(null);
			}
		}

		return $this;
	}
}

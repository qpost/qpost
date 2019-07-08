<?php

namespace qpost\Feed;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use qpost\Account\User;

/**
 * Class Notification
 * @package qpost\Feed
 *
 * @ORM\Entity
 */
class Notification {
	/**
	 * @var int $id
	 *
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @var User $user
	 *
	 * @ORM\ManyToOne(targetEntity="qpost\Account\User")
	 */
	private $user;

	/**
	 * @var string $type
	 *
	 * @ORM\Column(type="string")
	 */
	private $type;

	/**
	 * @var User|null $follower
	 *
	 * @ORM\ManyToOne(targetEntity="qpost\Account\User")
	 */
	private $follower;

	/**
	 * @var FeedEntry|null $post
	 *
	 * @ORM\OneToOne(targetEntity="FeedEntry")
	 */
	private $post;

	/**
	 * @var boolean $seen
	 *
	 * @ORM\Column(type="boolean")
	 */
	private $seen = false;

	/**
	 * @var boolean $notified
	 *
	 * @ORM\Column(type="boolean")
	 */
	private $notified = false;

	/**
	 * @var DateTime $time
	 *
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @param int $id
	 * @return Notification
	 */
	public function setId(int $id): self {
		$this->id = $id;
		return $this;
	}

	/**
	 * @return User
	 */
	public function getUser(): User {
		return $this->user;
	}

	/**
	 * @param User $user
	 * @return Notification
	 */
	public function setUser(User $user): self {
		$this->user = $user;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getType(): string {
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
	 * @return User|null
	 */
	public function getFollower(): ?User {
		return $this->follower;
	}

	/**
	 * @param User|null $follower
	 * @return Notification
	 */
	public function setFollower(?User $follower): self {
		$this->follower = $follower;
		return $this;
	}

	/**
	 * @return FeedEntry
	 */
	public function getPost(): ?FeedEntry {
		return $this->post;
	}

	/**
	 * @param FeedEntry|null $post
	 * @return $this
	 */
	public function setPost(?FeedEntry $post) {
		$this->post = $post;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isSeen(): bool {
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
	 * @return bool
	 */
	public function isNotified(): bool {
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
	 * @return DateTime
	 */
	public function getTime(): DateTime {
		return $this->time;
	}

	/**
	 * @param DateTime $time
	 * @return Notification
	 */
	public function setTime(DateTime $time): self {
		$this->time = $time;
		return $this;
	}
}
<?php

namespace qpost\Feed;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use qpost\Account\Follower;
use qpost\Account\PrivacyLevel;
use qpost\Account\User;
use qpost\Block\Block;
use qpost\Database\EntityManager;

/**
 * Class Favorite
 * @package qpost\Account
 *
 * @ORM\Entity
 */
class Favorite {
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
	 * @var FeedEntry $post
	 *
	 * @ORM\ManyToOne(targetEntity="FeedEntry")
	 */
	private $post;
	/**
	 * @var DateTime $time
	 *
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * @param User $user
	 * @param FeedEntry $post
	 * @return bool
	 */
	public static function favorite(User $user, FeedEntry $post): bool {
		if ($post->getType() != FeedEntryType::POST || !is_null($post->getPost())) return false;
		if (Block::hasBlocked($post->getUser(), $user)) return false;

		// Check for privacy level
		if (($user->getId() != $post->getUser()->getId()) && (($post->getUser()->getPrivacyLevel() == PrivacyLevel::PUBLIC && !Follower::isFollowing($user, $post->getUser())) || ($post->getUser()->getPrivacyLevel() == PrivacyLevel::CLOSED))) {
			return false;
		}

		$entityManager = EntityManager::instance();

		$favorite = new Favorite();
		$favorite->setUser($user)
			->setPost($post)
			->setTime(new DateTime("now"));

		$entityManager->persist($favorite);

		$notification = new Notification();
		$notification->setUser($post->getUser())
			->setSeen(false)
			->setType(NotificationType::FAVORITE)
			->setNotified(false)
			->setTime(new DateTime("now"));

		$entityManager->persist($notification);

		$entityManager->flush();

		return true;
	}

	/**
	 * @param User $user
	 * @param FeedEntry $post
	 * @return bool
	 */
	public static function unfavorite(User $user, FeedEntry $post): bool {
		if (!self::hasFavorited($user, $post)) return false;

		$entityManager = EntityManager::instance();

		$favorite = $entityManager->getRepository(Favorite::class)->findOneBy([
			"user" => $user,
			"post" => $post
		]);

		if ($favorite) {
			$entityManager->remove($favorite);

			$notification = $entityManager->getRepository(Notification::class)->findOneBy([
				"user" => $user,
				"type" => NotificationType::FAVORITE,
				"post" => $post
			]);

			if ($notification) {
				$entityManager->remove($notification);
			}

			$entityManager->flush();

			return true;
		}

		return false;
	}

	/**
	 * @param User $user
	 * @param FeedEntry $post
	 * @return bool
	 */
	public static function hasFavorited(User $user, FeedEntry $post): bool {
		return EntityManager::instance()->getRepository(Favorite::class)->count([
				"user" => $user,
				"post" => $post
			]) > 0;
	}

	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @param int $id
	 * @return Favorite
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
	 * @return Favorite
	 */
	public function setUser(User $user): self {
		$this->user = $user;
		return $this;
	}

	/**
	 * @return FeedEntry
	 */
	public function getPost(): FeedEntry {
		return $this->post;
	}

	/**
	 * @param FeedEntry $post
	 * @return Favorite
	 */
	public function setPost(FeedEntry $post): self {
		$this->post = $post;
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
	 * @return Favorite
	 */
	public function setTime(DateTime $time): self {
		$this->time = $time;
		return $this;
	}
}
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

namespace qpost\Account;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use qpost\Database\EntityManager;
use qpost\Feed\FeedEntry;
use qpost\Feed\FeedEntryType;
use qpost\Feed\Notification;
use qpost\Feed\NotificationType;

/**
 * Class Follower
 * @package qpost\Account
 *
 * @ORM\Entity
 */
class Follower {
	/**
	 * @var int $id
	 *
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;
	/**
	 * @var User $from
	 *
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="following")
	 */
	private $from;
	/**
	 * @var User $to
	 *
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="followers")
	 */
	private $to;
	/**
	 * @var DateTime $time
	 *
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * @param User $from
	 * @param User $to
	 * @return bool
	 */
	public static function follow(User $from, User $to): bool {
		if ($from->getId() === $to->getId()) return false;
		if ($from->getFollowingCount() >= FOLLOW_LIMIT) return false;
		if (self::isFollowing($from, $to)) return false;
		if ($to->getPrivacyLevel() === PrivacyLevel::CLOSED) return false;

		$entityManager = EntityManager::instance();

		if ($to->getPrivacyLevel() == PrivacyLevel::PRIVATE) {
			// Private user
			if (!FollowRequest::hasSentFollowRequest($from, $to)) {
				// Create follow request

				$followRequest = new FollowRequest();
				$followRequest->setFrom($from)
					->setTo($to)
					->setTime(new DateTime("now"));

				$entityManager->persist($followRequest);
				$entityManager->flush();

				return true;
			} else {
				// Request is accepted
				// Delete current request

				$followRequest = $entityManager->getRepository(FollowRequest::class)->findOneBy([
					"from" => $from,
					"to" => $to
				]);

				$entityManager->remove($followRequest);
			}
		}

		// create follower data
		$follower = new Follower();
		$follower->setFrom($from)
			->setTo($to)
			->setTime(new DateTime("now"));

		$entityManager->persist($follower);

		// create new following post
		$feedEntry = new FeedEntry();
		$feedEntry->setUser($from)
			->setFollowing($to)
			->setType("NEW_FOLLOWING")
			->setNSFW(false)
			->setTime(new DateTime("now"))
			->setSessionId(""); // TODO

		$entityManager->persist($feedEntry);

		// create notification
		$notification = new Notification();
		$notification->setUser($to)
			->setFollower($from)
			->setType(NotificationType::NEW_FOLLOWER)
			->setSeen(false)
			->setNotified(false)
			->setTime(new DateTime("now"));

		$entityManager->persist($notification);

		$entityManager->flush();

		return true;
	}

	/**
	 * @param User $from
	 * @param User $to
	 * @return bool
	 */
	public static function isFollowing(User $from, User $to): bool {
		return EntityManager::instance()->getRepository(Follower::class)->count([
				"from" => $from,
				"to" => $to
			]) > 0;
	}

	/**
	 * @param User $from
	 * @param User $to
	 * @return bool
	 */
	public static function unfollow(User $from, User $to): bool {
		if (!self::isFollowing($from, $to)) return false;

		$entityManager = EntityManager::instance();

		$follower = $entityManager->getRepository(Follower::class)->findOneBy([
			"from" => $from,
			"to" => $to
		]);

		$feedEntry = $entityManager->getRepository(FeedEntry::class)->findOneBy([
			"type" => FeedEntryType::NEW_FOLLOWING,
			"user" => $from,
			"following" => $to
		]);

		if ($feedEntry) $entityManager->remove($feedEntry);

		$notification = $entityManager->getRepository(Notification::class)->findOneBy([
			"type" => NotificationType::NEW_FOLLOWER,
			"user" => $to,
			"follower" => $from
		]);

		if ($notification) $entityManager->remove($notification);

		$return = false;

		if ($follower) {
			$entityManager->remove($follower);
			$return = true;
		}

		$entityManager->flush();

		return $return;
	}

	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @param int $id
	 * @return Follower
	 */
	public function setId(int $id): self {
		$this->id = $id;
		return $this;
	}

	/**
	 * @return User
	 */
	public function getFrom(): User {
		return $this->from;
	}

	/**
	 * @param User $from
	 * @return Follower
	 */
	public function setFrom(User $from): self {
		$this->from = $from;
		return $this;
	}

	/**
	 * @return User
	 */
	public function getTo(): User {
		return $this->to;
	}

	/**
	 * @param User $to
	 * @return Follower
	 */
	public function setTo(User $to): self {
		$this->to = $to;
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
	 * @return Follower
	 */
	public function setTime(DateTime $time): self {
		$this->time = $time;
		return $this;
	}
}
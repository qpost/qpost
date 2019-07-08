<?php

namespace qpost\Block;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use qpost\Account\User;
use qpost\Database\EntityManager;

/**
 * Class Block
 * @package qpost\Account
 *
 * @ORM\Entity
 */
class Block {
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
	 * @ORM\OneToOne(targetEntity="qpost\Account\User")
	 */
	private $user;
	/**
	 * @var User $target
	 *
	 * @ORM\OneToOne(targetEntity="qpost\Account\User")
	 */
	private $target;
	/**
	 * @var DateTime $time
	 *
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * @param User $user
	 * @param User $target
	 * @return bool
	 */
	public static function block(User $user, User $target): bool {
		if ($user->getId() === $target->getId()) return false;
		if (self::hasBlocked($user, $target)) return false;

		$entityManager = EntityManager::instance();

		$block = new Block();
		$block->setUser($user)
			->setTarget($target)
			->setTime(new DateTime("now"));

		$entityManager->persist($block);
		$entityManager->flush();

		return true;
	}

	/**
	 * @param User $user
	 * @param User $target
	 * @return bool
	 */
	public static function hasBlocked(User $user, User $target): bool {
		return EntityManager::instance()->getRepository(Block::class)->count([
				"user" => $user,
				"target" => $target
			]) > 0;
	}

	/**
	 * @param User $user
	 * @param User $target
	 * @return bool
	 */
	public static function unblock(User $user, User $target): bool {
		if ($user->getId() === $target->getId()) return false;
		if (!self::hasBlocked($user, $target)) return false;

		$entityManager = EntityManager::instance();

		$block = $entityManager->getRepository(Block::class)->findOneBy([
			"user" => $user,
			"target" => $target
		]);

		if ($block) {
			$entityManager->remove($block);
			$entityManager->flush();

			return true;
		}

		return false;
	}

	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @param int $id
	 * @return Block
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
	 * @return Block
	 */
	public function setUser(User $user): self {
		$this->user = $user;
		return $this;
	}

	/**
	 * @return User
	 */
	public function getTarget(): User {
		return $this->target;
	}

	/**
	 * @param User $target
	 * @return Block
	 */
	public function setTarget(User $target): self {
		$this->target = $target;
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
	 * @return Block
	 */
	public function setTime(DateTime $time): self {
		$this->time = $time;
		return $this;
	}
}
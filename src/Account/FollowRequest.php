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

/**
 * Class FollowRequest
 * @package qpost\Account
 *
 * @ORM\Entity
 */
class FollowRequest {
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
	 * @ORM\OneToOne(targetEntity="User")
	 */
	private $from;
	/**
	 * @var User $to
	 *
	 * @ORM\OneToOne(targetEntity="User")
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
	public static function hasSentFollowRequest(User $from, User $to): bool {
		return EntityManager::instance()->getRepository(FollowRequest::class)->count([
				"from" => $from,
				"to" => $to
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
	 * @return FollowRequest
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
	 * @return FollowRequest
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
	 * @return FollowRequest
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
	 * @return FollowRequest
	 */
	public function setTime(DateTime $time): self {
		$this->time = $time;
	}
}
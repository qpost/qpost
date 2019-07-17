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

/**
 * Represents a suspension of an account
 *
 * @package Account
 * @author Gigadrive (support@gigadrivegroup.com)
 * @copyright 2016-2018 Gigadrive
 * @link https://gigadrivegroup.com/dev/technologies
 *
 * @ORM\Entity
 */
class Suspension {
	/**
	 * @access private
	 * @var int $id
	 *
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @access private
	 * @var User $target
	 *
	 * @ORM\OneToOne(targetEntity="User")
	 */
	private $target;

	/**
	 * @access private
	 * @var string|null $reason
	 *
	 * @ORM\Column(type="text",nullable=true)
	 */
	private $reason;

	/**
	 * @access private
	 * @var User $staff
	 *
	 * @ORM\OneToOne(targetEntity="User")
	 */
	private $staff;

	/**
	 * @access private
	 * @var bool $active
	 *
	 * @ORM\Column(type="boolean")
	 */
	private $active;

	/**
	 * @access private
	 * @var DateTime $time
	 *
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * Returns the ID of this suspension
	 *
	 * @access public
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @param int $id
	 * @return Suspension
	 */
	public function setId(int $id): self {
		$this->id = $id;
		return $this;
	}

	/**
	 * Returns the user object of this suspension's target
	 *
	 * @access public
	 * @return User
	 */
	public function getTarget(): User {
		return $this->target;
	}

	/**
	 * @param User $target
	 * @return Suspension
	 */
	public function setTarget(User $target): self {
		$this->target = $target;
		return $this;
	}

	/**
	 * Returns the reason of this suspension
	 *
	 * @access public
	 * @return string|null
	 */
	public function getReason(): ?string {
		return $this->reason;
	}

	/**
	 * @param string|null $reason
	 * @return Suspension
	 */
	public function setReason(?string $reason): self {
		$this->reason = $reason;
		return $this;
	}

	/**
	 * Returns the user object of this suspension's staff member
	 *
	 * @access public
	 * @return User
	 */
	public function getStaff(): User {
		return $this->staff;
	}

	/**
	 * @param User $staff
	 * @return Suspension
	 */
	public function setStaff(User $staff): self {
		$this->staff = $staff;
		return $this;
	}

	/**
	 * Returns whether this suspension is active
	 *
	 * @access public
	 * @return bool
	 */
	public function isActive(): bool {
		return $this->active;
	}

	/**
	 * @param bool $active
	 * @return Suspension
	 */
	public function setActive(bool $active): self {
		$this->active = $active;
		return $this;
	}

	/**
	 * Returns the timestamp of when this suspension was created
	 *
	 * @access public
	 * @return DateTime
	 */
	public function getTime(): DateTime {
		return $this->time;
	}

	/**
	 * @param DateTime $time
	 * @return Suspension
	 */
	public function setTime(DateTime $time): self {
		$this->time = $time;
		return $this;
	}
}
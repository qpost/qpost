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

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="qpost\Repository\UserAppearanceSettingsRepository")
 */
class UserAppearanceSettings {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 * @Serializer\Exclude()
	 */
	private $id;

	/**
	 * @ORM\OneToOne(targetEntity="qpost\Entity\User", inversedBy="appearanceSettings", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
	 * @Serializer\Exclude()
	 */
	private $user;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $nightMode = false;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $autoplayGifs = false;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $showTrends = true;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $showSuggestedUsers = true;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $showBirthdays = true;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $showMatureWarning = true;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private $showChangelogs = true;

	public function getId(): ?int {
		return $this->id;
	}

	public function getUser(): ?User {
		return $this->user;
	}

	public function setUser(User $user): self {
		$this->user = $user;

		return $this;
	}

	public function getNightMode(): ?bool {
		return $this->nightMode;
	}

	public function setNightMode(bool $nightMode): self {
		$this->nightMode = $nightMode;

		return $this;
	}

	public function getAutoplayGifs(): ?bool {
		return $this->autoplayGifs;
	}

	public function setAutoplayGifs(bool $autoplayGifs): self {
		$this->autoplayGifs = $autoplayGifs;

		return $this;
	}

	public function getShowTrends(): ?bool {
		return $this->showTrends;
	}

	public function setShowTrends(bool $showTrends): self {
		$this->showTrends = $showTrends;

		return $this;
	}

	public function getShowSuggestedUsers(): ?bool {
		return $this->showSuggestedUsers;
	}

	public function setShowSuggestedUsers(bool $showSuggestedUsers): self {
		$this->showSuggestedUsers = $showSuggestedUsers;

		return $this;
	}

	public function getShowBirthdays(): ?bool {
		return $this->showBirthdays;
	}

	public function setShowBirthdays(bool $showBirthdays): self {
		$this->showBirthdays = $showBirthdays;

		return $this;
	}

	public function getShowMatureWarning(): ?bool {
		return $this->showMatureWarning;
	}

	public function setShowMatureWarning(bool $showMatureWarning): self {
		$this->showMatureWarning = $showMatureWarning;

		return $this;
	}

	public function getShowChangelogs(): ?bool {
		return $this->showChangelogs;
	}

	public function setShowChangelogs(bool $showChangelogs): self {
		$this->showChangelogs = $showChangelogs;

		return $this;
	}
}

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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents the data of a user's featured box.
 *
 * @ORM\Entity(repositoryClass="qpost\Repository\UserFeaturedBoxRepository")
 */
class UserFeaturedBox {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=32, nullable=true)
	 */
	private $title;

	/**
	 * @ORM\ManyToMany(targetEntity="qpost\Entity\User", inversedBy="featuringBoxes")
	 */
	private $users;

	/**
	 * @ORM\OneToOne(targetEntity="qpost\Entity\User", mappedBy="featuredBox", cascade={"persist", "remove"})
	 */
	private $user;

	public function __construct() {
		$this->users = new ArrayCollection();
	}

	/**
	 * The id of this featured box.
	 *
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * The title of this featured box.
	 *
	 * @return string|null
	 */
	public function getTitle(): ?string {
		return $this->title;
	}

	/**
	 * @param string|null $title
	 * @return UserFeaturedBox
	 */
	public function setTitle(?string $title): self {
		$this->title = $title;

		return $this;
	}

	/**
	 * The users featured in this box.
	 *
	 * @return Collection|User[]
	 */
	public function getUsers(): Collection {
		return $this->users;
	}

	/**
	 * @param User $user
	 * @return UserFeaturedBox
	 */
	public function addUser(User $user): self {
		if (!$this->users->contains($user)) {
			$this->users[] = $user;
		}

		return $this;
	}

	/**
	 * @param User $user
	 * @return UserFeaturedBox
	 */
	public function removeUser(User $user): self {
		if ($this->users->contains($user)) {
			$this->users->removeElement($user);
		}

		return $this;
	}

	/**
	 * The user that owns this featured box.
	 *
	 * @return User|null
	 */
	public function getUser(): ?User {
		return $this->user;
	}

	/**
	 * @param User|null $user
	 * @return UserFeaturedBox
	 */
	public function setUser(?User $user): self {
		$this->user = $user;

		// set (or unset) the owning side of the relation if necessary
		$newFeaturedBox = $user === null ? null : $this;
		if ($newFeaturedBox !== $user->getFeaturedBox()) {
			$user->setFeaturedBox($newFeaturedBox);
		}

		return $this;
	}
}

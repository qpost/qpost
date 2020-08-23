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
use Doctrine\ORM\Mapping as ORM;
use qpost\Repository\ChangelogEntryRepository;

/**
 * @ORM\Entity(repositoryClass=ChangelogEntryRepository::class)
 * @ORM\Table(indexes={@ORM\Index(columns={"time"})})
 */
class ChangelogEntry {
	/**
	 * @ORM\Id()
	 * @ORM\Column(type="string", length=16)
	 */
	private $tag;

	/**
	 * @ORM\Column(type="text")
	 */
	private $description;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	public function getTag(): ?string {
		return $this->tag;
	}

	public function setTag(string $tag): self {
		$this->tag = $tag;

		return $this;
	}

	public function getDescription(): ?string {
		return $this->description;
	}

	public function setDescription(string $description): self {
		$this->description = $description;

		return $this;
	}

	public function getTime(): ?DateTimeInterface {
		return $this->time;
	}

	public function setTime(DateTimeInterface $time): self {
		$this->time = $time;

		return $this;
	}
}

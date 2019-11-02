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
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="qpost\Repository\IpStackResultRepository")
 */
class IpStackResult {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=45)
	 */
	private $ip;

	/**
	 * @ORM\Column(type="string", length=16)
	 */
	private $type;

	/**
	 * @ORM\Column(type="string", length=4, nullable=true)
	 */
	private $continentCode;

	/**
	 * @ORM\Column(type="string", length=24, nullable=true)
	 */
	private $continentName;

	/**
	 * @ORM\Column(type="string", length=2, nullable=true)
	 */
	private $countryCode;

	/**
	 * @ORM\Column(type="string", length=64, nullable=true)
	 */
	private $countryName;

	/**
	 * @ORM\Column(type="string", length=2, nullable=true)
	 */
	private $regionCode;

	/**
	 * @ORM\Column(type="string", length=64, nullable=true)
	 */
	private $regionName;

	/**
	 * @ORM\Column(type="string", length=64, nullable=true)
	 */
	private $city;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $zipCode;

	/**
	 * @ORM\Column(type="float", nullable=true)
	 */
	private $latitude;

	/**
	 * @ORM\Column(type="float", nullable=true)
	 */
	private $longitude;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * @ORM\OneToOne(targetEntity="qpost\Entity\Token", mappedBy="ipStackResult", cascade={"persist", "remove"})
	 */
	private $token;

	public function getId(): ?int {
		return $this->id;
	}

	public function getIp(): ?string {
		return $this->ip;
	}

	public function setIp(string $ip): self {
		$this->ip = $ip;

		return $this;
	}

	public function getType(): ?string {
		return $this->type;
	}

	public function setType(string $type): self {
		$this->type = $type;

		return $this;
	}

	public function getContinentCode(): ?string {
		return $this->continentCode;
	}

	public function setContinentCode(?string $continentCode): self {
		$this->continentCode = $continentCode;

		return $this;
	}

	public function getContinentName(): ?string {
		return $this->continentName;
	}

	public function setContinentName(?string $continentName): self {
		$this->continentName = $continentName;

		return $this;
	}

	public function getCountryCode(): ?string {
		return $this->countryCode;
	}

	public function setCountryCode(?string $countryCode): self {
		$this->countryCode = $countryCode;

		return $this;
	}

	public function getCountryName(): ?string {
		return $this->countryName;
	}

	public function setCountryName(?string $countryName): self {
		$this->countryName = $countryName;

		return $this;
	}

	public function getRegionCode(): ?string {
		return $this->regionCode;
	}

	public function setRegionCode(?string $regionCode): self {
		$this->regionCode = $regionCode;

		return $this;
	}

	public function getRegionName(): ?string {
		return $this->regionName;
	}

	public function setRegionName(?string $regionName): self {
		$this->regionName = $regionName;

		return $this;
	}

	public function getCity(): ?string {
		return $this->city;
	}

	public function setCity(?string $city): self {
		$this->city = $city;

		return $this;
	}

	public function getZipCode(): ?int {
		return $this->zipCode;
	}

	public function setZipCode(int $zipCode): self {
		$this->zipCode = $zipCode;

		return $this;
	}

	public function getLatitude(): ?float {
		return $this->latitude;
	}

	public function setLatitude(?float $latitude): self {
		$this->latitude = $latitude;

		return $this;
	}

	public function getLongitude(): ?float {
		return $this->longitude;
	}

	public function setLongitude(?float $longitude): self {
		$this->longitude = $longitude;

		return $this;
	}

	public function getTime(): ?DateTimeInterface {
		return $this->time;
	}

	public function setTime(DateTimeInterface $time): self {
		$this->time = $time;

		return $this;
	}

	public function getToken(): ?Token {
		return $this->token;
	}

	public function setToken(Token $token): self {
		$this->token = $token;

		// set the owning side of the relation if necessary
		if ($this !== $token->getIpStackResult()) {
			$token->setIpStackResult($this);
		}

		return $this;
	}
}

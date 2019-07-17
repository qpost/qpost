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
use qpost\Cache\CacheHandler;
use qpost\Util\Util;

/**
 * @ORM\Entity
 */
class UserGigadriveData {
	/**
	 * @access private
	 * @var int $id
	 *
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	private $id;
	/**
	 * @access private
	 * @var int $accountId
	 *
	 * @ORM\Column(type="integer")
	 */
	private $accountId;
	/**
	 * @access private
	 * @var string $token
	 *
	 * @ORM\Column(type="string")
	 */
	private $token;
	/**
	 * @access private
	 * @var DateTime $joinDate
	 *
	 * @ORM\Column(type="datetime")
	 */
	private $joinDate;
	/**
	 * @access private
	 * @var DateTime $lastUpdate
	 *
	 * @ORM\Column(type="datetime")
	 */
	private $lastUpdate;

	/**
	 * @param $code
	 * @return mixed|null
	 */
	public static function getGigadriveTokenFromCode($code) {
		if (Util::isEmpty($code)) return null;

		$n = "gigadriveTokenFromCode_" . $code . "_" . Util::getIP();

		if (CacheHandler::existsInCache($n)) {
			return CacheHandler::getFromCache($n);
		} else {
			$url = "https://gigadrivegroup.com/api/v3/token?secret=" . GIGADRIVE_API_SECRET . "&code=" . urlencode($_GET["code"]);
			$j = @json_decode(@file_get_contents($url), true);

			if (isset($j["success"]) && !Util::isEmpty($j["success"]) && isset($j["token"]) && !Util::isEmpty($j["token"])) {
				$token = $j["token"];

				CacheHandler::setToCache($n, $token, 30 * 60);

				return $token;
			}
		}

		return null;
	}

	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @param int $id
	 * @return UserGigadriveData
	 */
	public function setId(int $id): self {
		$this->id = $id;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getAccountId(): int {
		return $this->accountId;
	}

	/**
	 * @param int $accountId
	 * @return UserGigadriveData
	 */
	public function setAccountId(int $accountId): self {
		$this->accountId = $accountId;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getToken(): string {
		return $this->token;
	}

	/**
	 * @param string $token
	 * @return UserGigadriveData
	 */
	public function setToken(string $token): self {
		$this->token = $token;
		return $this;
	}

	/**
	 * @return DateTime
	 */
	public function getJoinDate(): DateTime {
		return $this->joinDate;
	}

	/**
	 * @param DateTime $joinDate
	 * @return UserGigadriveData
	 */
	public function setJoinDate(DateTime $joinDate): self {
		$this->joinDate = $joinDate;
		return $this;
	}

	/**
	 * @return DateTime
	 */
	public function getLastUpdate(): DateTime {
		return $this->lastUpdate;
	}

	/**
	 * @param DateTime $lastUpdate
	 * @return UserGigadriveData
	 */
	public function setLastUpdate(DateTime $lastUpdate): self {
		$this->lastUpdate = $lastUpdate;
		return $this;
	}
}
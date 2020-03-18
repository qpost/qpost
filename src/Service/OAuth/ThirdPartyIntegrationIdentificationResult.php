<?php
/**
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

namespace qpost\Service\OAuth;

class ThirdPartyIntegrationIdentificationResult {
	/**
	 * @var string|int $id
	 */
	private $id;

	/**
	 * @var string $username
	 */
	private $username;

	/**
	 * @var string|null $avatar
	 */
	private $avatar;

	public function __construct($id, string $username, ?string $avatar) {
		$this->id = $id;
		$this->username = $username;
		$this->avatar = $avatar;
	}

	/**
	 * @return string|int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getUsername(): string {
		return $this->username;
	}

	/**
	 * @return string|null
	 */
	public function getAvatar(): ?string {
		return $this->avatar;
	}
}
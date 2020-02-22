<?php
/**
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
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

namespace qpost\Service\OAuth;

class ThirdPartyIntegrationExchangeCodeResult {
	/**
	 * @var string $accessToken
	 */
	private $accessToken;

	/**
	 * @var string $refreshToken
	 */
	private $refreshToken;

	/**
	 * @var int $expiresIn
	 */
	private $expiresIn;

	/**
	 * @var string $clientId
	 */
	private $clientId;

	/**
	 * @var string $clientSecret
	 */
	private $clientSecret;

	public function __construct(string $accessToken, string $refreshToken, int $expiresIn, string $clientId, string $clientSecret) {
		$this->accessToken = $accessToken;
		$this->refreshToken = $refreshToken;
		$this->expiresIn = $expiresIn;
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
	}

	/**
	 * @return string
	 */
	public function getAccessToken(): string {
		return $this->accessToken;
	}

	/**
	 * @return string
	 */
	public function getRefreshToken(): string {
		return $this->refreshToken;
	}

	/**
	 * @return int
	 */
	public function getExpiresIn(): int {
		return $this->expiresIn;
	}

	/**
	 * @return string
	 */
	public function getClientId(): string {
		return $this->clientId;
	}

	/**
	 * @return string
	 */
	public function getClientSecret(): string {
		return $this->clientSecret;
	}
}
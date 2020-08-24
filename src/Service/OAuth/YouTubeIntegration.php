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

namespace qpost\Service\OAuth;

use DateInterval;
use DateTime;
use Google_Client;
use Google_Service_YouTube;
use qpost\Constants\LinkedAccountService;
use qpost\Entity\LinkedAccount;
use qpost\Entity\User;
use function is_null;

class YouTubeIntegration extends ThirdPartyIntegration {
	public function exchangeCode(string $code): ?ThirdPartyIntegrationExchangeCodeResult {
		$client = $this->getClient();
		$client->fetchAccessTokenWithAuthCode($code);

		$result = $client->getAccessToken();
		$accessToken = $result["access_token"];
		$refreshToken = $result["refresh_token"];
		$expiresIn = $result["expires_in"];

		return new ThirdPartyIntegrationExchangeCodeResult(
			$accessToken,
			$refreshToken,
			$expiresIn,
			$client->getClientId(),
			$client->getClientSecret()
		);
	}

	private function getClient(): Google_Client {
		$client = new Google_Client();
		$client->setClientId($this->getClientId());
		$client->setClientSecret($this->getClientSecret());
		$client->setRedirectUri($this->getRedirectURL());
		$client->setAccessType("offline");
		$client->setApprovalPrompt("force");

		foreach ($this->getScopes() as $scope) {
			$client->addScope($scope);
		}

		return $client;
	}

	public function getScopes(): ?array {
		return ["https://www.googleapis.com/auth/youtube.readonly"];
	}

	public function refreshToken(LinkedAccount $account): ?LinkedAccount {
		$client = $this->getClient();
		$client->setClientId($account->getClientId());
		$client->setClientSecret($account->getClientSecret());
		$client->setAccessToken([
			"access_token" => $account->getAccessToken(),
			"refresh_token" => $account->getRefreshToken(),
			"expires_in" => $this->getExpiresIn($account->getExpiry())
		]);

		$client->fetchAccessTokenWithRefreshToken();

		$result = $client->getAccessToken();
		$accessToken = $result["access_token"];
		$refreshToken = $result["refresh_token"];
		$expiresIn = $result["expires_in"];

		$account->setAccessToken($accessToken);
		$account->setRefreshToken($refreshToken);
		$account->setExpiry((new DateTime("now"))->add(DateInterval::createFromDateString($expiresIn . " seconds")));

		return $account;
	}

	private function getExpiresIn(DateTime $expiry): int {
		return $expiry->getTimestamp() - (new DateTime("now"))->getTimestamp();
	}

	public function identify($credentials): ?ThirdPartyIntegrationIdentificationResult {
		if ($credentials instanceof LinkedAccount) {
			$credentials = $this->refreshToken($credentials);
			if (is_null($credentials)) return null;
		}

		$client = $this->getClient();
		$client->setClientId($credentials->getClientId());
		$client->setClientSecret($credentials->getClientSecret());
		$client->setAccessToken([
			"access_token" => $credentials->getAccessToken(),
			"refresh_token" => $credentials->getRefreshToken(),
			"expires_in" => ($credentials instanceof LinkedAccount) ? $this->getExpiresIn($credentials->getExpiry()) : $credentials->getExpiresIn()
		]);

		$youtube = new Google_Service_YouTube($client);
		$channel = $youtube->channels->listChannels("snippet", ["mine" => true]);

		if (count($channel["items"]) === 0) return null;

		$result = $channel["items"][0];
		$snippet = $result["snippet"];

		$id = $result["id"];
		$username = $snippet["title"];
//		$avatar = isset($snippet["thumbnails"]) && isset($snippet["thumbnails"]["medium"]) && isset($snippet["thumbnails"]["medium"]["url"]) ? $snippet["thumbnails"]["medium"]["url"] : null;
		$avatar = null;

		$this->logger->info("YouTube identification", ["channel" => $channel]);

		return new ThirdPartyIntegrationIdentificationResult(
			$id,
			$username,
			$avatar
		);
	}

	public function getAuthenticationURL(User $user): ?string {
		$client = $this->getClient();

		return $client->createAuthUrl();
	}

	public function getBaseURL(): ?string {
		return parent::getBaseURL(); // TODO: Change the autogenerated stub
	}

	public function getServiceIdentifier(): ?string {
		return LinkedAccountService::YOUTUBE;
	}
}
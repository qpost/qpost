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
use qpost\Constants\LinkedAccountService;
use qpost\Entity\LinkedAccount;
use function implode;
use function is_null;
use function json_decode;
use function sprintf;

class InstagramIntegration extends ThirdPartyIntegration {
	public function getServiceIdentifier(): ?string {
		return LinkedAccountService::INSTAGRAM;
	}

	public function exchangeCode(string $code): ?ThirdPartyIntegrationExchangeCodeResult {
		$baseURL = $this->getBaseURL();
		if (is_null($baseURL)) return null;

		$clientId = $this->getClientId();
		if (is_null($clientId)) return null;

		$clientSecret = $this->getClientSecret();
		if (is_null($clientSecret)) return null;

		$scopes = $this->getScopes();
		if (is_null($scopes)) return null;

		$response = $this->httpClient->post($baseURL . "/access_token", [
			"form_params" => [
				"client_id" => $clientId,
				"client_secret" => $clientSecret,
				"grant_type" => "authorization_code",
				"code" => $code,
				"redirect_uri" => $this->getRedirectURL(),
				"scope" => implode(" ", $scopes)
			]
		]);

		$body = $response->getBody();
		if (is_null($body)) return null;

		$content = $body->getContents();
		$body->close();

		if (is_null($content)) return null;

		$this->logger->info("Code response", [
			"response" => $content
		]);

		$data = @json_decode($content, true);
		$this->logger->info("Code response data", [
			"response" => $data
		]);
		if (!$data) return null;

		if (!isset($data["access_token"])) {
			return null;
		}

		$token = $data["access_token"];

		return $this->getLongLivedToken($token);
	}

	public function getBaseURL(): ?string {
		return "https://api.instagram.com/oauth";
	}

	public function getScopes(): ?array {
		return ["user_profile"];
	}

	public function getLongLivedToken(string $shortLivedToken): ?ThirdPartyIntegrationExchangeCodeResult {
		$response = $this->httpClient->get(sprintf("https://graph.instagram.com/access_token?grant_type=ig_exchange_token&client_secret=%s&access_token=%s", $this->getClientSecret(), $shortLivedToken));

		$body = $response->getBody();
		if (is_null($body)) return null;

		$content = $body->getContents();
		$body->close();
		if (is_null($content)) return null;

		$this->logger->info("instagram response", ["content" => $content]);

		$data = @json_decode($content, true);
		if (!$data) return null;

		if (!isset($data["access_token"]) || !isset($data["expires_in"])) return null;

		return new ThirdPartyIntegrationExchangeCodeResult(
			$data["access_token"],
			null,
			$data["expires_in"] - 900,
			$this->getClientId(),
			$this->getClientSecret()
		);
	}

	public function identify($credentials): ?ThirdPartyIntegrationIdentificationResult {
		$token = $credentials->getAccessToken();

		$response = $this->httpClient->get(sprintf("https://graph.instagram.com/me?fields=id,username&access_token=%s", $token));

		$body = $response->getBody();
		if (is_null($body)) return null;

		$content = $body->getContents();
		$body->close();
		if (is_null($content)) return null;

		$this->logger->info("instagram response", ["content" => $content]);

		$data = @json_decode($content, true);
		if (!$data) return null;

		if (!isset($data["id"]) || !isset($data["username"])) return null;

		$id = $data["id"];
		$username = $data["username"];
		$avatar = null;

		return new ThirdPartyIntegrationIdentificationResult(
			$id,
			$username,
			$avatar
		);
	}

	public function refreshToken(LinkedAccount $account): ?LinkedAccount {
		$response = $this->httpClient->get(sprintf("https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=%s", $account->getAccessToken()));

		$body = $response->getBody();
		if (is_null($body)) return null;

		$content = $body->getContents();
		$body->close();
		if (is_null($content)) return null;

		$this->logger->info("instagram response", ["content" => $content]);

		$data = @json_decode($content, true);
		if (!$data) return null;

		if (!isset($data["access_token"]) || !isset($data["expires_in"])) return null;

		$expiry = new DateTime("now");
		$expiry->add(new DateInterval("PT" . ($data["expires_in"] - 900) . "S"));

		$account->setAccessToken($data["access_token"])
			->setExpiry($expiry);

		return $account;
	}
}
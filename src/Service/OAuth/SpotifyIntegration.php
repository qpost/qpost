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
use qpost\Entity\User;
use function base64_encode;
use function implode;
use function is_null;
use function json_decode;
use function sprintf;
use function urlencode;

class SpotifyIntegration extends ThirdPartyIntegration {
	public function getBaseURL(): ?string {
		return "https://accounts.spotify.com/api";
	}

	public function getAuthenticationURL(User $user): ?string {
		$clientId = $this->getClientId();
		$scopes = $this->getScopes();
		$redirectURL = $this->getRedirectURL();

		if (is_null($clientId) || is_null($redirectURL) || is_null($scopes)) return null;

		return sprintf(
			"https://accounts.spotify.com/authorize?client_id=%s&redirect_uri=%s&response_type=code&scope=%s",
			$clientId,
			urlencode($redirectURL),
			urlencode(implode(" ", $scopes))
		);
	}

	public function getScopes(): ?array {
		return [];
	}

	public function getServiceIdentifier(): ?string {
		return LinkedAccountService::SPOTIFY;
	}

	public function identify($credentials): ?ThirdPartyIntegrationIdentificationResult {
		$token = $credentials->getAccessToken();

		$response = $this->httpClient->get("https://api.spotify.com/v1/me", [
			"headers" => [
				"Authorization" => "Bearer " . $token
			]
		]);

		$body = $response->getBody();
		if (is_null($body)) return null;

		$content = $body->getContents();
		$body->close();
		if (is_null($content)) return null;

		$this->logger->info("spotify response", ["content" => $content]);

		$data = @json_decode($content, true);
		if (!$data) return null;

		if (!isset($data["id"]) || !isset($data["display_name"])) return null;

		$id = $data["id"];
		$username = $data["display_name"];
		$avatar = null;

		if (isset($data["images"]) && is_array($data["images"]) && count($data["images"]) > 0) {
			$avatar = $data["images"][0]["url"];
		}

		return new ThirdPartyIntegrationIdentificationResult(
			$id,
			$username,
			$avatar
		);
	}

	public function refreshToken(LinkedAccount $account): ?LinkedAccount {
		if (!$account->isExpired()) return $account;

		if ($account->getService() !== $this->getServiceIdentifier()) return $account;

		$refreshToken = $account->getRefreshToken();
		if (is_null($refreshToken)) return null;

		$baseURL = $this->getBaseURL();
		if (is_null($baseURL)) return null;

		$clientId = $account->getClientId();
		if (is_null($clientId)) return null;

		$clientSecret = $account->getClientSecret();
		if (is_null($clientSecret)) return null;

		$response = $this->httpClient->post($baseURL . "/token", [
			"form_params" => [
				"grant_type" => "refresh_token",
				"refresh_token" => $refreshToken
			],
			"headers" => [
				"Authorization" => "Basic " . base64_encode($clientId . ":" . $clientSecret)
			]
		]);

		$body = $response->getBody();
		if (is_null($body)) return null;

		$content = $body->getContents();
		$body->close();
		if (is_null($content)) return null;

		$data = @json_decode($content, true);
		if (!$data) return null;

		if (!isset($data["access_token"]) || !isset($data["token_type"]) || !isset($data["expires_in"])) {
			$this->logger->info("Invalid spotify response", ["response" => $data]);
			return null;
		}

		$expiry = new DateTime("now");
		$expiry->add(new DateInterval("PT" . $data["expires_in"] . "S"));

		$this->entityManager->persist(
			$account->setAccessToken($data["access_token"])
				->setExpiry($expiry)
		);

		$this->entityManager->flush();

		return $account;
	}
}
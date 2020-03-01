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

use qpost\Constants\LinkedAccountService;
use qpost\Entity\LinkedAccount;
use qpost\Entity\User;
use function array_merge;
use function is_null;
use function json_decode;
use function json_encode;
use function simplexml_load_string;
use function sprintf;
use function urlencode;

class LastFmIntegration extends ThirdPartyIntegration {
	public function getBaseURL(): ?string {
		return "https://www.last.fm/api";
	}

	public function getServiceIdentifier(): ?string {
		return LinkedAccountService::LASTFM;
	}

	public function getAuthenticationURL(User $user): string {
		return sprintf("https://www.last.fm/api/auth/?api_key=%s&cb=%s", $this->getClientId(), urlencode($this->getRedirectURL()));
	}

	public function exchangeCode(string $code): ?ThirdPartyIntegrationExchangeCodeResult {
		$apikey = $this->getClientId();
		$secret = $this->getClientSecret();

		$response = $this->apiCall([
			"token" => $code,
			"api_key" => $apikey,
			"method" => "auth.getSession"
		], $secret);

		if ($response && isset($response["session"])) {
			$session = $response["session"];

			if (isset($session["key"])) {
				return new ThirdPartyIntegrationExchangeCodeResult(
					$session["key"],
					null,
					null,
					$this->getClientId(),
					$this->getClientSecret()
				);
			}
		}
	}

	private function apiCall(array $params, ?string $secret = null): ?array {
		$response = $this->httpClient->get("https://ws.audioscrobbler.com/2.0/", [
			"query" => array_merge(["api_sig" => $this->generateSignature($params, $secret)], $params)
		]);

		$body = $response->getBody();
		if (is_null($body)) return null;

		$content = $body->getContents();
		$body->close();
		if (is_null($content)) return null;

		$data = @json_decode(json_encode(simplexml_load_string($content)), true);

		$this->logger->info("LastFM Response", [
			"content" => $content,
			"data" => $data
		]);

		$this->logger->info("data", ["data" => $data]);
		if (!$data) return null;

		return $data;
	}

	private function generateSignature(array $params, ?string $secret = null): string {
		if (is_null($secret)) {
			$secret = $this->getClientSecret();
		}

		ksort($params);

		$sig = "";

		foreach ($params as $key => $value) {
			$sig .= $key . $value;
		}

		$sig .= $secret;

		return md5($sig);
	}

	public function identify($credentials): ?ThirdPartyIntegrationIdentificationResult {
		$sessionKey = $credentials->getAccessToken();
		$apikey = $credentials->getClientId();
		$secret = $credentials->getClientSecret();

		$response = $this->apiCall([
			"api_key" => $apikey,
			"method" => "user.getInfo",
			"sk" => $sessionKey
		], $secret);

		if ($response && isset($response["user"])) {
			$user = $response["user"];

			if (isset($user["name"]) && isset($user["image"])) {
				return new ThirdPartyIntegrationIdentificationResult(
					$user["name"], // use username as ID since last.fm does not pass user ID anymore
					$user["name"],
					null
				);
			}
		}

		return null;
	}

	public function refreshToken(LinkedAccount $account): ?LinkedAccount {
		return $account;
	}
}
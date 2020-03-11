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

use qpost\Constants\LinkedAccountService;
use function is_null;
use function json_decode;

class TwitchIntegration extends ThirdPartyIntegration {
	private $apiBaseURL = "https://api.twitch.tv/helix";

	public function getBaseURL(): ?string {
		return "https://id.twitch.tv/oauth2";
	}

	public function getServiceIdentifier(): ?string {
		return LinkedAccountService::TWITCH;
	}

	public function getScopes(): ?array {
		return [];
	}

	public function identify($credentials): ?ThirdPartyIntegrationIdentificationResult {
		$token = $credentials->getAccessToken();

		$response = $this->httpClient->get($this->apiBaseURL . "/users", [
			"headers" => [
				"Authorization" => "Bearer " . $token
			]
		]);

		$body = $response->getBody();
		if (is_null($body)) return null;

		$content = $body->getContents();
		$body->close();
		if (is_null($content)) return null;

		$this->logger->info("Identification response", [
			"response" => $content
		]);

		$data = @json_decode($content, true);
		if (!$data) return null;

		$data = $data["data"][0];

		if (!isset($data["id"]) || !isset($data["login"]) || !isset($data["profile_image_url"])) return null;

		return new ThirdPartyIntegrationIdentificationResult(
			$data["id"],
			$data["login"],
			$data["profile_image_url"]
		);
	}
}
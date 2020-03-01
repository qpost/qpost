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
use qpost\Util\Util;
use function is_null;
use function json_decode;

class MastodonIntegration extends ThirdPartyIntegration {
	private $baseURL = "https://mastodon.social";

	public function getBaseURL(): ?string {
		return $this->baseURL . "/oauth";
	}

	public function getServiceIdentifier(): ?string {
		return LinkedAccountService::MASTODON;
	}

	public function getScopes(): ?array {
		return ["read:accounts"];
	}

	public function identify($credentials): ?ThirdPartyIntegrationIdentificationResult {
		$token = $credentials->getAccessToken();

		$response = $this->httpClient->get($this->baseURL . "/api/v1/accounts/verify_credentials", [
			"headers" => [
				"Authorization" => "Bearer " . $token
			]
		]);

		$body = $response->getBody();
		if (is_null($body)) return null;

		$content = $body->getContents();
		$body->close();
		if (is_null($content)) return null;

		$data = @json_decode($content, true);
		if (!$data) return null;

		if (!isset($data["id"]) || !isset($data["acct"]) || !isset($data["avatar"])) return null;

		$id = $data["id"];
		$username = $data["acct"];
		$avatar = $data["avatar"];

		if (!Util::contains($username, "@")) {
			$username .= "@mastodon.social";
		}

		return new ThirdPartyIntegrationIdentificationResult(
			$id,
			$username,
			$avatar
		);
	}

	public function refreshToken(LinkedAccount $account): ?LinkedAccount {
		return $account;
	}
}
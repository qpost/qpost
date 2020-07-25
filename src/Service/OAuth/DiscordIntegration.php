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

use Gigadrive\Bundle\SymfonyExtensionsBundle\DependencyInjection\Util;
use qpost\Constants\LinkedAccountService;
use function is_null;
use function json_decode;

class DiscordIntegration extends ThirdPartyIntegration {
	private $apiBaseURL = "https://discordapp.com/api";

	public function getBaseURL(): ?string {
		return $this->apiBaseURL . "/oauth2";
	}

	public function getServiceIdentifier(): ?string {
		return LinkedAccountService::DISCORD;
	}

	public function getScopes(): ?array {
		return ["identify"];
	}

	public function identify($credentials): ?ThirdPartyIntegrationIdentificationResult {
		$token = $credentials->getAccessToken();

		$response = $this->httpClient->get($this->apiBaseURL . "/users/@me", [
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

		if (!isset($data["id"]) || !isset($data["username"]) || !isset($data["discriminator"])) return null;

		$id = $data["id"];
		$username = $data["username"];
		$discriminator = $data["discriminator"];
		$avatar = "https://cdn.discordapp.com/embed/avatars/" . ($discriminator % 5) . ".png";

		if (isset($data["avatar"])) {
			$avatarHash = $data["avatar"];

			$avatar = "https://cdn.discordapp.com/avatars/" . $id . "/" . $avatarHash . (Util::startsWith($avatarHash, "a_") ? ".gif" : ".png") . "?size=256";
		}

		return new ThirdPartyIntegrationIdentificationResult(
			$id,
			$username . "#" . $discriminator,
			$avatar
		);
	}
}
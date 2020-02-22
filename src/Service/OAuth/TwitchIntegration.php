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

class TwitchIntegration extends ThirdPartyIntegration {
	private $apiBaseURL = "https://api.twitch.tv/helix";

	public function getBaseURL(): ?string {
		return "https://id.twitch.tv/oauth2";
	}

	public function getServiceIdentifier(): ?string {
		return LinkedAccountService::TWITCH;
	}

	public function getScopes(): ?array {
		return ["identify"];
	}
}
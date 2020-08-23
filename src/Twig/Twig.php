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

namespace qpost\Twig;

use qpost\Constants\LinkedAccountService;
use function array_merge;

class Twig {
	public static function param($parameters = []): array {
		$twigGlobals = [
			"siteName" => "qpost",
			"defaultDescription" => isset($_ENV["DEFAULT_DESCRIPTION"]) ? $_ENV["DEFAULT_DESCRIPTION"] : "",
			"defaultTwitterImage" => isset($_ENV["DEFAULT_TWITTER_IMAGE"]) ? $_ENV["DEFAULT_TWITTER_IMAGE"] : "",
			"postCharacterLimit" => $_ENV["POST_CHARACTER_LIMIT"],
			"increasedPostCharacterLimit" => $_ENV["INCREASED_POST_CHARACTER_LIMIT"],
			"linkedAccountServices" => LinkedAccountService::all(),
			"_POST" => isset($_POST) ? $_POST : [],
			"_GET" => isset($_GET) ? $_GET : [],
			"_COOKIE" => isset($_COOKIE) ? $_COOKIE : [],
			"_SERVER" => isset($_SERVER) ? $_SERVER : [],
			"_SESSION" => isset($_SESSION) ? $_SESSION : [],
			"_ENV" => isset($_ENV) ? $_ENV : []
		];

		return array_merge($twigGlobals, $parameters);
	}
}
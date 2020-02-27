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

namespace qpost\Constants;

class LinkedAccountService {
	/**
	 * @var string DISCORD
	 */
	public const DISCORD = "DISCORD";

	/**
	 * @var string TWITCH
	 */
	public const TWITCH = "TWITCH";

	/**
	 * @var string TWITTER
	 */
	public const TWITTER = "TWITTER";

	/**
	 * @return string[]
	 */
	public static function all(): array {
		$services = [self::DISCORD, self::TWITCH, self::TWITTER];
		$result = [];

		foreach ($services as $service) {
			if (self::isEnabled($service)) {
				$result[] = $service;
			}
		}

		return $result;
	}

	/**
	 * @param string $service
	 * @return bool
	 */
	public static function isEnabled(string $service): bool {
		return isset($_ENV[$service . "_CLIENT_ID"]) && !empty($_ENV[$service . "_CLIENT_ID"]) && isset($_ENV[$service . "_CLIENT_SECRET"]) && !empty($_ENV[$service . "_CLIENT_SECRET"]);
	}
}
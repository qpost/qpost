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

namespace qpost\Constants;

class PrivacyLevel {
	/**
	 * @access public
	 * @var string PUBLIC Privacy level for public accounts, visible to everyone
	 */
	public const PUBLIC = "PUBLIC";
	/**
	 * @access public
	 * @var string PRIVATE Privacy level for private accounts, only visible for followers and followers must be confirmed
	 */
	public const PRIVATE = "PRIVATE";
	/**
	 * @access public
	 * @var string CLOSED Privacy level for closed accounts, only visible for self
	 */
	public const CLOSED = "CLOSED";

	/**
	 * @param string $privacyLevel
	 * @return bool
	 */
	public static function isValid(string $privacyLevel): bool {
		return $privacyLevel === self::PUBLIC || $privacyLevel == self::PRIVATE || $privacyLevel == self::CLOSED;
	}
}
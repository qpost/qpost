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

class APIParameterType {
	/**
	 * @var string CUSTOM
	 */
	public const CUSTOM = "CUSTOM";

	/**
	 * @var string STRING
	 */
	public const STRING = "STRING";

	/**
	 * @var string INTEGER
	 */
	public const INTEGER = "INTEGER";

	/**
	 * @var string BOOLEAN
	 */
	public const BOOLEAN = "BOOLEAN";

	/**
	 * @var string DATE
	 */
	public const DATE = "DATE";

	/**
	 * @var string DATETIME
	 */
	public const DATETIME = "DATETIME";

	/**
	 * @var string BASE64_ARRAY
	 */
	public const BASE64_ARRAY = "BASE64_ARRAY";

	/**
	 * @param string $parameterType
	 * @return string
	 */
	public static function getName(string $parameterType): string {
		switch ($parameterType) {
			case self::STRING:
				return "string";
			case self::INTEGER:
				return "integer";
			case self::BOOLEAN:
				return "boolean";
			case self::DATE:
				return "date";
			case self::DATETIME:
				return "timestamp";
			case self::BASE64_ARRAY:
				return "array of base64 strings";
			default:
				return $parameterType;
		}
	}
}
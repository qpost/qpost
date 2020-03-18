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

namespace qpost\Exception;

use qpost\Constants\APIParameterType;
use function in_array;
use function sprintf;
use function strtolower;
use function substr;

class InvalidParameterTypeException extends RestAPIErrorException {
	/**
	 * @var string $parameterName
	 */
	protected $parameterName;

	/**
	 * @var string $parameterType
	 */
	protected $parameterType;

	public function __construct(string $parameterName, string $parameterType) {
		parent::__construct(self::buildMessage($parameterName, $parameterType));

		$this->parameterName = $parameterName;
		$this->parameterType = $parameterType;
	}

	/**
	 * @param string $parameterName
	 * @param string $parameterType
	 * @return string
	 */
	public static function buildMessage(string $parameterName, string $parameterType): string {
		$humanName = APIParameterType::getName($parameterType);
		$suf = in_array(substr(strtolower($humanName), 0, 1), ["a", "e", "i", "o", "u"]) ? "n" : "";

		return sprintf("'%s' has to be a%s %s.", $parameterName, $suf, $humanName);
	}

	/**
	 * @return string
	 */
	public function getParameterName(): string {
		return $this->parameterName;
	}

	/**
	 * @return string
	 */
	public function getParameterType(): string {
		return $this->parameterType;
	}
}
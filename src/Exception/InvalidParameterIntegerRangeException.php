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

use Symfony\Component\HttpFoundation\Response;
use function sprintf;
use const PHP_INT_MAX;

class InvalidParameterIntegerRangeException extends RestAPIErrorException {
	/**
	 * @var string $parameterName
	 */
	protected $parameterName;

	/**
	 * @var int $minimum
	 */
	protected $minimum;

	/**
	 * @var int $maximum
	 */
	protected $maximum;

	public function __construct(string $parameterName, int $minimum, int $maximum) {
		parent::__construct(self::buildMessage($parameterName, $minimum, $maximum), Response::HTTP_BAD_REQUEST);

		$this->parameterName = $parameterName;
		$this->minimum = $minimum;
		$this->maximum = $maximum;
	}

	/**
	 * @param string $parameterName
	 * @param int $minimum
	 * @param int $maximum
	 * @return string
	 */
	public static function buildMessage(string $parameterName, int $minimum, int $maximum) {
		return $maximum === PHP_INT_MAX ? sprintf("'%s' has to be at least %d.", $parameterName, $minimum) : sprintf("'%s' has to be at least %d and at most %d.", $parameterName, $minimum, $maximum);
	}

	/**
	 * @return string
	 */
	public function getParameterName(): string {
		return $this->parameterName;
	}

	/**
	 * @return int
	 */
	public function getMinimum(): int {
		return $this->minimum;
	}

	/**
	 * @return int
	 */
	public function getMaximum(): int {
		return $this->maximum;
	}
}
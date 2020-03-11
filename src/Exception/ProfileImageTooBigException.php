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

use Exception;
use Throwable;

class ProfileImageTooBigException extends Exception {
	/**
	 * @var int $maxFileSizeMB
	 */
	private $maxFileSizeMB;

	public function __construct(int $maxFileSizeMB, $message = "", $code = 0, Throwable $previous = null) {
		parent::__construct($message, $code, $previous);

		$this->maxFileSizeMB = $maxFileSizeMB;
	}

	/**
	 * @return int
	 */
	public function getMaxFileSizeMB(): int {
		return $this->maxFileSizeMB;
	}
}
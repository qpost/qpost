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

class InvalidParameterStringLengthException extends RestAPIErrorException {
	public function __construct(string $parameterLength, int $minimum, int $maximum) {
		parent::__construct(sprintf("'%s' must be between %d and %d characters long.", $parameterLength, $minimum, $maximum), Response::HTTP_BAD_REQUEST);
	}
}
<?php
/**
 * Copyright (C) 2018-2019 Gigadrive - All rights reserved.
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

namespace qpost\Database;

use Doctrine\ORM\Configuration;
use UnexpectedValueException;

class EntityManager extends \Doctrine\ORM\EntityManager {
	protected static $instance = null;

	public function __construct(array $conn, Configuration $config) {
		if (self::$instance !== null) {
			throw new UnexpectedValueException("Instance of EntityManager already present, use the static instance() function.");
		}

		self::$instance = \Doctrine\ORM\EntityManager::create($conn, $config);
	}

	public static function instance(): \Doctrine\ORM\EntityManager {
		return self::$instance;
	}
}
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

namespace qpost\Twig;

use Twig\Environment;
use Twig\Loader\LoaderInterface;
use UnexpectedValueException;

// originally created by Julian van de Groep <me@flash.moe>
// https://github.com/flashwave/misuzu/blob/master/src/Twig.php
// Licensed under the Apache License 2.0 (https://github.com/flashwave/misuzu/blob/master/LICENSE)
//
// modified by Mehdi Baaboura <mbaaboura@gigadrivegroup.com>: adjusted classes and namespaces to current Twig standards
class Twig extends Environment {
	protected static $instance = null;

	public function __construct(LoaderInterface $loader, array $options = []) {
		if (self::$instance !== null) {
			throw new UnexpectedValueException("Instance of Twig already present, use the static instance() function.");
		}

		parent::__construct($loader, $options);
		self::$instance = $this;
	}

	public static function instance(): Environment {
		return self::$instance;
	}
}
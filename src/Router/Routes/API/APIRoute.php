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

namespace qpost\Router\API;

use Closure;
use qpost\Util\Method;

class APIRoute {
	/**
	 * @var string $path
	 */
	private $path;

	private $closures;

	public function __construct($path) {
		$this->path = $path;
		$this->closures = [];
	}

	/**
	 * @return string
	 */
	public function getPath(): string {
		return $this->path;
	}

	/**
	 * @param string $path
	 * @return APIRoute
	 */
	public function setPath(string $path): self {
		$this->path = $path;
		return $this;
	}

	/**
	 * @param string $method
	 * @param Closure $closure
	 */
	public function registerClosure(string $method, Closure $closure): void {
		if (!array_key_exists($method, $this->closures)) {
			$this->closures[$method] = $closure;
		}
	}

	/**
	 * @param string $method
	 * @return Closure|null
	 */
	public function getClosure(string $method): ?Closure {
		if (array_key_exists($method, $this->closures)) {
			return $this->closures[$method];
		}

		return null;
	}

	/**
	 * @return string
	 */
	public function allowedMethodsString(): string {
		if (count($this->closures) > 0) {
			$s = "";

			foreach ($this->closures as $method => $closure) {
				$s .= $method . ",";
			}

			$s .= Method::OPTIONS;

			return $s;
		} else {
			return Method::OPTIONS;
		}
	}
}
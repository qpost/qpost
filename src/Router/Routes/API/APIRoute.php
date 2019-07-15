<?php

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
<?php

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
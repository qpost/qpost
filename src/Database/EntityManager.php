<?php

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
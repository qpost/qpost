<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use qpost\Database\EntityManager;

require_once __DIR__ . "/src/bootstrap.php";

return ConsoleRunner::createHelperSet(EntityManager::instance());

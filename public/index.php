<?php

require_once __DIR__ . "/../config.php";

if (DEBUG === true) {
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	ini_set("display_startup_errors", 1);
} else {
	error_reporting(0);
	ini_set("display_errors", 0);
	ini_set("display_startup_errors", 0);
}

require_once "../src/Load/Load.php";
require_once "../src/Router/Router.php";

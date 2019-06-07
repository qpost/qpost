<?php

if (!(php_sapi_name() === 'cli')) {
	echo "Please run this script with the PHP CLI." . PHP_EOL;
	exit(1);
}

require __DIR__ . "/../src/Load/Load.php";

function println($line) {
	echo $line . PHP_EOL;
}

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

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
	# Throw error exception to trigger exception handler on errors

	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

set_exception_handler(function (Throwable $e) {
	if (DEBUG === true) {
		if (!(php_sapi_name() === 'cli')) {
			// WEB BROWSER

			http_response_code(500);

			echo "Uncaught Exception (" . get_class($e) . ") in " . $e->getFile() . " line " . $e->getLine() . ":" . "<br/><br/>";
			echo "<b>" . $e->getMessage() . "</b><br/><br/>";
			echo str_replace(PHP_EOL, "<br/>", $e->getTraceAsString());
		} else {
			// PHP CLI
			echo "Uncaught Exception (" . get_class($e) . ") in " . $e->getFile() . " line " . $e->getLine() . ":" . PHP_EOL;
			echo $e->getMessage() . PHP_EOL;
			echo $e->getTraceAsString();
		}
		exit(1);
	} else {
		echo "Internal Server Error.";
		exit(1);
	}
});

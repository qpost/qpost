<?php

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

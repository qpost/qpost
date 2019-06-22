<?php

set_exception_handler(function (Throwable $e) {
	if (DEBUG === true) {
		echo "Uncaught Exception (" . get_class($e) . ") in " . $e->getFile() . " line " . $e->getLine() . ":" . "<br/><br/>";
		echo "<b>" . $e->getMessage() . "</b><br/><br/>";
		echo str_replace(PHP_EOL, "<br/>", $e->getTraceAsString());
		exit(1);
	} else {
		echo "Internal Server Error.";
		exit(1);
	}
});

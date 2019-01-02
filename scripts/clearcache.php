<?php

if((isset($_SERVER["DOCUMENT_ROOT"]) && !empty($_SERVER["DOCUMENT_ROOT"])) || (isset($_SERVER["HTTP_HOST"]) && !empty($_SERVER["HTTP_HOST"]))){
	echo "This script may only be run from the command line!";
	exit();
}

require_once "../src/Load/Load.php";

\CacheHandler::clearCache();
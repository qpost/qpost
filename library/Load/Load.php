<?php

# Loads all library files

date_default_timezone_set("UTC");

if(isset($_SERVER["HTTP_HOST"]) && (explode(":",$_SERVER["HTTP_HOST"])[0] == "localhost" || explode(":",$_SERVER["HTTP_HOST"])[0] == "127.0.0.1")){
	require_once __DIR__ . "/../../../twitter-config.php";
} else {
	require_once __DIR__ . "/../../config.php";
}

require_once __DIR__ . "/../../vendor/autoload.php";

require_once __DIR__ . "/../Account/User.php";
require_once __DIR__ . "/../Cache/CacheHandler.php";
require_once __DIR__ . "/../Shutdown/Shutdown.php";
require_once __DIR__ . "/../Database/Database.php";
require_once __DIR__ . "/../Util/Util.php";
require_once __DIR__ . "/../Lime/App.php";
require_once __DIR__ . "/../Session/session.php";

/**
 * Alias for i18n::getTranslatedMessage()
 * 
 * @param string $phrase
 * @param arrray $variables
 * @return string
 */
/*function tr($phrase,$variables = null){
	return i18n::getTranslatedMessage($phrase,$variables);
}*/

Util::cleanupTempFolder();
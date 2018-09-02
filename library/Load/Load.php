<?php

# Loads all library files

date_default_timezone_set("UTC");

ini_set("session.cookie_lifetime",(60*60*24)*3);
ini_set("session.gc_maxlifetime",(60*60*24)*3);

ini_set("max_execution_time", 300);

if(isset($_SERVER["HTTP_HOST"]) && (explode(":",$_SERVER["HTTP_HOST"])[0] == "localhost" || explode(":",$_SERVER["HTTP_HOST"])[0] == "127.0.0.1")){
	require_once __DIR__ . "/../../../twitter-config.php";
} else {
	require_once __DIR__ . "/../../config.php";
}

require_once __DIR__ . "/../../vendor/autoload.php";

require_once __DIR__ . "/../Account/User.php";
require_once __DIR__ . "/../Account/Suspension.php";
require_once __DIR__ . "/../Account/IPInformation.php";
require_once __DIR__ . "/../Account/Token.php";
require_once __DIR__ . "/../Cache/CacheHandler.php";
require_once __DIR__ . "/../Shutdown/Shutdown.php";
require_once __DIR__ . "/../Database/Database.php";
require_once __DIR__ . "/../Feed/FeedEntry.php";
require_once __DIR__ . "/../Mail/Templates/autoload.php";
require_once __DIR__ . "/../Util/Util.php";
require_once __DIR__ . "/../Lime/App.php";
require_once __DIR__ . "/../Media/MediaFile.php";
require_once __DIR__ . "/../Session/session.php";

//\CacheHandler::clearCache();

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
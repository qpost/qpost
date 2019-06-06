<?php

# Loads all library files

date_default_timezone_set("UTC");

ini_set("session.cookie_lifetime",(60*60*24)*3);
ini_set("session.gc_maxlifetime",(60*60*24)*3);

ini_set("max_execution_time", 300);

session_start();

require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../../vendor/autoload.php";

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
if(Util::isLoggedIn()){
	$token = Token::getTokenById($_COOKIE["sesstoken"]);

	if(!is_null($token)){
		$token->renew();
	}
}
<?php

# Loads all library files

require_once __DIR__ . "/exception_handler.php";

date_default_timezone_set("UTC");

ini_set("session.cookie_lifetime", (60 * 60 * 24) * 3);
ini_set("session.gc_maxlifetime", (60 * 60 * 24) * 3);

ini_set("max_execution_time", 300);

session_start();

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../vendor/autoload.php";

use qpost\Account\Token;
use qpost\Twig\Twig;
use qpost\Twig\TwigExtension;
use qpost\Util\Util;
use Twig\Loader\FilesystemLoader;

//use qpost\Cache\CacheHandler

//CacheHandler::clearCache();

/**
 * Alias for i18n::getTranslatedMessage()
 *
 * @param string $phrase
 * @param array $variables
 * @return string
 */
/*function tr($phrase,$variables = null){
	return i18n::getTranslatedMessage($phrase,$variables);
}*/

Util::cleanupTempFolder();
if (Util::isLoggedIn()) {
	$token = Token::getTokenById($_COOKIE["sesstoken"]);

	if (!is_null($token)) {
		$token->renew();
	}
}

function currentRoute() {
	return isset($_SERVER["PATH_INFO"]) && trim($_SERVER["PATH_INFO"]) != "" ? $_SERVER["PATH_INFO"] : strtok($_SERVER["REQUEST_URI"], "?");
}

# Loads Twig

$loader = new FilesystemLoader(__DIR__ . "/../templates");
$twig = new Twig($loader, [
	"debug" => DEBUG,
	"cache" => DEBUG ? sys_get_temp_dir() : false
]);

$twig->addExtension(new TwigExtension());

function twig_render(string $fileName, array $variables = []): string {
	global $twig;

	$twigGlobals = [
		"currentUser" => Util::getCurrentUser(),
		"siteName" => SITE_NAME,
		"defaultDescription" => DEFAULT_DESCRIPTION,
		"postCharacterLimit" => POST_CHARACTER_LIMIT,
		"verifiedPostCharacterLimit" => VERIFIED_POST_CHARACTER_LIMIT,
		"csrfToken" => CSRF_TOKEN,
		"_POST" => $_POST,
		"_GET" => $_GET
	];

	return $twig->render($fileName, array_merge($twigGlobals, $variables));
}
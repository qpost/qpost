<?php

use qpost\Util\Util;
use Riimu\Kit\CSRF\CSRFHandler;
use Riimu\Kit\CSRF\InvalidCSRFTokenException;

$app = new Lime\App();
$app["config.begin_point"] = microtime();
$app["config.site"] = array(
    "name" => SITE_NAME
);

$app->path("assets", "assets/");
$app->path("routes",__DIR__ . "/Routes");
$app->path("views",__DIR__ . "/Views");
$app->path("public", __DIR__ . "/../../public");

$csrf = new CSRFHandler();

// dont do CSRF check on API requests
if(!(substr($app->route,0,5) === "/api/")){
	try {
		$csrf->validateRequest(true);
	} catch (InvalidCSRFTokenException $e) {
		header("HTTP/1.0 400 Bad Request");
		exit("400 Bad Request");
	}
}

define("CSRF_TOKEN",$csrf->getToken());

if(Util::isLoggedIn()){
	$currentUser = Util::getCurrentUser();

	if(!is_null($currentUser) && $currentUser->isSuspended()){
		unset($_COOKIE["sesstoken"]);
	}
}

require_once $app->path("routes:Home.php");
require_once $app->path("routes:Notifications.php");
require_once $app->path("routes:Status.php");
require_once $app->path("routes:Out.php");
require_once $app->path("routes:Login.php");
require_once $app->path("routes:Logout.php");
require_once $app->path("routes:Profile.php");
require_once $app->path("routes:Account.php");
require_once $app->path("routes:Messages.php");
require_once $app->path("routes:Requests.php");
require_once $app->path("routes:Edit.php");
require_once $app->path("routes:Search.php");
require_once $app->path("routes:Features.php");
require_once $app->path("routes:Discover.php");
require_once $app->path("routes:ScriptsRoute.php");
require_once $app->path("routes:Sitemap.php");
require_once $app->path("routes:Register.php");
require_once $app->path("routes:Delete.php");

require_once $app->path("routes:Cronjobs/DeleteStaleAccounts.php");

require_once $app->path("routes:API/autoload.php");

$app->on("after",function() {
	if(!(substr($this->route,0,5) === "/api/")){
		if($this->response->status == "404"){
			$this->response->body = $this->render("views:ErrorPages/404.php with views:Layout.php",[
				"title" => "Error 404: Page not found",
				"subtitle" => "Error 404: Page not found"
			]);
		}
	}
});

$path = currentRoute();
$filePath = $app->path("public:" . $path);
if ($path !== "/" && file_exists($filePath)) {
	$app->bind($path, function () {
		global $path;
		global $filePath;
		$this->response->mime = pathinfo($path, PATHINFO_EXTENSION);
		return file_get_contents($filePath);
	});
}

$app->run();
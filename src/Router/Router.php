<?php

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

require $app->path("routes:Home.php");
require $app->path("routes:Notifications.php");
require $app->path("routes:Status.php");
require $app->path("routes:Out.php");
require $app->path("routes:Login.php");
require $app->path("routes:Logout.php");
require $app->path("routes:Profile.php");
require $app->path("routes:Account.php");
require $app->path("routes:Messages.php");
require $app->path("routes:Requests.php");
require $app->path("routes:Edit.php");
require $app->path("routes:Search.php");
require $app->path("routes:Features.php");
require $app->path("routes:Discover.php");
require $app->path("routes:ScriptsRoute.php");
require $app->path("routes:Sitemap.php");
require $app->path("routes:NightMode.php");
require $app->path("routes:Register.php");
require $app->path("routes:Delete.php");

require $app->path("routes:Cronjobs/DeleteStaleAccounts.php");

require $app->path("routes:API/autoload.php");

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
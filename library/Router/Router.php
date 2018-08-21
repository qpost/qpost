<?php

$app = new Lime\App();
$app["config.begin_point"] = microtime();
$app["config.site"] = array(
    "name" => SITE_NAME
);

$app->path("assets",__DIR__ . "/../../assets");
$app->path("routes",__DIR__ . "/Routes");
$app->path("views",__DIR__ . "/Views");

$csrf = new \Riimu\Kit\CSRF\CSRFHandler();

try {
	$csrf->validateRequest(true);
} catch(\Riimu\Kit\CSRF\InvalidCSRFTokenException $e){
	header("HTTP/1.0 400 Bad Request");
	exit("400 Bad Request");
}

define("CSRF_TOKEN",$csrf->getToken());

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

require $app->path("routes:Cronjobs/UpdateUserData.php");

$app->on("after",function() {
	if($this->response->status == "404"){
		$data = array(
			"title" => "Error 404: Page not found",
			"subtitle" => "Error 404: Page not found"
		);

		$this->response->body = $this->render("views:ErrorPages/404.php with views:Layout.php",$data);
	}
});

$app->run();
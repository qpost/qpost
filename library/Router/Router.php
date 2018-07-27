<?php

$app = new Lime\App();
$app["config.begin_point"] = microtime();
$app["config.site"] = array(
    "name" => "twitterClone"
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
require $app->path("routes:Login.php");
require $app->path("routes:Logout.php");

$app->on("after",function() {
	if($this->response->status == "404"){
		$data = array(
			"title" => "Not found"
		);

		$this->response->body = $this->render("views:ErrorPages/404.php with views:Layout.php",$data);
	}
});

$app->run();
<?php

namespace qpost\Router;

use Lime\App;
use qpost\Account\Token;
use qpost\Database\EntityManager;
use qpost\Util\Util;
use Riimu\Kit\CSRF\CSRFHandler;
use Riimu\Kit\CSRF\InvalidCSRFTokenException;

function create_route($path, $callable) {
	if (php_sapi_name() === 'cli') {
		return;
	}

	global $app;

	$app->bind($path, $callable);
}

function create_route_post($path, $callable) {
	if (php_sapi_name() === 'cli') {
		return;
	}

	global $app;

	$app->post($path, $callable);
}

function create_route_get($path, $callable) {
	if (php_sapi_name() === 'cli') {
		return;
	}

	global $app;

	$app->get($path, $callable);
}

if (!(php_sapi_name() === 'cli')) {
	$app = new App();
	$app["config.begin_point"] = microtime();
	$app["config.site"] = array(
		"name" => SITE_NAME
	);

	$app->path("assets", "assets/");
	$app->path("routes", __DIR__ . "/Routes");
	$app->path("views", __DIR__ . "/Views");
	$app->path("public", __DIR__ . "/../../public");

	$csrf = new CSRFHandler();

	// dont do CSRF check on API requests
	if (!(substr($app->route, 0, 5) === "/api/")) {
		try {
			$csrf->validateRequest(true);
		} catch (InvalidCSRFTokenException $e) {
			header("HTTP/1.0 400 Bad Request");
			exit("400 Bad Request");
		}
	}

	define("CSRF_TOKEN", $csrf->getToken());

	if (Util::isLoggedIn()) {
		# Updates current token
		/**
		 * @var Token $token
		 */
		$token = EntityManager::instance()->getRepository(Token::class)->findOneBy(["id" => $_COOKIE["sesstoken"]]);

		if (!is_null($token)) {
			$token->renew();
		}

		$currentUser = Util::getCurrentUser();

		if (!is_null($currentUser) && $currentUser->isSuspended()) {
			unset($_COOKIE["sesstoken"]);
		}
	}

	# Autoload all routes
	foreach (array_merge(glob(__DIR__ . "/Routes/**/*.php"), glob(__DIR__ . "/Routes/*.php")) as $path) {
		require_once $path;
	}

	$app->on("after", function () {
		if (!(substr($this->route, 0, 5) === "/api/")) {
			if ($this->response->status == "404") {
				$this->response->body = twig_render("pages/error/404.html.twig", [
					"title" => "Error 404: Page not found"
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
}
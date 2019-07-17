<?php
/**
 * Copyright (C) 2018-2019 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpo.st
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://gnu.org/licenses/>
 */

namespace qpost\Router;

use Lime\App;
use qpost\Account\Token;
use qpost\Database\EntityManager;
use qpost\Util\Util;
use Riimu\Kit\CSRF\CSRFHandler;
use Riimu\Kit\CSRF\InvalidCSRFTokenException;
use function qpost\Router\API\api_find_route;
use function qpost\Router\API\api_headers;
use function qpost\Router\API\registered_api_routes;

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

function dir_contents($dir, &$results = array()) {
	$files = scandir($dir);

	foreach ($files as $key => $value) {
		$path = realpath($dir . DIRECTORY_SEPARATOR . $value);
		if (!is_dir($path)) {
			$results[] = $path;
		} else if ($value != "." && $value != "..") {
			dir_contents($path, $results);
			$results[] = $path;
		}
	}

	return $results;
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
	foreach (dir_contents(__DIR__ . "/Routes/") as $path) {
		if (Util::endsWith($path, ".php", true)) {
			require_once $path;
		}
	}

	foreach (registered_api_routes() as $route) {
		$app->bind("/api" . $route->getPath(), function () {
			$route = api_find_route(currentRoute());

			if (is_null($route)) {
				return false;
			}

			if (isset($_SERVER["REQUEST_METHOD"])) {
				$usedMethod = $_SERVER["REQUEST_METHOD"];

				api_headers($this);

				if ($usedMethod === "OPTIONS") {
					$this->response->status = "204";
					return false;
				}

				$closure = $route->getClosure($usedMethod);
				if (is_null($closure)) {
					$this->response->status = "405";
					$this->response->headers[] = "Allow: " . $route->allowedMethodsString();

					return json_encode(["error" => "Method Not Allowed"]);
				} else {
					$this->response->status = "200";
					$output = $closure->call($this);

					return $output;
				}
			}
		});
	}

	$app->on("after", function () {
		if (!(substr($this->route, 0, 5) === "/api/")) {
			if ($this->response->status == "404") {
				$this->response->body = react();
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
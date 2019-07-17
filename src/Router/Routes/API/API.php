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

namespace qpost\Router\API;

use Closure;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use Lime\App;
use qpost\Account\Token;
use qpost\Database\EntityManager;
use qpost\Util\Util;

$routes = [];

/**
 * @return APIRoute[]
 */
function registered_api_routes(): array {
	global $routes;

	return $routes;
}

/**
 * @param APIRoute $route
 */
function api_register_route(APIRoute $route): void {
	global $routes;

	if (!api_find_route($route->getPath()) === null) {
		$routes = Util::removeFromArray($routes, $route);
	}

	$routes[] = $route;
}

/**
 * @param string $path
 * @return APIRoute|null
 */
function api_find_route(string $path): ?APIRoute {
	foreach (registered_api_routes() as $route) {
		if (Util::startsWith($path, "/api", true)) $path = substr($path, strlen("/api"));

		if (strtolower($path) === strtolower($route->getPath())) {
			return $route;
		}
	}

	return null;
}

/**
 * @param string $method
 * @param string $path
 * @param Closure $closure
 */
function api_create_route(string $method, string $path, Closure $closure): void {
	if (Util::startsWith($path, "/api", true)) $path = substr($path, strlen("/api"));

	$apiRoute = api_find_route($path);
	if (is_null($apiRoute)) $apiRoute = new APIRoute($path);

	$apiRoute->registerClosure($method, $closure);

	api_register_route($apiRoute);
}

/**
 * Sets all required headers for an API respones (mime type, CORS, etc.)
 *
 * @param App $app
 * @return void
 */
function api_headers(App $app): void {
	$apiRoute = api_find_route(currentRoute());

	// mime type json
	$app->response->mime = "json";

	// CORS
	$app->response->headers[] = "Access-Control-Allow-Origin: *";
	$app->response->headers[] = "Access-Control-Allow-Headers: Authorization,Content-Type,DNT,X-CustomHeader,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control";

	if (!is_null($apiRoute)) {
		$app->response->headers[] = "Access-Control-Allow-Methods: " . $apiRoute->allowedMethodsString();
	} else {
		$app->response->headers[] = "Access-Control-Allow-Methods: GET,POST,OPTIONS,DELETE,PUT,PATCH";
	}
}

function api_auth_check(App $app): bool {
	$token = api_get_token();

	if (!is_null($token)) {
		return true;
	}

	$app->response->status = "401";
	$app->response->body = json_encode(["error" => "Invalid token"]);
	return false;
}

/**
 * @return Token|null
 */
function api_get_token(): ?Token {
	$header = Util::getAuthorizationHeader();

	if (!is_null($header) && !Util::isEmpty($header) && Util::startsWith($header, "Token ")) {
		$entityManager = EntityManager::instance();

		/**
		 * @var Token $token
		 */
		$token = $entityManager->getRepository(Token::class)->findOneBy(["id" => substr($header, strlen("Token "))]);

		if (!is_null($token)) {
			if (!$token->isExpired()) {
				return $token;
			}
		}
	}

	return null;
}

/**
 * Gets the data from the current API request
 *
 * @param App $app
 * @return array
 */
function api_request_data(App $app): array {
	if ($_SERVER["REQUEST_METHOD"] === "GET") {
		return $_GET;
	} else {
		$input = file_get_contents("php://input");

		$json = json_decode($input, true);
		if (is_null($json)) {
			parse_str($input, $parsed);

			return $parsed ? $parsed : [];
		} else {
			return $json;
		}
	}
}

$serializer = SerializerBuilder::create()
	->setDebug(DEBUG)
	->setCacheDir(sys_get_temp_dir())
	->setPropertyNamingStrategy(
		new SerializedNameAnnotationStrategy(
			new IdenticalPropertyNamingStrategy()
		)
	)
	->build();

/**
 * @param $object
 * @return array
 */
function api_prepare_object($object): array {
	global $serializer;

	$context = new SerializationContext();
	$context->setSerializeNull(true);

	$string = $serializer->serialize($object, "json", $context);
	$array = json_decode($string, true);

	return $array;
}
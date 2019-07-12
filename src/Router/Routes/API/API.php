<?php

namespace qpost\Router\API;

use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use Lime\App;
use qpost\Account\Token;
use qpost\Database\EntityManager;
use qpost\Util\Util;

/**
 * Sets all required headers for an API respones (mime type, CORS, etc.)
 *
 * @param App $app
 * @return void
 */
function api_headers(App $app): void {
	// mime type json
	$app->response->mime = "json";

	// CORS
	$app->response->headers[] = "Access-Control-Allow-Origin: *";
	$app->response->headers[] = "Access-Control-Allow-Methods: GET,POST,OPTIONS,DELETE,PUT,PATCH";
	$app->response->headers[] = "Access-Control-Allow-Headers: Authorization,Content-Type,DNT,X-CustomHeader,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control";
}

/**
 * Validates that the proper HTTP method was used
 *
 * @param App $app
 * @param string $method The prefered method
 * @param bool $authCheck Whether or not to validate the Token and cancel the request if it is invalid
 * @return bool True or false, depending on the used method
 */
function api_method_check(App $app, string $method, bool $authCheck = true): bool {
	if (isset($_SERVER["REQUEST_METHOD"])) {
		$usedMethod = $_SERVER["REQUEST_METHOD"];

		api_headers($app);

		if ($usedMethod === "OPTIONS") {
			$app->response->status = "204";
			return false;
		}

		if ($usedMethod !== $method) {
			$app->response->status = "405";
			$app->response->headers[] = "Allow: " . $method . ", OPTIONS";

			return false;
		} else {
			if ($authCheck) {
				$token = api_get_token();

				if (!is_null($token)) {
					return true;
				}

				$app->response->status = "401";
				$app->response->body = json_encode(["error" => "Invalid token"]);
				return false;
			} else {
				$app->response->status = "200";
				return true;
			}
		}
	}

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
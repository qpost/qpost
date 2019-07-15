<?php

namespace qpost\Router\API\User;

use qpost\Account\User;
use qpost\Util\Method;
use qpost\Util\Util;
use function qpost\Router\API\api_create_route;
use function qpost\Router\API\api_get_token;
use function qpost\Router\API\api_prepare_object;
use function qpost\Router\API\api_request_data;

api_create_route(Method::GET, "/user/info", function () {
	$requestData = api_request_data($this);
	$token = api_get_token();
	$currentUser = !is_null($token) ? $token->getUser() : null;

	if (isset($requestData["user"])) {
		if (!Util::isEmpty($requestData["user"])) {
			$user = User::getUser($requestData["user"]);

			if (!is_null($user) && $user->mayView($currentUser)) {
				return json_encode(api_prepare_object($user));
			} else {
				return json_encode(["error" => "Unknown user"]);
			}
		} else {
			return json_encode(["error" => "Invalid user"]);
		}
	} else {
		return json_encode(["error" => "Invalid user"]);
	}
});
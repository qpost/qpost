<?php

namespace qpost\Router\API\Token;

use qpost\Util\Method;
use function qpost\Router\API\api_auth_check;
use function qpost\Router\API\api_create_route;
use function qpost\Router\API\api_get_token;
use function qpost\Router\API\api_prepare_object;

api_create_route(Method::POST, "/token/verify", function () {
	if (api_auth_check($this)) {
		$token = api_get_token();
		$user = $token->getUser();

		if (!$user->isSuspended()) {
			if (!$token->isExpired()) {
				$token->renew();

				return json_encode(["status" => "Token valid", "user" => api_prepare_object($user)]);
			} else {
				return json_encode(["error" => "Token expired"]);
			}
		} else {
			return json_encode(["error" => "User suspended"]);
		}
	} else {
		return "";
	}
});
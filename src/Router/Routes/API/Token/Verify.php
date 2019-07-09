<?php

namespace qpost\Router\API\Token;

use function qpost\Router\API\api_get_token;
use function qpost\Router\API\api_method_check;
use function qpost\Router\API\api_prepare_object;
use function qpost\Router\create_route;

create_route("/api/token/verify", function () {
	if(api_method_check($this,"POST")){
		$token = api_get_token();
		$user = $token->getUser();

		if (!$user->isSuspended()) {
			if (!$token->isExpired()) {
				$token->renew();

				return json_encode(["status" => "Token valid", "user" => api_prepare_object($user)]);
			} else {
				return json_encode(["status" => "Token expired"]);
			}
		} else {
			return json_encode(["error" => "User suspended"]);
		}
	} else {
		return "";
	}
});
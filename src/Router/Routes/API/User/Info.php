<?php

namespace qpost\Router\API\User;

use qpost\Account\User;
use qpost\Util\Util;
use function qpost\Router\API\api_method_check;
use function qpost\Router\API\api_prepare_object;
use function qpost\Router\API\api_request_data;
use function qpost\Router\create_route;

create_route("/api/user/info", function () {
	if(api_method_check($this,"GET")){
		$requestData = api_request_data($this);

		if (isset($requestData["user"])) {
			if (!Util::isEmpty($requestData["user"])) {
				$user = User::getUser($requestData["user"]);

				if (!is_null($user)) {
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
	} else {
		return "";
	}
});
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
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
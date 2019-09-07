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

namespace qpost\Router\API\Post;

use DateTime;
use qpost\Database\EntityManager;
use qpost\Feed\FeedEntry;
use qpost\Feed\FeedEntryType;
use qpost\Util\Method;
use function is_string;
use function json_encode;
use function qpost\Router\API\api_auth_check;
use function qpost\Router\API\api_create_route;
use function qpost\Router\API\api_get_token;
use function qpost\Router\API\api_prepare_object;
use function qpost\Router\API\api_request_data;
use function strlen;
use const POST_CHARACTER_LIMIT;
use const VERIFIED_POST_CHARACTER_LIMIT;

api_create_route(Method::POST, "/post", function () {
	if (api_auth_check($this)) {
		$requestData = api_request_data($this);
		$token = api_get_token();
		$currentUser = $token->getUser();

		if (isset($requestData["message"])) {
			if (is_string($requestData["message"])) {
				$message = $requestData["message"];
				$entityManager = EntityManager::instance();

				/**
				 * @var int $characterLimit
				 */
				$characterLimit = $currentUser->isVerified() ? VERIFIED_POST_CHARACTER_LIMIT : POST_CHARACTER_LIMIT;

				if (strlen($message) >= 1 && strlen($message) <= $characterLimit) {
					$feedEntry = new FeedEntry();
					$feedEntry->setUser($currentUser)
						->setText($message)
						->setSessionId($token->getId())
						->setType(FeedEntryType::POST)
						->setNSFW(false)// TODO
						->setTime(new DateTime("now"));

					$entityManager->persist($feedEntry);
					$entityManager->flush();

					return json_encode(["post" => api_prepare_object($feedEntry)]);
				} else {
					$this->response->status = "400";
					return json_encode(["error" => "The message must be between 1 and " . $characterLimit . " characters long."]);
				}
			} else {
				$this->response->status = "400";
				return json_encode(["error" => "'message' has to be a string."]);
			}
		} else {
			$this->response->status = "400";
			return json_encode(["error" => "'to' is required."]);
		}
	} else {
		return "";
	}
});
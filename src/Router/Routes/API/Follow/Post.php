<?php

namespace qpost\Router\API\Follow;

use qpost\Account\Follower;
use qpost\Account\FollowRequest;
use qpost\Account\FollowStatus;
use qpost\Account\PrivacyLevel;
use qpost\Account\User;
use qpost\Database\EntityManager;
use qpost\Util\Method;
use function qpost\Router\API\api_auth_check;
use function qpost\Router\API\api_create_route;
use function qpost\Router\API\api_get_token;
use function qpost\Router\API\api_request_data;

api_create_route(Method::POST, "/follow", function () {
	if (api_auth_check($this)) {
		$requestData = api_request_data($this);
		$token = api_get_token();
		$currentUser = $token->getUser();

		if (isset($requestData["to"])) {
			if (is_numeric($requestData["to"])) {
				$entityManager = EntityManager::instance();

				/**
				 * @var User $to
				 */
				$to = $entityManager->getRepository(User::class)->findOneBy(["id" => $requestData["to"]]);

				if (!is_null($to) && !$to->getPrivacyLevel() === PrivacyLevel::CLOSED) {
					if (!Follower::isFollowing($currentUser, $to)) {
						if ($to->getPrivacyLevel() === PrivacyLevel::PUBLIC) {
							if (Follower::follow($currentUser, $to)) {
								return json_encode(["status" => FollowStatus::FOLLOWING]);
							} else {
								$this->response->status = "500";
								return json_encode(["error" => "An error occurred."]);
							}
						} else if ($to->getPrivacyLevel() === PrivacyLevel::PRIVATE) {
							if (!FollowRequest::hasSentFollowRequest($currentUser, $to)) {
								if (Follower::follow($currentUser, $to)) {
									return json_encode(["status" => FollowStatus::PENDING]);
								} else {
									$this->response->status = "500";
									return json_encode(["error" => "An error occurred."]);
								}
							} else {
								$this->response->status = "409";
								return json_encode(["error" => "You have already sent a request to this user."]);
							}
						} else {
							// should not happen
							$this->response->status = "500";
							return json_encode(["error" => "An error occurred."]);
						}
					} else {
						$this->response->status = "409";
						return json_encode(["error" => "You are already following this user."]);
					}
				} else {
					$this->response->status = "404";
					return json_encode(["error" => "The requested user could not be found."]);
				}
			} else {
				$this->response->status = "400";
				return json_encode(["error" => "'to' has to be an integer."]);
			}
		} else {
			$this->response->status = "400";
			return json_encode(["error" => "'to' is required."]);
		}
	} else {
		return "";
	}
});
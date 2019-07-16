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

api_create_route(Method::DELETE, "/follow", function () {
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

				if (!is_null($to) && $to->getPrivacyLevel() !== PrivacyLevel::CLOSED) {
					if ($to->getPrivacyLevel() === PrivacyLevel::PUBLIC) {
						if (Follower::isFollowing($currentUser, $to)) {
							if (Follower::unfollow($currentUser, $to)) {
								return json_encode(["status" => FollowStatus::NOT_FOLLOWING]);
							} else {
								$this->response->status = "500";
								return json_encode(["error" => "An error occurred."]);
							}
						} else {
							$this->response->status = "404";
							return json_encode(["error" => "The requested resource could not be found."]);
						}
					} else if ($to->getPrivacyLevel() === PrivacyLevel::PRIVATE) {
						if (FollowRequest::hasSentFollowRequest($currentUser, $to)) {
							/**
							 * @var FollowRequest $followRequest
							 */
							$followRequest = $entityManager->getRepository(FollowRequest::class)->findOneBy([
								"from" => $currentUser,
								"to" => $to
							]);

							if (!is_null($followRequest)) {
								$entityManager->remove($followRequest);
								$entityManager->flush();

								return json_encode(["status" => FollowStatus::NOT_FOLLOWING]);
							} else {
								$this->response->status = "500";
								return json_encode(["error" => "An error occurred."]);
							}
						} else {
							$this->response->status = "404";
							return json_encode(["error" => "The requested resource could not be found."]);
						}
					} else {
						$this->response->status = "500";
						return json_encode(["error" => "An error occurred."]);
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
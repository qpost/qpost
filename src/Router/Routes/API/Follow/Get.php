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
use function qpost\Router\API\api_prepare_object;
use function qpost\Router\API\api_request_data;

api_create_route(Method::GET, "/follow", function () {
	if (api_auth_check($this)) {
		$requestData = api_request_data($this);

		if (isset($requestData["from"])) {
			if (is_numeric($requestData["from"])) {
				if (isset($requestData["to"])) {
					if (is_numeric($requestData["to"])) {
						$from = User::getUser($requestData["from"]);

						if (!is_null($from)) {
							$to = User::getUser($requestData["to"]);

							if (!is_null($to)) {
								$entityManager = EntityManager::instance();
								$follower = $entityManager->getRepository(Follower::class)->findOneBy([
									"from" => $from,
									"to" => $to
								]);

								if (!is_null($follower)) {
									return json_encode(api_prepare_object($follower));
								} else {
									if ($to->getPrivacyLevel() === PrivacyLevel::PRIVATE && FollowRequest::hasSentFollowRequest($from, $to)) {
										return json_encode(["status" => FollowStatus::PENDING]);
									}

									$this->response->status = "404";
									return json_encode(["error" => "The requested resource could not be found."]);
								}
							} else {
								$this->response->status = "404";
								return json_encode(["error" => "The requested user could not be found."]);
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
				$this->response->status = "400";
				return json_encode(["error" => "'from' has to be an integer."]);
			}
		} else {
			$this->response->status = "400";
			return json_encode(["error" => "'from' is required."]);
		}
	}
});
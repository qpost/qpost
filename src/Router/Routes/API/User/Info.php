<?php

namespace qpost\Router\API\User;

use qpost\Account\Token;
use qpost\Account\User;
use qpost\Util\Util;
use function qpost\Router\API\api_method_check;
use function qpost\Router\API\api_request_data;
use function qpost\Router\create_route;

create_route("/api/user/info", function () {
	if(api_method_check($this,"GET")){
		$header = Util::getAuthorizationHeader();
		$requestData = api_request_data($this);

		if(!is_null($header) && !Util::isEmpty($header) && Util::startsWith($header,"Token ")){
			$token = Token::getTokenById(substr($header,strlen("Token ")));

			if(!is_null($token)){
				if(!$token->isExpired()){
					if(isset($requestData["user"])){
						if(!Util::isEmpty($requestData["user"])){
							$user = User::getUserByUsername($requestData["user"]);
							if(is_null($user) && is_numeric($requestData["user"])) $user = User::getUserById($requestData["user"]);

							if(!is_null($user)){
								$followersYouKnow = [];

								foreach($user->followersYouFollow($token->getUser()) as $u){
									array_push($followersYouKnow,$u->toAPIJson($token->getUser(),false));
								}

								$a = $user->toAPIJson($token->getUser(),false);
								$a["followersYouKnow"] = $followersYouKnow;

								return json_encode($a);
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
					return json_encode(["error" => "Token expired"]);
				}
			} else {
				return json_encode(["error" => "Invalid token"]);
			}
		} else {
			return json_encode(["error" => "Invalid token"]);
		}
	} else {
		return "";
	}
});
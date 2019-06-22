<?php

use qpost\Account\Token;
use qpost\Account\User;
use qpost\Util\Util;

$app->bind("/api/token/verify",function(){
	if(api_method_check($this,"POST")){
		$header = Util::getAuthorizationHeader();
		$requestData = api_request_data($this);

		if(!is_null($header) && !Util::isEmpty($header) && Util::startsWith($header,"Token ")){
			$tokenString = substr($header,strlen("Token "));

			if(isset($requestData["user"])){
				if(!Util::isEmpty($requestData["user"])){
					$userID = $requestData["user"];

					$token = Token::getTokenById($tokenString);

					if(!is_null($token)){
						$user = User::getUserById($userID);

						if(!is_null($user)){
							if($token->getUserId() == $userID){
								if(!$user->isSuspended()){
									if(!$token->isExpired()){
										$token->renew();

										return json_encode(["status" => "Token valid","user" => $user->toAPIJson($token->getUser(),false)]);
									} else {
										return json_encode(["status" => "Token expired"]);
									}
								} else {
									return json_encode(["error" => "User suspended"]);
								}
							} else {
								return json_encode(["error" => "Invalid user"]);
							}
						} else {
							return json_encode(["error" => "Invalid user"]);
						}
					} else {
						return json_encode(["error" => "Invalid token"]);
					}
				} else {
					return json_encode(["error" => "Invalid user"]);
				}
			} else {
				return json_encode(["error" => "Invalid user"]);
			}
		} else {
			return json_encode(["error" => "Invalid token"]);
		}
	} else {
		return "";
	}
});
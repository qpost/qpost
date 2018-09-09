<?php

$app->bind("/api/token/verify",function(){
	$this->response->mime = "json";
	header("Access-Control-Allow-Origin: *");

	if(isset($_GET["token"])){
		if(!Util::isEmpty($_GET["token"])){
			$tokenString = $_GET["token"];

			if(isset($_GET["user"])){
				if(!Util::isEmpty($_GET["user"])){
					$userID = $_GET["user"];

					$token = Token::getTokenById($tokenString);

					if(!is_null($token)){
						$user = User::getUserById($userID);

						if(!is_null($user)){
							if($token->getUserId() == $userID){
								if(!$user->isSuspended()){
									if(!$token->isExpired()){
										$token->renew();

										return json_encode(["status" => "Token valid","user" => $user->toAPIJson(false)]);
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
		return json_encode(["error" => "Invalid token"]);
	}
});
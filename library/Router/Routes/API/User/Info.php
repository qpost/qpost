<?php

$app->bind("/api/user/info",function(){
	$this->response->mime = "json";

	if(isset($_GET["token"])){
		if(!Util::isEmpty($_GET["token"])){
			$token = Token::getTokenById($_GET["token"]);

			if(!is_null($token)){
				if(!$token->isExpired()){
					if(isset($_GET["user"])){
						if(!Util::isEmpty($_GET["user"])){
							$user = User::getUserByUsername($_GET["user"]);
							if(is_null($user) && is_numeric($_GET["user"])) $user = User::getUserById($_GET["user"]);

							if(!is_null($user)){
								$a = $user->toAPIJson(false);
								$a["followersYouKnow"] = $user->followersYouFollow($token->getUser());

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
		return json_encode(["error" => "Invalid token"]);
	}
});
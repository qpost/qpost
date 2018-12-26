<?php

$app->bind("/api/user/info",function(){
	if(api_method_check($this,"POST")){
		api_headers($this);

		if(isset($_POST["token"])){
			if(!Util::isEmpty($_POST["token"])){
				$token = Token::getTokenById($_POST["token"]);
	
				if(!is_null($token)){
					if(!$token->isExpired()){
						if(isset($_POST["user"])){
							if(!Util::isEmpty($_POST["user"])){
								$user = User::getUserByUsername($_POST["user"]);
								if(is_null($user) && is_numeric($_POST["user"])) $user = User::getUserById($_POST["user"]);
	
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
			return json_encode(["error" => "Invalid token"]);
		}
	} else {
		return "";
	}
});
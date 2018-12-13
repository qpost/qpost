<?php

$app->bind("/api/token/request",function(){
	$this->response->mime = "json";
	header("Access-Control-Allow-Origin: *");

	if(isset($_GET["email"])){
		if(!Util::isEmpty($_GET["email"])){
			$email = $_GET["email"];

			if(isset($_GET["password"])){
				if(!Util::isEmpty($_GET["password"])){
					$password = $_GET["password"];

					$user = User::getUserByEmail($email);
					if(is_null($user)) $user = User::getUserByUsername($email);

					if(!is_null($user)){
						if($user->isGigadriveLinked()){
							$content = @file_get_contents("https://api.gigadrivegroup.com/v1/login/?apiKey=" . urlencode(GIGADRIVE_API_LEGACY_KEY) . "&username=" . urlencode($email) . "&password=" . urlencode($password));

							if($content && Util::isValidJSON($content)){
								$result = json_decode($content,true);

								if($result){
									if(!isset($result["success"])){
										return json_encode(["error" => isset($result["error"]) ? $result["error"] : "An error occurred"]);
									}
								} else {
									return json_encode(["error" => "An error occurred"]);
								}
							} else {
								return json_encode(["error" => "An error occurred"]);
							}
						} else {
							if(password_verify($password,$user->getPassword())){
								if($user->isSuspended()){
									return json_encode(["error" => "Your account has been suspended."]);
								}
							} else {
								return json_encode(["error" => "Invalid email/password combination"]);	
							}
						}

						// successfully authenticated

						$token = Token::createToken($user,isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : null,Util::getIP());

						if(!is_null($token)){
							return json_encode(["token" => ["id" => $token->getId(),"expiry" => $token->getExpiryTime()], "user" => $user->toAPIJson($token->getUser(),false)]);
						} else {
							return json_encode(["error" => "An error occurred"]);
						}
					} else {
						return json_encode(["error" => "Invalid email/password combination"]);
					}
				} else {
					return json_encode(["error" => "Invalid password"]);
				}
			} else {
				return json_encode(["error" => "Invalid password"]);
			}
		} else {
			return json_encode(["error" => "Invalid email"]);
		}
	} else {
		return json_encode(["error" => "Invalid email"]);
	}
});
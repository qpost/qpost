<?php

$app->bind("/login",function(){
	if(DEVELOPER_MODE !== null && DEVELOPER_MODE == true && isset($_GET["id"])){
		// DEBUG ROUTE FOR FORCING LOGIN

		$user = User::getUserById($_GET["id"]);
	
		if($user->isSuspended() === false){
			$token = Token::createToken($user,$_SERVER["HTTP_USER_AGENT"],Util::getIP());

			if(!is_null($token)){
				Util::setCookie("sesstoken",$token->getId(),180);
			}
		}

		return $this->reroute("/");
	} else {
		if(!Util::isLoggedIn()){
			$errorMsg = null;

			if(isset($_GET["msg"])){
				switch($_GET["msg"]){
					case "suspended":
						$errorMsg = "Your account has been suspended.";
						break;
				}
			}

			if(isset($_POST["email"]) && isset($_POST["password"])){
				$email = trim($_POST["email"]);
				$password = trim($_POST["password"]);

				if(!Util::isEmpty($email) && !Util::isEmpty($password)){
					$mysqli = Database::Instance()->get();

					$stmt = $mysqli->prepare("SELECT `id`,`token`,`email`,`password`,`emailActivated` FROM `users` WHERE `email` = ? OR `username` = ?");
					$stmt->bind_param("ss",$email,$email);
					if($stmt->execute()){
						$result = $stmt->get_result();

						if($result->num_rows){
							$row = $result->fetch_assoc();

							if(is_null($row["token"])){
								if(password_verify($password,$row["password"])){
									if($row["emailActivated"] == true){
										$user = User::getUserById($row["id"]);
	
										if($user->isSuspended() === false){
											$token = Token::createToken($user,$_SERVER["HTTP_USER_AGENT"],Util::getIP());

											if(!is_null($token)){
												Util::setCookie("sesstoken",$token->getId(),180);
											} else {
												$errorMsg = "Your session token could not be created.";
											}
										} else {
											$errorMsg = "Your account has been suspended.";
										}
									} else {
										$errorMsg = "Please activate your email address before logging in.";
									}
								} else {
									$errorMsg = "Invalid credentials.";
								}
							} else {
								$errorMsg = "Please log in via Gigadrive.";
							}
						} else {
							$errorMsg = "Invalid credentials.";
						}
					} else {
						$errorMsg = "An error occurred. " . $stmt->error;
					}
					$stmt->close();

					if(is_null($errorMsg))
						return $this->reroute("/");
				} else {
					$errorMsg = "Please fill all the fields.";
				}
			}

			$data = array(
				"title" => "Log in",
				"errorMsg" => $errorMsg
			);
		
			return $this->render("views:Login.php with views:Layout.php",$data);
		} else {
			return $this->reroute("/");
		}
	}
});

$app->bind("/login/gigadrive",function(){
	return $this->reroute("https://gigadrivegroup.com/authorize?app=" . GIGADRIVE_APP_ID . "&scopes=user:info,user:email");
});

$app->bind("/loginCallback",function(){
	if(!Util::isLoggedIn()){
		if(isset($_GET["code"])){
			$url = "https://api.gigadrivegroup.com/v3/gettoken?secret=" . GIGADRIVE_API_SECRET . "&code=" . urlencode($_GET["code"]);
			$j = @json_decode(@file_get_contents($url),true);

			if(isset($j["success"]) && !Util::isEmpty($j["success"]) && isset($j["token"]) && !Util::isEmpty($j["token"])){
				$token = $j["token"];

				$url = "https://api.gigadrivegroup.com/v3/userdata?secret=" . GIGADRIVE_API_SECRET . "&token=" . urlencode($token);
				$j = @json_decode(@file_get_contents($url),true);

				if(isset($j["success"]) && !Util::isEmpty($j["success"]) && isset($j["user"])){
					$userData = $j["user"];

					if(isset($userData["id"]) && isset($userData["username"]) && isset($userData["avatar"]) && isset($userData["email"])){
						$id = $userData["id"];
						$username = $userData["username"];
						$avatar = isset($userData["avatar"]["url"]) ? $userData["avatar"]["url"] : null;
						$email = $userData["email"];
						$registerDate = $userData["joinDate"];

						$user = User::getUserByGigadriveId($id);
						if(!is_null($user)){
							if($user->isSuspended() === false){
								if($user->getEmail() != $email && !Util::isEmailAvailable($email)) return $this->reroute("/?msg=gigadriveLoginEmailNotAvailable");
								if($user->getUsername() != $username && !Util::isUsernameAvailable($username)) return $this->reroute("/?msg=gigadriveLoginUsernameNotAvailable");

								$user = User::registerUser($id,$username,$avatar,$email,$token,$registerDate);
								$user->updateLastGigadriveUpdate();

								$token = Token::createToken($user,$_SERVER["HTTP_USER_AGENT"],Util::getIP());

								if(!is_null($token)){
									Util::setCookie("sesstoken",$token->getId(),180);
									return $this->reroute("/");
								} else {
									return $this->reroute("/?msg=sessionTokenCouldNotBeCreated");
								}
							} else {
								return $this->reroute("/login?msg=suspended");
							}
						} else {
							if(Util::isEmailAvailable($email)){
								if(Util::isUsernameAvailable($username)){
									$user = User::registerUser($id,$username,$avatar,$email,$token,$registerDate);
									$user->updateLastGigadriveUpdate();

									$token = Token::createToken($user,$_SERVER["HTTP_USER_AGENT"],Util::getIP());

									if(!is_null($token)){
										Util::setCookie("sesstoken",$token->getId(),180);
										return $this->reroute("/");
									} else {
										return $this->reroute("/?msg=sessionTokenCouldNotBeCreated");
									}
								} else {
									return $this->reroute("/?msg=gigadriveLoginUsernameNotAvailable");
								}
							} else {
								return $this->reroute("/?msg=gigadriveLoginEmailNotAvailable");
							}
						}
					} else {
						return $this->reroute("/");
					}
				} else {
					return $this->reroute("/");
				}
			} else {
				return $this->reroute("/");
			}
		} else {
			return $this->reroute("/");
		}
	} else {
		return $this->reroute("/");
	}
});
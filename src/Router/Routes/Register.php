<?php

$app->bind("/register",function(){
    if(!Util::isLoggedIn()){
		if(isset($_GET["code"])){
			$token = User::getGigadriveTokenFromCode($_GET["code"]);

			if(!is_null($token)){
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
							$errorMsg = null;
							$successMsg = null;

							if(isset($_POST["username"]) && isset($_POST["email"])){
								$verifyEmail = ($_POST["email"] != $email);
								$email = $_POST["email"];
								$username = $_POST["username"];

								if(!Util::isEmpty($email) && !Util::isEmpty($username)){
									if(strlen($email) >= 3){
										if(filter_var($email,FILTER_VALIDATE_EMAIL)){
											if(strlen($username) >= 3){
												if(strlen($username) <= 16){
													if(ctype_alnum($username)){
														if(Util::isEmailAvailable($email)){
															if(Util::isUsernameAvailable($username)){
																$mysqli = Database::Instance()->get();
		
																$emailActivated = !$verifyEmail;
																$emailToken = Util::getRandomString(7);
							
																$stmt = $mysqli->prepare("INSERT INTO `users` (`displayName`,`username`,`email`,`emailActivated`,`emailActivationToken`,`token`) VALUES(?,?,?,?,?,?,?);");
																$stmt->bind_param("sssss",$displayName,$username,$email,$emailActivated,$emailToken,$token);
																if($stmt->execute()){
																	$id = $stmt->insert_id;
							
																	if($verifyEmail){
																		$mailContent = MailTemplates::readTemplate("verifyEmail",[
																			"qpost: Verify your email address",
																			"Complete your qpost registration!",
																			"Hello, " . $username . "!",
																			"To complete the creation of your qpost account, please click the button below and verify your email address.",
																			"https://qpost.gigadrivegroup.com/account/verify-email?account=" . $id . "&verificationtoken=" . $emailToken,
																			"Verify",
																			"You did not register for qpost?",
																			"Don't worry! Simply ignore this email and the account registered with this email address will be deleted in 2 weeks.",
																			"Contact Info",
																			"Terms of Service",
																			"Privacy Policy",
																			"Disclaimer",
																			"You don't want to receive this type of emails?",
																			"Click here to change your email settings or unsubscribe."
																		]);

                                                                        Util::sendMail($email,"qpost: Verify your email address",$mailContent,"Paste this link into your browser to verify your account on qpost: https://qpost.gigadrivegroup.com/account/verify-email?account=" . $id . "&verificationtoken=" . $emailToken,$username);
                                                                        $user = User::getUserById($id);

                                                                        $successMsg = "Your account has been created. An activation email has been sent to you. Click the link in that email to verify your account. (Check your spam folder!)";
																	} else {
                                                                        $user = User::getUserById($id);
                                                                        $token = Token::createToken($user,$_SERVER["HTTP_USER_AGENT"],Util::getIP());

                                                                        if(!is_null($token)){
                                                                            Util::setCookie("sesstoken",$token->getId(),180);
                                                                            return $this->reroute("/");
                                                                        } else {
                                                                            return $this->reroute("/?msg=sessionTokenCouldNotBeCreated");
                                                                        }
                                                                    }
																} else {
																	$errorMsg = "An error occurred. " . $stmt->error;
																}

																$stmt->close();
															} else {
																$errorMsg = "That username is not available anymore.";
															}
														} else {
															$errorMsg = "That email is not available anymore.";
														}
													} else {
														$errorMsg = "Your username may only consist of letters and numbers.";
													}
												} else {
													$errorMsg = "Your username may not be longer than 16 characters.";
												}
											} else {
												$errorMsg = "Your username must be at least 3 characters long.";
											}
										} else {
											$errorMsg = "Please enter a valid email address.";
										}
									} else {
										$errorMsg = "Please enter a valid email address.";
									}
								} else {
									$errorMsg = "Please fill all the fields.";
								}
							}

							return $this->render("views:Register.php with views:Layout.php",[
								"title" => "Register",
								"id" => $id,
								"username" => $username,
								"avatar" => $avatar,
								"email" => $email,
								"registerDate" => $registerDate,
								"token" => $token,
								"errorMsg" => $errorMsg,
								"successMsg" => $successMsg
							]);
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
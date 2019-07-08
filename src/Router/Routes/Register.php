<?php

namespace qpost\Router;

use DateTime;
use Doctrine\DBAL\Types\Type;
use Gigadrive\MailTemplates\MailTemplates;
use qpost\Account\Token;
use qpost\Account\User;
use qpost\Account\UserGigadriveData;
use qpost\Database\EntityManager;
use qpost\Util\Util;

create_route("/register", function () {
	if(!Util::isLoggedIn()){
		if(isset($_GET["code"])){
			$token = UserGigadriveData::getGigadriveTokenFromCode($_GET["code"]);

			if(!is_null($token)){
				$url = "https://gigadrivegroup.com/api/v3/user?secret=" . GIGADRIVE_API_SECRET . "&token=" . urlencode($token);
				$j = @json_decode(@file_get_contents($url),true);

				if(isset($j["success"]) && !Util::isEmpty($j["success"]) && isset($j["user"])){
					$userData = $j["user"];
					$entityManager = EntityManager::instance();

					if(isset($userData["id"]) && isset($userData["username"]) && isset($userData["avatar"]) && isset($userData["email"])){
						$id = $userData["id"];
						$username = $userData["username"];
						$avatar = isset($userData["avatar"]["url"]) ? $userData["avatar"]["url"] : null;
						$email = $userData["email"];
						$registerDate = $userData["joinDate"];

						/**
						 * @var User $user
						 */
						$user = $entityManager->getRepository(User::class)->createQueryBuilder("u")
							->innerJoin("u.gigadriveData", "g")
							->where("g.accountId = :gigadriveId")
							->setParameter("gigadriveId", $id, Type::INTEGER)
							->getQuery()
							->getOneOrNullResult();

						if(!is_null($user)){
							if($user->isSuspended() === false){
								if ($user->getEmail() != $email && !User::isEmailAvailable($email)) return $this->reroute("/?msg=gigadriveLoginEmailNotAvailable");
								if ($user->getUsername() != $username && !User::isUsernameAvailable($username)) return $this->reroute("/?msg=gigadriveLoginUsernameNotAvailable");

								$gigadriveData = $user->getGigadriveData();
								$gigadriveData->setLastUpdate(new DateTime("now"));
								$entityManager->persist($gigadriveData);

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
														if (User::isEmailAvailable($email)) {
															if (User::isUsernameAvailable($username)) {
																$emailActivated = !$verifyEmail;
																$emailToken = Util::getRandomString(7);

																$displayName = $username;

																$user = new User();

																$user->setUsername($username)
																	->setDisplayName($displayName)
																	->setEmail($email)
																	->setEmailActivated($emailActivated)
																	->setEmailActivationToken($emailToken)
																	->setTime(new DateTime("now"));

																$gigadriveData = new UserGigadriveData();

																$user->setGigadriveData($gigadriveData->setAccountId($id)
																	->setJoinDate(new DateTime($registerDate))
																	->setToken($token)
																	->setLastUpdate(new DateTime("now")));

																$entityManager->persist($gigadriveData);
																$entityManager->persist($user);

																$entityManager->flush();

																if ($verifyEmail) {
																	$mailContent = MailTemplates::readTemplate("verifyEmail", [
																		"qpost: Verify your email address",
																		"Complete your qpost registration!",
																		"Hello, " . Util::sanatizeString($username) . "!",
																		"To complete the creation of your qpost account, please click the button below and verify your email address.",
																		"https://qpost.gigadrivegroup.com/account/verify-email?account=" . $user->getId() . "&verificationtoken=" . $emailToken,
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

																	$sendMail = Util::sendMail($email, "qpost: Verify your email address", $mailContent, "Paste this link into your browser to verify your account on qpost: https://qpost.gigadrivegroup.com/account/verify-email?account=" . $user->getId() . "&verificationtoken=" . $emailToken, $username);

																	if ($sendMail) {
																		$successMsg = "Your account has been created. An activation email has been sent to you. Click the link in that email to verify your account. (Check your spam folder!)";
																	} else {
																		$errorMsg = "Failed to send your activation email.";
																	}
																} else {
																	$token = Token::createToken($user, $_SERVER["HTTP_USER_AGENT"], Util::getIP());

																	if (!is_null($token)) {
																		Util::setCookie("sesstoken", $token->getId(), 180);
																		return $this->reroute("/");
																	} else {
																		return $this->reroute("/?msg=sessionTokenCouldNotBeCreated");
																	}
																}
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

							return twig_render("pages/register.html.twig", [
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
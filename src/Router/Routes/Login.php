<?php
/**
 * Copyright (C) 2018-2019 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpo.st
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://gnu.org/licenses/>
 */

namespace qpost\Router;

use Doctrine\DBAL\Types\Type;
use qpost\Account\Token;
use qpost\Account\User;
use qpost\Account\UserGigadriveData;
use qpost\Database\EntityManager;
use qpost\Util\Util;

create_route("/login", function () {
	if (DEBUG == true && isset($_GET["id"])) {
		// DEBUG ROUTE FOR FORCING LOGIN

		/**
		 * @var User $user
		 */
		$user = EntityManager::instance()->getRepository(User::class)->findOneBy([
			"id" => $_GET["id"]
		]);

		if (!is_null($user)) {
			if ($user->isSuspended() === false) {
				$token = Token::createToken($user, $_SERVER["HTTP_USER_AGENT"], Util::getIP());

				if (!is_null($token)) {
					Util::setCookie("sesstoken", $token->getId(), 180);
				}
			}
		}

		return $this->reroute("/");
	} else {
		if (!Util::isLoggedIn()) {
			$errorMsg = null;

			if (isset($_GET["msg"])) {
				switch ($_GET["msg"]) {
					case "suspended":
						$errorMsg = "Your account has been suspended.";
						break;
				}
			}

			if (isset($_POST["email"]) && isset($_POST["password"])) {
				$email = trim($_POST["email"]);
				$password = trim($_POST["password"]);

				if (!Util::isEmpty($email) && !Util::isEmpty($password)) {
					$entityManager = EntityManager::instance();

					/**
					 * @var User $user
					 */
					$user = $entityManager->getRepository(User::class)->createQueryBuilder("u")
						->where("upper(u.username) = upper(:query)")
						->setParameter("query", $email, Type::STRING)
						->orWhere("upper(u.email) = upper(:query)")
						->setParameter("query", $email, Type::STRING)
						->setMaxResults(1)
						->getQuery()
						->getOneOrNullResult();

					if (!is_null($user)) {
						if (is_null($user->getGigadriveData())) {
							if (password_verify($password, $user->getPassword())) {
								if ($user->isEmailActivated()) {
									if (!$user->isSuspended()) {
										$token = Token::createToken($user, $_SERVER["HTTP_USER_AGENT"], Util::getIP());

										if (!is_null($token)) {
											Util::setCookie("sesstoken", $token->getId(), 180);
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

					if (is_null($errorMsg))
						return $this->reroute("/");
				} else {
					$errorMsg = "Please fill all the fields.";
				}
			}

			return twig_render("pages/login.html.twig", [
				"title" => "Log in",
				"errorMsg" => $errorMsg
			]);
		} else {
			return $this->reroute("/");
		}
	}
});

create_route("/login/gigadrive", function () {
	return $this->reroute("https://gigadrivegroup.com/authorize?app=" . GIGADRIVE_APP_ID . "&scopes=user:info,user:email");
});

create_route("/loginCallback", function () {
	if (!Util::isLoggedIn()) {
		if (isset($_GET["code"])) {
			$token = UserGigadriveData::getGigadriveTokenFromCode($_GET["code"]);

			if (!is_null($token)) {
				$url = "https://gigadrivegroup.com/api/v3/user?secret=" . GIGADRIVE_API_SECRET . "&token=" . urlencode($token);
				$j = @json_decode(@file_get_contents($url), true);

				if (isset($j["success"]) && !Util::isEmpty($j["success"]) && isset($j["user"])) {
					$userData = $j["user"];

					if (isset($userData["id"]) && isset($userData["username"]) && isset($userData["avatar"]) && isset($userData["email"])) {
						$username = $userData["username"];
						$email = $userData["email"];

						$entityManager = EntityManager::instance();

						/**
						 * @var User $user
						 */
						$user = $entityManager->getRepository(User::class)->createQueryBuilder("u")
							->innerJoin("u.gigadriveData", "g")
							->where("g.accountId = :gigadriveId")
							->setParameter("gigadriveId", $userData["id"], Type::INTEGER)
							->getQuery()
							->getOneOrNullResult();

						if (!is_null($user)) {
							if ($user->isSuspended() === false) {
								if ($user->getEmail() != $email && !User::isEmailAvailable($email)) return $this->reroute("/?msg=gigadriveLoginEmailNotAvailable");
								if ($user->getUsername() != $username && !User::isUsernameAvailable($username)) return $this->reroute("/?msg=gigadriveLoginUsernameNotAvailable");

								$token = Token::createToken($user, $_SERVER["HTTP_USER_AGENT"], Util::getIP());

								if (!is_null($token)) {
									Util::setCookie("sesstoken", $token->getId(), 180);
									return $this->reroute("/");
								} else {
									return $this->reroute("/?msg=sessionTokenCouldNotBeCreated");
								}
							} else {
								return $this->reroute("/login?msg=suspended");
							}
						} else {
							return $this->reroute("/register?code=" . $_GET["code"]);
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
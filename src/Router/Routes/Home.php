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

use DateTime;
use Gigadrive\MailTemplates\MailTemplates;
use qpost\Account\User;
use qpost\Database\EntityManager;
use qpost\Util\Util;

create_route("/", function () {
	if(!Util::isLoggedIn()){
		$errorMsg = null;
		$successMsg = null;

		if (isset($_POST["email"]) && isset($_POST["displayName"]) && isset($_POST["username"]) && isset($_POST["password"])) {
			$email = trim(Util::fixString($_POST["email"]));
			$displayName = trim(Util::fixString($_POST["displayName"]));
			$username = trim(Util::fixString($_POST["username"]));
			$password = trim($_POST["password"]);

			if (!Util::isEmpty($email) && !Util::isEmpty($displayName) && !Util::isEmpty($username) && !Util::isEmpty($password)) {
				if (strlen($email) >= 3) {
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						if (strlen($displayName) >= 1 && strlen($displayName) <= 25) {
							if (strlen($username) >= 3) {
								if (strlen($username) <= 16) {
									if (ctype_alnum($username)) {
										if (!Util::contains($displayName, "☑️") && !Util::contains($displayName, "✔️") && !Util::contains($displayName, "✅") && !Util::contains($displayName, "🗹") && !Util::contains($displayName, "🗸")) {
											if (User::isEmailAvailable($email)) {
												if (User::isUsernameAvailable($username)) {
													$displayName = Util::sanatizeString($displayName);

													$entityManager = EntityManager::instance();

													$emailToken = Util::getRandomString(7);

													$password = password_hash($password, PASSWORD_BCRYPT);

													$user = new User();
													$user->setUsername($username)
														->setDisplayName($displayName)
														->setEmail($email)
														->setPassword($password)
														->setEmailActivated(false)
														->setEmailActivationToken($emailToken)
														->setTime(new DateTime("now"));

													$entityManager->persist($user);
													$entityManager->flush();

													$mailContent = MailTemplates::readTemplate("verifyEmail", [
														"qpost: Verify your email address",
														"Complete your qpost registration!",
														"Hello, " . $displayName . "!",
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

													Util::sendMail($email, "qpost: Verify your email address", $mailContent, "Paste this link into your browser to verify your account on qpost: https://qpost.gigadrivegroup.com/account/verify-email?account=" . $user->getId() . "&verificationtoken=" . $emailToken, $displayName);

													$successMsg = "Your account has been created. An activation email has been sent to you. Click the link in that email to verify your account. (Check your spam folder!)";
												} else {
													$errorMsg = "That username is not available anymore.";
												}
											} else {
												$errorMsg = "That email is not available anymore.";
											}
										} else {
											$errorMsg = "Invalid display name.";
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
							$errorMsg = "Your name must be between 1 and 25 characters long.";
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

		return twig_render("pages/home/index.html.twig", [
			"forceDisableNightMode" => true,
			"errorMsg" => $errorMsg,
			"successMsg" => $successMsg
		]);
	} else {
		return react();
	}
});
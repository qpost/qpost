<?php
/**
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
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

namespace qpost\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use qpost\Constants\FlashMessageType;
use qpost\Entity\Follower;
use qpost\Entity\User;
use qpost\Repository\UserRepository;
use qpost\Util\Util;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function filter_var;
use function password_hash;
use function strlen;
use function trim;
use const FILTER_VALIDATE_EMAIL;
use const PASSWORD_BCRYPT;

class PostRequestService {
	private $entityManager;
	private $mailer;

	public function __construct(EntityManagerInterface $entityManager, Swift_Mailer $mailer) {
		$this->entityManager = $entityManager;
		$this->mailer = $mailer;
	}

	public function handleRegistration($_this, Request $request): void {
		if ($request->isMethod("POST")) {
			$parameters = $request->request;

			if ($parameters->has("_csrf_token") && $_this->csrf("csrf", $parameters->get("_csrf_token"))) {
				if ($parameters->has("email") && $parameters->has("displayName") && $parameters->has("username") && $parameters->has("password")) {
					$email = trim(Util::fixString($parameters->get("email")));
					$displayName = trim(Util::fixString($parameters->get("displayName")));
					$username = trim(Util::fixString($parameters->get("username")));
					$password = trim($parameters->get("password"));

					if (!Util::isEmpty($email) && !Util::isEmpty($displayName) && !Util::isEmpty($username) && !Util::isEmpty($password)) {
						if (strlen($email) >= 3 && filter_var($email, FILTER_VALIDATE_EMAIL)) {
							if (strlen($displayName) >= 1 && strlen($displayName) <= 25) {
								if (strlen($username) >= 3 && strlen($username) <= 16) {
									if (ctype_alnum($username)) {
										if (!Util::contains($displayName, "☑️") && !Util::contains($displayName, "✔️") && !Util::contains($displayName, "✅") && !Util::contains($displayName, "🗹") && !Util::contains($displayName, "🗸")) {
											/**
											 * @var UserRepository $userRepository
											 */
											$userRepository = $this->entityManager->getRepository(User::class);

											if ($userRepository->isEmailAvailable($email)) {
												if ($userRepository->isUsernameAvailable($username)) {
													$ip = $request->getClientIp();

													if ($userRepository->getRecentCreatedAccounts($ip) < 5) {
														$displayName = Util::sanatizeString($displayName);
														$emailToken = Util::getRandomString(7);
														$password = password_hash($password, PASSWORD_BCRYPT);

														// Create user
														$user = new User();
														$user->setUsername($username)
															->setDisplayName($displayName)
															->setEmail($email)
															->setPassword($password)
															->setEmailActivated(false)
															->setEmailActivationToken($emailToken)
															->setCreationIP($ip)
															->setTime(new DateTime("now"));

														$this->entityManager->persist($user);

														$autoFollowAccountId = $_ENV["AUTOFOLLOW_ACCOUNT_ID"];
														if ($autoFollowAccountId) {
															$autoFollowAccount = $userRepository->findOneBy(["id" => $autoFollowAccountId]);

															if ($autoFollowAccount) {
																$this->entityManager->persist((new Follower())
																	->setSender($user)
																	->setReceiver($autoFollowAccount)
																	->setTime(new DateTime("now")));
															}
														}

														$this->entityManager->flush();

														// Send email
														$message = (new Swift_Message("Finish your qpost registration"))
															->setFrom($_ENV["MAILER_FROM"])
															->setTo($email)
															->setBody(
																$_this->view("emails/register.html.twig", [
																	"username" => $username,
																	"displayName" => $displayName,
																	"verificationLink" => $_this->url("qpost_verifyemail_verifyemail", ["userId" => $user->getId(), "activationToken" => $emailToken], UrlGeneratorInterface::ABSOLUTE_URL)
																]),
																"text/html"
															);

														if ($this->mailer->send($message) !== 0) {
															$_this->flash(FlashMessageType::SUCCESS, "Your account has been created. An activation email has been sent to you. Click the link in that email to verify your account. (Check your spam folder!)");
														} else {
															$_this->flash(FlashMessageType::ERROR, "Your email address could not be verified.");
														}
													} else {
														$_this->flash(FlashMessageType::ERROR, "You have created too many accounts in a short period of time.");
													}
												} else {
													$_this->flash(FlashMessageType::ERROR, "That username is not available anymore.");
												}
											} else {
												$_this->flash(FlashMessageType::ERROR, "That email address is not available anymore.");
											}
										} else {
											$_this->flash(FlashMessageType::ERROR, "Your name contains invalid characters.");
										}
									} else {
										$_this->flash(FlashMessageType::ERROR, "Your username may only consist of letters and numbers.");
									}
								} else {
									$_this->flash(FlashMessageType::ERROR, "Your username must be between 3 and 16 characters long.");
								}
							} else {
								$_this->flash(FlashMessageType::ERROR, "Your name must be between 1 and 25 characters long.");
							}
						} else {
							$_this->flash(FlashMessageType::ERROR, "Please enter a valid email address.");
						}
					} else {
						$_this->flash(FlashMessageType::ERROR, "Please fill all the fields.");
					}
				}
			}
		}
	}
}
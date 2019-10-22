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

namespace qpost\Controller;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use qpost\Constants\FlashMessageType;
use qpost\Entity\User;
use qpost\Repository\UserRepository;
use qpost\Service\AuthorizationService;
use qpost\Twig\Twig;
use qpost\Util\Util;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function ctype_alnum;
use function filter_var;
use function password_hash;
use function strlen;
use function trim;
use const FILTER_VALIDATE_EMAIL;
use const PASSWORD_BCRYPT;

class HomeController extends AbstractController {
	/**
	 * @Route("/")
	 *
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @param Swift_Mailer $mailer
	 * @return Response
	 * @throws Exception
	 */
	public function index(Request $request, EntityManagerInterface $entityManager, Swift_Mailer $mailer) {
		$authService = new AuthorizationService($request, $entityManager);

		if ($authService->isAuthorized()) {
			return $this->render("react.html.twig", Twig::param());
		} else {
			if ($request->isMethod("POST")) {
				$parameters = $request->request;

				if ($parameters->has("_csrf_token") && $this->isCsrfTokenValid("csrf", $parameters->get("_csrf_token"))) {
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
												$userRepository = $entityManager->getRepository(User::class);

												if ($userRepository->isEmailAvailable($email)) {
													if ($userRepository->isUsernameAvailable($username)) {
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
															->setTime(new DateTime("now"));

														$entityManager->persist($user);
														$entityManager->flush();

														// Send email
														$message = (new Swift_Message("subject"))
															->setFrom($_ENV["MAILER_FROM"])
															->setTo($email)
															->setBody(
																$this->renderView("emails/register.html.twig", [
																	"username" => $username,
																	"displayName" => $displayName,
																	"verificationLink" => $this->generateUrl("qpost_verifyemail_verifyemail", ["userId" => $user->getId(), "activationToken" => $emailToken], UrlGeneratorInterface::ABSOLUTE_URL)
																]),
																"text/html"
															);

														if ($mailer->send($message) !== 0) {
															$this->addFlash(FlashMessageType::SUCCESS, "Your account has been created. An activation email has been sent to you. Click the link in that email to verify your account. (Check your spam folder!)");
														} else {
															$this->addFlash(FlashMessageType::ERROR, "Your email address could not be verified.");
														}
													} else {
														$this->addFlash(FlashMessageType::ERROR, "That username is not available anymore.");
													}
												} else {
													$this->addFlash(FlashMessageType::ERROR, "That email address is not available anymore.");
												}
											} else {
												$this->addFlash(FlashMessageType::ERROR, "Your name contains invalid characters.");
											}
										} else {
											$this->addFlash(FlashMessageType::ERROR, "Your username may only consist of letters and numbers.");
										}
									} else {
										$this->addFlash(FlashMessageType::ERROR, "Your username must be between 3 and 16 characters long.");
									}
								} else {
									$this->addFlash(FlashMessageType::ERROR, "Your name must be between 1 and 25 characters long.");
								}
							} else {
								$this->addFlash(FlashMessageType::ERROR, "Please enter a valid email address.");
							}
						} else {
							$this->addFlash(FlashMessageType::ERROR, "Please fill all the fields.");
						}
					}
				}
			}

			return $this->render("pages/home/index.html.twig", Twig::param());
		}
	}
}

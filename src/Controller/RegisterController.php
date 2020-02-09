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

namespace qpost\Controller;

use DateInterval;
use DateTime;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use qpost\Constants\FlashMessageType;
use qpost\Constants\MiscConstants;
use qpost\Entity\Follower;
use qpost\Entity\Token;
use qpost\Entity\User;
use qpost\Entity\UserGigadriveData;
use qpost\Repository\UserGigadriveDataRepository;
use qpost\Repository\UserRepository;
use qpost\Service\AuthorizationService;
use qpost\Service\IpStackService;
use qpost\Twig\Twig;
use qpost\Util\Util;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function filter_var;
use function strlen;

class RegisterController extends AbstractController {
	/**
	 * @Route("/register")
	 *
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @param Swift_Mailer $mailer
	 * @param IpStackService $ipStackService
	 * @return RedirectResponse|Response
	 * @throws NonUniqueResultException
	 */
	public function index(Request $request, EntityManagerInterface $entityManager, Swift_Mailer $mailer, IpStackService $ipStackService) {
		$user = $this->getUser();
		if (!$user) {
			$query = $request->query;

			if ($query->has("code")) {
				$code = $query->get("code");

				/**
				 * @var UserGigadriveDataRepository $gigadriveRepository
				 */
				$gigadriveRepository = $entityManager->getRepository(UserGigadriveData::class);

				$token = $gigadriveRepository->getGigadriveTokenFromCode($code);
				if (!is_null($token)) {
					$userData = $gigadriveRepository->getGigadriveUserData($token);

					if (!is_null($userData)) {
						if (isset($userData["id"]) && isset($userData["username"]) && isset($userData["avatar"]) && isset($userData["email"])) {
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
								->useQueryCache(true)
								->getOneOrNullResult();

							if (is_null($user)) {
								if ($request->isMethod("POST")) {
									$parameters = $request->request;

									if ($parameters->has("_csrf_token") && $this->isCsrfTokenValid("csrf", $parameters->get("_csrf_token"))) {
										if ($parameters->has("username") && $parameters->has("email")) {
											$verifyEmail = (bool)($parameters->get("email") != $email);
											$email = $parameters->get("email");
											$username = $parameters->get("username");

											if (!Util::isEmpty($email) && !Util::isEmpty($username)) {
												if (strlen($email) >= 3) {
													if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
														if (strlen($username) >= 3) {
															if (strlen($username) <= 16) {
																if (ctype_alnum($username)) {
																	/**
																	 * @var UserRepository $userRepository
																	 */
																	$userRepository = $entityManager->getRepository(User::class);

																	$ip = $request->getClientIp();

																	if ($userRepository->getRecentCreatedAccounts($ip) < 5) {
																		if ($userRepository->isEmailAvailable($email)) {
																			if ($userRepository->isUsernameAvailable($username)) {
																				$emailActivated = !$verifyEmail;
																				$emailToken = Util::getRandomString(7);

																				$user = (new User())
																					->setUsername($username)
																					->setDisplayName($username)
																					->setEmail($email)
																					->setEmailActivated($emailActivated)
																					->setEmailActivationToken($emailToken)
																					->setTime(new DateTime("now"))
																					->setCreationIP($request->getClientIp())
																					->setGigadriveData((new UserGigadriveData())
																						->setAccountId($id)
																						->setJoinDate(new DateTime($registerDate))
																						->setToken($token)
																						->setLastUpdate(new DateTime("now")));

																				$entityManager->persist($user);

																				$autoFollowAccountId = $_ENV["AUTOFOLLOW_ACCOUNT_ID"];
																				if ($autoFollowAccountId) {
																					$autoFollowAccount = $userRepository->findOneBy(["id" => $autoFollowAccountId]);

																					if ($autoFollowAccount) {
																						$entityManager->persist((new Follower())
																							->setSender($user)
																							->setReceiver($autoFollowAccount)
																							->setTime(new DateTime("now")));
																					}
																				}

																				$entityManager->flush();

																				if ($verifyEmail) {
																					// Send email
																					$message = (new Swift_Message("Finish your qpost registration"))
																						->setFrom($_ENV["MAILER_FROM"])
																						->setTo($email)
																						->setBody(
																							$this->renderView("emails/register.html.twig", [
																								"username" => $username,
																								"displayName" => $username,
																								"verificationLink" => $this->generateUrl("qpost_verifyemail_verifyemail", ["userId" => $user->getId(), "activationToken" => $emailToken], UrlGeneratorInterface::ABSOLUTE_URL)
																							]),
																							"text/html"
																						);

																					if ($mailer->send($message) !== 0) {
																						$this->addFlash(FlashMessageType::SUCCESS, "Your account has been created. An activation email has been sent to you. Click the link in that email to verify your account. (Check your spam folder!)");
																					} else {
																						$this->addFlash(FlashMessageType::ERROR, "Your email address could not be verified.");
																					}

																					return $this->redirect($this->generateUrl("qpost_login_index"));
																				} else {
																					$expiry = new DateTime("now");
																					$expiry->add(DateInterval::createFromDateString("6 month"));

																					$token = (new Token())
																						->setUser($user)
																						->setTime(new DateTime("now"))
																						->setLastAccessTime(new DateTime("now"))
																						->setUserAgent($request->headers->get("User-Agent"))
																						->setLastIP($request->getClientIp())
																						->setExpiry($expiry);

																					$entityManager->persist($token);

																					$ipStackResult = $ipStackService->createIpStackResult($token);
																					if ($ipStackResult) {
																						$entityManager->persist($ipStackResult);

																						$token->setIpStackResult($ipStackResult);

																						$entityManager->persist($token);
																					}

																					$entityManager->flush();

																					$response = $this->redirect($this->generateUrl("qpost_home_index"));
																					$response->headers->setCookie(Cookie::create("sesstoken", $token->getId(), $expiry->getTimestamp(), "/", null, null, false));

																					return $response;
																				}
																			} else {
																				$this->addFlash(FlashMessageType::ERROR, "That username is not available anymore.");
																			}
																		} else {
																			$this->addFlash(FlashMessageType::ERROR, "That email address is not available anymore.");
																		}
																	} else {
																		$this->addFlash(FlashMessageType::ERROR, "You have created too many accounts in a short period of time.");
																	}
																} else {
																	$this->addFlash(FlashMessageType::ERROR, "Your username may only consist of letters and numbers.");
																}
															} else {
																$this->addFlash(FlashMessageType::ERROR, "Your username may not be longer than 16 characters.");
															}
														} else {
															$this->addFlash(FlashMessageType::ERROR, "Your username must be at least 3 characters long.");
														}
													} else {
														$this->addFlash(FlashMessageType::ERROR, "Please enter a valid email address.");
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

								return $this->render("pages/register.html.twig", Twig::param([
									"id" => $id,
									"username" => $username,
									"avatar" => $avatar,
									"email" => $email,
									"registerDate" => $registerDate,
									"token" => $token,
									"code" => $code,
									MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_register_index", [], UrlGeneratorInterface::ABSOLUTE_URL)
								]));
							} else {
								$this->addFlash(FlashMessageType::ERROR, "You are already registered.");
							}
						}
					}
				}
			}

			return $this->redirect($this->generateUrl("qpost_login_index"));
		}

		return $this->redirect($this->generateUrl("qpost_home_index"));
	}
}

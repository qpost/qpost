<?php
/*
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpostapp.com
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
use Gigadrive\Bundle\SymfonyExtensionsBundle\DependencyInjection\Util;
use Psr\Log\LoggerInterface;
use qpost\Constants\FlashMessageType;
use qpost\Entity\Token;
use qpost\Entity\User;
use qpost\Entity\UserGigadriveData;
use qpost\Repository\UserGigadriveDataRepository;
use qpost\Service\AuthorizationService;
use qpost\Service\IpStackService;
use qpost\Service\TokenService;
use qpost\Twig\Twig;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function is_null;
use function password_verify;
use function trim;

class LoginController extends qpostController {
	/**
	 * @Route("/login")
	 *
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @param IpStackService $ipStackService
	 * @param TokenService $tokenService
	 * @return RedirectResponse|Response
	 * @throws NonUniqueResultException
	 */
	public function index(Request $request, EntityManagerInterface $entityManager, IpStackService $ipStackService, TokenService $tokenService) {
		$user = $this->getUser();
		$loggedIn = !!$user;
		if ($loggedIn && ($this->query("addToken") != "true" && !$request->isMethod("POST"))) return $this->redirectToRoute("qpost_home_index");

		if ($request->isMethod("POST")) {
			$parameters = $request->request;

			if ($parameters->has("_csrf_token") && $this->isCsrfTokenValid("csrf", $parameters->get("_csrf_token"))) {
				if ($parameters->has("email") && $parameters->has("password")) {
					$email = trim($parameters->get("email"));
					$password = trim($parameters->get("password"));

					if (!Util::isEmpty($email) && !Util::isEmpty($password)) {
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
							->useQueryCache(true)
							->getOneOrNullResult();

						if (!is_null($user) && is_null($user->getGigadriveData())) {
							$passwordHash = $user->getPassword();

							if (!is_null($passwordHash) && password_verify($password, $passwordHash)) {
								if ($user->isEmailActivated()) {
									if (!$user->isSuspended()) {
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
										$tokenService->addToken($token->getId(), $request, $response);

										return $response;
									} else {
										$this->addFlash(FlashMessageType::ERROR, "Your account has been suspended.");
									}
								} else {
									$this->addFlash(FlashMessageType::ERROR, "Please activate your email address before logging in.");
								}
							} else {
								$this->addFlash(FlashMessageType::ERROR, "Invalid credentials.");
							}
						} else {
							$this->addFlash(FlashMessageType::ERROR, "Invalid credentials.");
						}
					} else {
						$this->addFlash(FlashMessageType::ERROR, "Please fill all the fields.");
					}
				}
			}
		}

		$isMobile = $request->headers->has("Q-User-Agent") && $request->headers->get("Q-User-Agent") === "android";

		return $this->render($isMobile === false ? "pages/login.html.twig" : "pages/mobile/login.html.twig", Twig::param());
	}

	/**
	 * @Route("/login/callback")
	 *
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @param IpStackService $ipStackService
	 * @param LoggerInterface $logger
	 * @param TokenService $tokenService
	 * @return RedirectResponse
	 * @throws NonUniqueResultException
	 */
	public function callback(Request $request, EntityManagerInterface $entityManager, IpStackService $ipStackService, LoggerInterface $logger, TokenService $tokenService) {
		$user = $this->getUser();
		$loggedIn = !!$user;
		$query = $request->query;

		if ($query->has("code")) {
			/**
			 * @var UserGigadriveDataRepository $gigadriveRepository
			 */
			$gigadriveRepository = $entityManager->getRepository(UserGigadriveData::class);

			$code = $query->get("code");
			$token = $gigadriveRepository->getGigadriveTokenFromCode($code);
			if (!is_null($token)) {
				$userData = $gigadriveRepository->getGigadriveUserData($token);

				if (!is_null($userData)) {
					if (isset($userData["id"]) && isset($userData["username"]) && isset($userData["avatar"]) && isset($userData["email"])) {
						$username = $userData["username"];
						$email = $userData["email"];

						/**
						 * @var User $user
						 */
						$user = $entityManager->getRepository(User::class)->createQueryBuilder("u")
							->innerJoin("u.gigadriveData", "g")
							->where("g.accountId = :gigadriveId")
							->setParameter("gigadriveId", $userData["id"], Type::INTEGER)
							->getQuery()
							->useQueryCache(true)
							->getOneOrNullResult();

						if (!is_null($user)) {
							if (!$user->isSuspended()) {
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

								$logger->info("Logged in");

								$response = $this->redirect($this->generateUrl("qpost_home_index"));
								$tokenService->addToken($token->getId(), $request, $response);

								return $response;
							} else {
								$this->addFlash(FlashMessageType::ERROR, "Your account has been suspended.");
							}
						} else {
							return $this->redirect($this->generateUrl("qpost_register_index", ["code" => $code]));
						}
					} else {
						$this->addFlash(FlashMessageType::ERROR, "Authentication failed.");
					}
				} else {
					$this->addFlash(FlashMessageType::ERROR, "Authentication failed.");
				}
			} else {
				$this->addFlash(FlashMessageType::ERROR, "Authentication failed.");
			}
		}

		return $this->redirect($this->generateUrl("qpost_login_index"));
	}

	/**
	 * @Route("/login/gigadrive")
	 */
	public function gigadrive() {
		return $this->redirect("https://gigadrivegroup.com/authorize?app=" . $_ENV["GIGADRIVE_APP_ID"] . "&scopes=user:info,user:email");
	}
}

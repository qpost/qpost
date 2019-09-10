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

use DateInterval;
use DateTime;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use qpost\Constants\FlashMessageType;
use qpost\Entity\Token;
use qpost\Entity\User;
use qpost\Service\AuthorizationService;
use qpost\Twig\Twig;
use qpost\Util\Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function password_verify;
use function trim;

class LoginController extends AbstractController {
	/**
	 * @Route("/login")
	 *
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @return RedirectResponse|Response
	 * @throws NonUniqueResultException
	 */
	public function index(Request $request, EntityManagerInterface $entityManager) {
		$authService = new AuthorizationService($request, $entityManager);
		if (!$authService->isAuthorized()) {
			if ($request->isMethod("POST")) {
				$parameters = $request->request;

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
							->getOneOrNullResult();

						if (!is_null($user) && is_null($user->getGigadriveData())) {
							$passwordHash = $user->getPassword();

							if (!is_null($passwordHash) && password_verify($password, $passwordHash)) {
								if ($user->isEmailActivated()) {
									if ($user->isSuspended()) {
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
										$entityManager->flush();

										$response = $this->redirect($this->generateUrl("qpost_home_index"));
										$response->headers->setCookie(Cookie::create("sesstoken", $token->getId(), $expiry->getTimestamp()));

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

			return $this->render("pages/login.html.twig", Twig::param());
		} else {
			return $this->redirect($this->generateUrl("qpost_home_index"));
		}
	}

	/**
	 * @Route("/login/gigadrive")
	 */
	public function gigadrive() {
		return $this->redirect("https://gigadrivegroup.com/authorize?app=" . $_ENV["GIGADRIVE_APP_ID"] . "&scopes=user:info,user:email");
	}
}

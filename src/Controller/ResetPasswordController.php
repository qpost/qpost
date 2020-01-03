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

use DateTime;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use qpost\Constants\FlashMessageType;
use qpost\Constants\MiscConstants;
use qpost\Entity\ResetPasswordToken;
use qpost\Entity\User;
use qpost\Service\AuthorizationService;
use qpost\Twig\Twig;
use qpost\Util\Util;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function is_null;
use function password_hash;
use const PASSWORD_BCRYPT;

class ResetPasswordController extends AbstractController {
	/**
	 * @Route("/reset-password")
	 *
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @param Swift_Mailer $mailer
	 * @return RedirectResponse|Response
	 * @throws NonUniqueResultException
	 */
	public function index(Request $request, EntityManagerInterface $entityManager, Swift_Mailer $mailer) {
		$user = $this->getUser();

		if (!$user) {
			if ($request->isMethod("POST")) {
				$parameters = $request->request;

				if ($parameters->has("_csrf_token") && $this->isCsrfTokenValid("csrf", $parameters->get("_csrf_token"))) {
					if ($parameters->has("email")) {
						$email = (string)$parameters->get("email");

						if (!Util::isEmpty($email)) {
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
								$token = (new ResetPasswordToken())
									->setUser($user)
									->setTime(new DateTime("now"));

								$entityManager->persist($token);
								$entityManager->flush();

								$mailer->send((new Swift_Message("Reset your qpost password"))
									->setFrom($_ENV["MAILER_FROM"])
									->setTo($user->getEmail())
									->setBody(
										$this->renderView("emails/register.html.twig", [
											"username" => $user->getUsername(),
											"displayName" => $user->getDisplayName(),
											"verificationLink" => $this->generateUrl("qpost_resetpassword_resetpasswordresponse", ["uid" => $user->getId(), "token" => $token->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
										]),
										"text/html"
									)
								);

								$this->addSuccessfulFlash();
							} else {
								// add successful flash regardless of whether the user exists to prevent leaking email addresses
								$this->addSuccessfulFlash();
							}
						} else {
							$this->addFlash(FlashMessageType::ERROR, "Please fill all the fields.");
						}
					}
				}
			}

			return $this->render("pages/resetPassword.html.twig", Twig::param([
				"title" => "Reset your password",
				MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_resetpassword_index", [], UrlGeneratorInterface::ABSOLUTE_URL)
			]));
		} else {
			return $this->redirectToRoute("qpost_home_index");
		}
	}

	/**
	 * @Route("/reset-password-response")
	 *
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @return RedirectResponse|Response
	 */
	public function resetPasswordResponse(Request $request, EntityManagerInterface $entityManager) {
		$user = $this->getUser();

		if (!$user) {
			$query = $request->query;

			if ($query->has("token") && $query->has("uid")) {
				$tokenId = $query->get("token");
				$userId = $query->get("uid");

				$user = $entityManager->getRepository(User::class)->findOneBy(["id" => $userId]);

				$token = $entityManager->getRepository(ResetPasswordToken::class)->findOneBy([
					"user" => $user,
					"id" => $tokenId,
					"active" => true
				]);

				if ($user && $token) {
					$renderForm = true;

					if ($request->isMethod("POST")) {
						$parameters = $request->request;

						if ($parameters->has("_csrf_token") && $this->isCsrfTokenValid("csrf", $parameters->get("_csrf_token"))) {
							if ($parameters->has("password") && $parameters->has("password2")) {
								$password = $parameters->get("password");
								$password2 = $parameters->get("password2");

								if ($password === $password2) {
									$hash = password_hash($password, PASSWORD_BCRYPT);

									$entityManager->persist($user->setPassword($hash));
									$entityManager->persist($token->setActive(false)->setTimeAccessed(new DateTime("now")));
									$entityManager->flush();

									$this->addFlash(FlashMessageType::SUCCESS, "Your password has been changed.");
									$renderForm = false;
								} else {
									$this->addFlash(FlashMessageType::ERROR, "The passwords do not match.");
								}
							}
						}
					}

					return $this->render("pages/resetPasswordResponse.html.twig", Twig::param([
						"title" => "Reset your password",
						"renderForm" => $renderForm,
						"user" => $user,
						"token" => $token,
						MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_resetpassword_resetpasswordresponse", [], UrlGeneratorInterface::ABSOLUTE_URL)
					]));
				} else {
					$this->addFlash(FlashMessageType::ERROR, "Invalid token.");

					return $this->render("pages/resetPasswordResponse.html.twig", Twig::param([
						"title" => "Reset your password",
						"renderForm" => false,
						MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_resetpassword_resetpasswordresponse", [], UrlGeneratorInterface::ABSOLUTE_URL)
					]));
				}
			} else {
				$this->addFlash(FlashMessageType::ERROR, "Invalid token.");

				return $this->render("pages/resetPasswordResponse.html.twig", Twig::param([
					"title" => "Reset your password",
					"renderForm" => false,
					MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_resetpassword_resetpasswordresponse", [], UrlGeneratorInterface::ABSOLUTE_URL)
				]));
			}
		} else {
			return $this->redirectToRoute("qpost_home_index");
		}
	}

	private function addSuccessfulFlash(): void {
		$this->addFlash(FlashMessageType::SUCCESS, "An email has been sent to you, that contains a link to reset your password.");
	}
}
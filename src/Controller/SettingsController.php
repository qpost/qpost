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
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use qpost\Constants\FlashMessageType;
use qpost\Constants\MiscConstants;
use qpost\Constants\PrivacyLevel;
use qpost\Constants\SettingsNavigationPoint;
use qpost\Entity\User;
use qpost\Twig\Twig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function array_merge;
use function is_null;
use function is_string;
use function password_hash;
use function password_verify;
use function strlen;
use function strtoupper;
use const PASSWORD_BCRYPT;

class SettingsController extends AbstractController {
	/**
	 * @Route("/settings/profile/appearance")
	 * @param Request $request
	 * @return Response
	 */
	public function profileAppearance(Request $request) {
		return $this->renderAction("Edit profile", "settings/profile/appearance.html.twig", SettingsNavigationPoint::PROFILE_APPEARANCE, $this->generateUrl(
			"qpost_settings_profileappearance", [], UrlGeneratorInterface::ABSOLUTE_URL
		));
	}

	/**
	 * @Route("/settings/preferences/appearance")
	 * @param Request $request
	 * @return Response
	 */
	public function preferencesAppearance(Request $request) {
		return $this->renderAction("Appearance", "settings/preferences/appearance.html.twig", SettingsNavigationPoint::PREFERENCES_APPEARANCE, $this->generateUrl(
			"qpost_settings_profileappearance", [], UrlGeneratorInterface::ABSOLUTE_URL
		));
	}

	/**
	 * @Route("/settings/relationships/following")
	 * @param Request $request
	 * @return Response
	 */
	public function relationshipsFollowing(Request $request) {
		return $this->renderAction("Following", "settings/relationships/following.html.twig", SettingsNavigationPoint::RELATIONSHIPS_FOLLOWING, $this->generateUrl(
			"qpost_settings_profileappearance", [], UrlGeneratorInterface::ABSOLUTE_URL
		));
	}

	/**
	 * @Route("/settings/relationships/followers")
	 * @param Request $request
	 * @return Response
	 */
	public function relationshipsFollowers(Request $request) {
		return $this->renderAction("Followers", "settings/relationships/followers.html.twig", SettingsNavigationPoint::RELATIONSHIPS_FOLLOWERS, $this->generateUrl(
			"qpost_settings_profileappearance", [], UrlGeneratorInterface::ABSOLUTE_URL
		));
	}

	/**
	 * @Route("/settings/relationships/blocked")
	 * @param Request $request
	 * @return Response
	 */
	public function relationshipsBlocked(Request $request) {
		return $this->renderAction("Blocked accounts", "settings/relationships/blocked.html.twig", SettingsNavigationPoint::RELATIONSHIP_BLOCKED, $this->generateUrl(
			"qpost_settings_profileappearance", [], UrlGeneratorInterface::ABSOLUTE_URL
		));
	}

	/**
	 * @Route("/settings/account/information")
	 * @param Request $request
	 * @return Response
	 */
	public function accountInformation(Request $request) {
		return $this->renderAction("Account information", "settings/account/information.html.twig", SettingsNavigationPoint::ACCOUNT_INFORMATION, $this->generateUrl(
			"qpost_settings_profileappearance", [], UrlGeneratorInterface::ABSOLUTE_URL
		));
	}

	/**
	 * @Route("/settings/account/username")
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @return Response
	 * @throws Exception
	 */
	public function accountUsername(Request $request, EntityManagerInterface $entityManager) {
		if ($this->validate($request)) {
			$parameters = $request->request;

			if ($parameters->has("username")) {
				$username = $parameters->get("username");

				if (is_string($username)) {
					if (ctype_alnum($username)) {
						if (strlen($username) >= 3) {
							if (strlen($username) <= 16) {
								/**
								 * @var User $user
								 */
								$user = $this->getUser();

								$lastChange = $user->getLastUsernameChange();
								$now = new DateTime("now");

								if (is_null($lastChange) || $lastChange->diff($now)->days > 30) {
									if ($username !== $user->getUsername()) {
										// allow users to change username capitalization
										if (strtoupper($username) === strtoupper($user->getUsername()) || $entityManager->getRepository(User::class)->isUsernameAvailable($username)) {
											$user->setUsername($username)
												->setLastUsernameChange($now);

											$entityManager->persist($user);
											$entityManager->flush();

											$this->addFlash(FlashMessageType::SUCCESS, "Your username has been changed.");
										} else {
											$this->addFlash(FlashMessageType::ERROR, "That username is not available anymore.");
										}
									} else {
										$this->addFlash(FlashMessageType::ERROR, "You already have this username.");
									}
								} else {
									$this->addFlash(FlashMessageType::ERROR, "You can only change your username every 30 days.");
								}
							} else {
								$this->addFlash(FlashMessageType::ERROR, "The username cannot be longer than 16 characters.");
							}
						} else {
							$this->addFlash(FlashMessageType::ERROR, "The username has to be at least 3 characters long.");
						}
					} else {
						$this->addFlash(FlashMessageType::ERROR, "The username has to be alphanumeric.");
					}
				} else {
					$this->addFlash(FlashMessageType::ERROR, "Please enter a username.");
				}
			} else {
				$this->addFlash(FlashMessageType::ERROR, "Please enter a username.");
			}
		}

		return $this->renderAction("Change username", "settings/account/username.html.twig", SettingsNavigationPoint::ACCOUNT_USERNAME, $this->generateUrl(
			"qpost_settings_profileappearance", [], UrlGeneratorInterface::ABSOLUTE_URL
		));
	}

	/**
	 * @Route("/settings/account/password")
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @return Response
	 */
	public function accountPassword(Request $request, EntityManagerInterface $entityManager) {
		/**
		 * @var User $user
		 */
		$user = $this->getUser();

		if ($user->getGigadriveData()) {
			$this->addFlash(FlashMessageType::ERROR, "You are not allowed to view that page.");
			return $this->redirectToRoute("qpost_settings_accountinformation");
		}

		if ($this->validate($request)) {
			$parameters = $request->request;

			if ($parameters->has("oldPassword")) {
				$oldPassword = $parameters->get("oldPassword");

				if (is_string($oldPassword)) {
					if ($parameters->has("newPassword")) {
						$newPassword = $parameters->get("newPassword");

						if (is_string($newPassword)) {
							if ($parameters->has("newPassword2")) {
								$newPassword2 = $parameters->get("newPassword2");

								if (is_string($newPassword2)) {
									if ($newPassword === $newPassword2) {
										if (password_verify($oldPassword, $user->getPassword())) {
											$newHash = password_hash($newPassword, PASSWORD_BCRYPT);

											$user->setPassword($newHash);

											$entityManager->persist($user);
											$entityManager->flush();

											$this->addFlash(FlashMessageType::SUCCESS, "Your changes have been saved.");
										} else {
											$this->addFlash(FlashMessageType::ERROR, "Your current password is incorrect.");
										}
									} else {
										$this->addFlash(FlashMessageType::ERROR, "The new passwords don't match.");
									}
								} else {
									$this->addFlash(FlashMessageType::ERROR, "Please fill all the fields.");
								}
							} else {
								$this->addFlash(FlashMessageType::ERROR, "Please fill all the fields.");
							}
						} else {
							$this->addFlash(FlashMessageType::ERROR, "Please fill all the fields.");
						}
					} else {
						$this->addFlash(FlashMessageType::ERROR, "Please fill all the fields.");
					}
				} else {
					$this->addFlash(FlashMessageType::ERROR, "Please fill all the fields.");
				}
			} else {
				$this->addFlash(FlashMessageType::ERROR, "Please fill all the fields.");
			}
		}

		return $this->renderAction("Change password", "settings/account/password.html.twig", SettingsNavigationPoint::ACCOUNT_PASSWORD, $this->generateUrl(
			"qpost_settings_profileappearance", [], UrlGeneratorInterface::ABSOLUTE_URL
		));
	}

	/**
	 * @Route("/settings/account/sessions")
	 * @param Request $request
	 * @return Response
	 */
	public function accountSessions(Request $request) {
		return $this->renderAction("Active sessions", "settings/account/sessions.html.twig", SettingsNavigationPoint::ACCOUNT_ACTIVE_SESSIONS, $this->generateUrl(
			"qpost_settings_profileappearance", [], UrlGeneratorInterface::ABSOLUTE_URL
		));
	}

	/**
	 * @Route("/settings/privacy")
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @return Response
	 */
	public function privacy(Request $request, EntityManagerInterface $entityManager) {
		if ($this->validate($request)) {
			$parameters = $request->request;

			if ($parameters->has("privacyLevel")) {
				$level = $parameters->get("privacyLevel");

				if (PrivacyLevel::isValid($level)) {
					/**
					 * @var User $user
					 */
					$user = $this->getUser();

					$user->setPrivacyLevel($level);

					$entityManager->persist($user);
					$entityManager->flush();

					$this->addFlash(FlashMessageType::SUCCESS, "Your changes have been saved.");
				} else {
					$this->addFlash(FlashMessageType::ERROR, "Please try again.");
				}
			} else {
				$this->addFlash(FlashMessageType::ERROR, "Please fill all the fields.");
			}
		}

		return $this->renderAction("Privacy", "settings/privacy/privacy.html.twig", SettingsNavigationPoint::PRIVACY, $this->generateUrl(
			"qpost_settings_profileappearance", [], UrlGeneratorInterface::ABSOLUTE_URL
		));
	}

	private function validate(Request $request): bool {
		if ($request->isMethod("POST")) {
			$parameters = $request->request;

			return $parameters->has("_csrf_token") && $this->isCsrfTokenValid("csrf", $parameters->get("_csrf_token"));
		}

		return false;
	}

	private function renderAction(string $headline, string $template, ?string $activeMenuPoint, string $canonicalURL, array $additionalParameters = []) {
		return $this->render($template, array_merge(Twig::param([
			"title" => $headline,
			MiscConstants::CANONICAL_URL => $canonicalURL,
			SettingsNavigationPoint::VARIABLE_NAME => $activeMenuPoint
		]), $additionalParameters));
	}
}
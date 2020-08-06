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

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Gigadrive\Bundle\SymfonyExtensionsBundle\DependencyInjection\Util;
use Gigadrive\Bundle\SymfonyExtensionsBundle\Exception\Form\FormException;
use qpost\Constants\FlashMessageType;
use qpost\Constants\PrivacyLevel;
use qpost\Constants\SettingsNavigationPoint;
use qpost\Entity\LinkedAccount;
use qpost\Entity\User;
use qpost\Exception\ProfileImageInvalidException;
use qpost\Exception\ProfileImageTooBigException;
use qpost\Service\DataDeletionService;
use qpost\Service\GigadriveService;
use qpost\Service\NameHistoryService;
use qpost\Service\ProfileImageService;
use qpost\Twig\Twig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function __;
use function array_merge;
use function is_null;
use function is_string;
use function password_hash;
use function password_verify;
use function strlen;
use function strtotime;
use function strtoupper;
use function time;
use const PASSWORD_BCRYPT;

class SettingsController extends qpostController {
	/**
	 * @Route("/settings/profile/appearance")
	 * @param Request $request
	 * @param ProfileImageService $imageService
	 * @param EntityManagerInterface $entityManager
	 * @return Response
	 * @throws Exception
	 */
	public function profileAppearance(Request $request, ProfileImageService $imageService, EntityManagerInterface $entityManager) {
		if ($this->csrf()) {
			$parameters = $request->request;

			/**
			 * @var User $user
			 */
			$user = $this->getUser();

			$save = true;

			// Display name
			$displayName = $parameters->get("displayName");
			if ($displayName !== $user->getDisplayName()) {
				$displayName = trim(Util::fixString($displayName));

				if (Util::isEmpty($displayName) || !(strlen($displayName) >= 1 && strlen($displayName) <= 24)) {
					$save = false;
					$this->addFlash(FlashMessageType::ERROR, __("settings.profile.appearance.error.displayNameExceedsLength"));
				}
			}

			// Bio
			$bio = $parameters->get("bio");
			if ($bio !== $user->getBio()) {
				$bio = trim(Util::fixString($bio));

				if (!Util::isEmpty($bio) && !(strlen($bio) >= 0 && strlen($bio) <= 200)) {
					$save = false;
					$this->addFlash(FlashMessageType::ERROR, __("settings.profile.appearance.error.bioExceedsLength"));
				}
			}

			// Birthday
			$birthday = $parameters->get("birthday");
			if ($birthday === "") {
				$user->setBirthday(null);
			} else if ((!$user->getBirthday() && $birthday !== "") || $user->getBirthday()->format("Y-m-d") !== $birthday) {
				if ($birthdayTime = strtotime($birthday)) {
					if ($birthdayTime >= time() - (13 * 365 * 24 * 60 * 60) || $birthdayTime <= time() - (120 * 365 * 24 * 60 * 60)) {
						$save = false;
						$this->addFlash(FlashMessageType::ERROR, __("settings.profile.appearance.error.birthdayExceedsLength"));
					}
				} else {
					$save = false;
					$this->addFlash(FlashMessageType::ERROR, __("settings.profile.appearance.error.invalidBirthday"));
				}
			}

			// Header
			$header = $parameters->get("header");
			if ($save && $header !== $user->getHeader() && $header !== "") { // new header has been uploaded
				try {
					$header = $imageService->upload($header, 5, 1500, 500);

					if (is_null($header)) {
						$save = false;
						$this->addFlash(FlashMessageType::ERROR, __("error.general"));
					} else {
						$user->setHeader($header);
					}
				} catch (ProfileImageInvalidException $e) {
					$save = false;
					$this->addFlash(FlashMessageType::ERROR, __("error.invalidImage"));
				} catch (ProfileImageTooBigException $e) {
					$save = false;
					$this->addFlash(FlashMessageType::ERROR, __("settings.profile.appearance.error.headerTooBig", ["%size%" => "5MB"]));
				}
			} else if ($header === "") { // header was deleted
				$user->setHeader(null);
			}

			// Avatar
			$avatar = $parameters->get("avatar");
			if ($save && $avatar !== $user->getAvatarURL() && $avatar !== "") { // new header has been uploaded
				try {
					$avatar = $imageService->upload($avatar, 2, 300, 300);

					if (is_null($avatar)) {
						$save = false;
						$this->addFlash(FlashMessageType::ERROR, __("error.general"));
					} else {
						$user->setAvatar($avatar);
					}
				} catch (ProfileImageInvalidException $e) {
					$save = false;
					$this->addFlash(FlashMessageType::ERROR, __("error.invalidImage"));
				} catch (ProfileImageTooBigException $e) {
					$save = false;
					$this->addFlash(FlashMessageType::ERROR, __("settings.profile.appearance.error.avatarTooBig", ["%size%" => "5MB"]));
				}
			} else if ($avatar === "") { // header was deleted
				$user->setAvatar(null);
			}

			$user
				->setBirthday(Util::isEmpty($birthday) ? null : new DateTime($birthday))
				->setDisplayName($displayName)
				->setBio($bio);

			if ($save === true) {
				$entityManager->persist($user);
				$entityManager->flush();

				$this->addFlash(FlashMessageType::SUCCESS, __("success.changesSaved"));
			}
		}

		return $this->renderAction(__("settings.profile.appearance.headline"), "settings/profile/appearance.html.twig", SettingsNavigationPoint::PROFILE_APPEARANCE);
	}

	/**
	 * @Route("/settings/profile/linked-accounts")
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @return Response
	 */
	public function profileLinkedAccounts(Request $request, EntityManagerInterface $entityManager) {
		if ($this->csrf()) {
			$parameters = $request->request;

			if ($parameters->has("action")) {
				$action = $parameters->get("action");

				if ($action === "delete") {
					if ($parameters->has("id")) {
						$id = $parameters->get("id");
						$linkedAccount = $entityManager->getRepository(LinkedAccount::class)->findOneBy([
							"id" => $id,
							"user" => $this->getUser()
						]);

						if ($linkedAccount) {
							$entityManager->remove($linkedAccount);
							$entityManager->flush();

							$this->addFlash(FlashMessageType::SUCCESS, __("settings.profile.linkedAccounts.unlinked"));
						}
					}
				} else if ($action === "update") {
					if ($parameters->has("id")) {
						$id = $parameters->get("id");
						$linkedAccount = $entityManager->getRepository(LinkedAccount::class)->findOneBy([
							"id" => $id,
							"user" => $this->getUser()
						]);

						if ($linkedAccount) {
							$linkedAccount->setOnProfile($this->readCheckbox("onProfile"));

							$entityManager->persist($linkedAccount);
							$entityManager->flush();

							$this->addFlash(FlashMessageType::SUCCESS, __("success.changesSaved"));
						}
					}
				}
			}
		}

		return $this->renderAction(__("settings.profile.linkedAccounts.headline"), "settings/profile/linkedAccounts.html.twig", SettingsNavigationPoint::PROFILE_LINKED_ACCOUNTS);
	}

	/**
	 * @Route("/settings/preferences/appearance")
	 * @param EntityManagerInterface $entityManager
	 * @return Response
	 */
	public function preferencesAppearance(EntityManagerInterface $entityManager) {
		if ($this->csrf()) {
			try {
				/**
				 * @var User $user
				 */
				$user = $this->getUser();

				// Update language
				$language = $this->stringParam("language", 2, 5);
				if ($language !== $this->i18n->getCurrentLanguage()) { // only update when user actually chose a new language
					if ($this->i18n->isValidLanguage($language)) {
						$user->setInterfaceLanguage($language);
					}
				}

				$entityManager->persist($user->getAppearanceSettings()
					->setNightMode($this->readCheckbox("nightMode"))
					->setAutoplayGifs($this->readCheckbox("autoplayGifs"))
					->setShowTrends($this->readCheckbox("showTrends"))
					->setShowSuggestedUsers($this->readCheckbox("showSuggestedUsers"))
					->setShowBirthdays($this->readCheckbox("showBirthdays"))
					->setShowMatureWarning($this->readCheckbox("showMatureWarning")));

				$entityManager->flush();

				$this->addFlash(FlashMessageType::SUCCESS, __("success.changesSaved"));
			} catch (FormException $e) {
				$this->addFlash(FlashMessageType::ERROR, $e->getMessage());
			}
		}

		return $this->renderAction(__("settings.preferences.appearance.headline"), "settings/preferences/appearance.html.twig", SettingsNavigationPoint::PREFERENCES_APPEARANCE, [
			"availableLanguages" => $this->i18n->getAvailableLanguages(),
			"currentLanguage" => $this->i18n->getCurrentLanguage()
		]);
	}

	/**
	 * @Route("/settings/relationships/following")
	 * @param Request $request
	 * @return Response
	 */
	public function relationshipsFollowing(Request $request) {
		return $this->renderAction(__("settings.relationships.following.headline"), "settings/relationships/following.html.twig", SettingsNavigationPoint::RELATIONSHIPS_FOLLOWING);
	}

	/**
	 * @Route("/settings/relationships/followers")
	 * @param Request $request
	 * @return Response
	 */
	public function relationshipsFollowers(Request $request) {
		return $this->renderAction(__("settings.relationships.followers.headline"), "settings/relationships/followers.html.twig", SettingsNavigationPoint::RELATIONSHIPS_FOLLOWERS);
	}

	/**
	 * @Route("/settings/relationships/blocked")
	 * @param Request $request
	 * @return Response
	 */
	public function relationshipsBlocked(Request $request) {
		return $this->renderAction(__("settings.relationships.blocked.headline"), "settings/relationships/blocked.html.twig", SettingsNavigationPoint::RELATIONSHIP_BLOCKED);
	}

	/**
	 * @Route("/settings/account/information")
	 * @param Request $request
	 * @return Response
	 */
	public function accountInformation(Request $request) {
		return $this->renderAction(__("settings.account.information.headline"), "settings/account/information.html.twig", SettingsNavigationPoint::ACCOUNT_INFORMATION);
	}

	/**
	 * @Route("/settings/account/username")
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @param NameHistoryService $nameHistoryService
	 * @return Response
	 * @throws Exception
	 */
	public function accountUsername(Request $request, EntityManagerInterface $entityManager, NameHistoryService $nameHistoryService) {
		if ($this->csrf()) {
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

											$nameHistoryService->createEntry($user, $username, $request->getClientIp(), $now);

											$this->addFlash(FlashMessageType::SUCCESS, __("settings.account.username.changed"));
										} else {
											$this->addFlash(FlashMessageType::ERROR, __("settings.account.username.error.notAvailable"));
										}
									} else {
										$this->addFlash(FlashMessageType::ERROR, __("settings.account.username.error.isCurrent"));
									}
								} else {
									$this->addFlash(FlashMessageType::ERROR, __("settings.account.username.error.cooldown"));
								}
							} else {
								$this->addFlash(FlashMessageType::ERROR, __("settings.account.username.error.tooLong"));
							}
						} else {
							$this->addFlash(FlashMessageType::ERROR, __("settings.account.username.error.tooShort"));
						}
					} else {
						$this->addFlash(FlashMessageType::ERROR, __("settings.account.username.error.invalidCharacters"));
					}
				} else {
					$this->addFlash(FlashMessageType::ERROR, __("settings.account.username.error.enterUsername"));
				}
			} else {
				$this->addFlash(FlashMessageType::ERROR, __("settings.account.username.error.enterUsername"));
			}
		}

		return $this->renderAction(__("settings.account.username.headline"), "settings/account/username.html.twig", SettingsNavigationPoint::ACCOUNT_USERNAME);
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
			$this->addFlash(FlashMessageType::ERROR, __("error.notAllowed"));
			return $this->redirectToRoute("qpost_settings_accountinformation");
		}

		if ($this->csrf()) {
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

											$this->addFlash(FlashMessageType::SUCCESS, __("success.changesSaved"));
										} else {
											$this->addFlash(FlashMessageType::ERROR, __("settings.account.password.error.currentIncorrect"));
										}
									} else {
										$this->addFlash(FlashMessageType::ERROR, __("settings.account.password.error.noMatch"));
									}
								} else {
									$this->addFlash(FlashMessageType::ERROR, __("error.fillAll"));
								}
							} else {
								$this->addFlash(FlashMessageType::ERROR, __("error.fillAll"));
							}
						} else {
							$this->addFlash(FlashMessageType::ERROR, __("error.fillAll"));
						}
					} else {
						$this->addFlash(FlashMessageType::ERROR, __("error.fillAll"));
					}
				} else {
					$this->addFlash(FlashMessageType::ERROR, __("error.fillAll"));
				}
			} else {
				$this->addFlash(FlashMessageType::ERROR, __("error.fillAll"));
			}
		}

		return $this->renderAction(__("settings.account.password.headline"), "settings/account/password.html.twig", SettingsNavigationPoint::ACCOUNT_PASSWORD);
	}

	/**
	 * @Route("/settings/account/sessions")
	 * @param Request $request
	 * @return Response
	 */
	public function accountSessions(Request $request) {
		return $this->renderAction(__("settings.account.sessions.headline"), "settings/account/sessions.html.twig", SettingsNavigationPoint::ACCOUNT_ACTIVE_SESSIONS);
	}

	/**
	 * @Route("/settings/account/delete")
	 * @param Request $request
	 * @param DataDeletionService $deletionService
	 * @param GigadriveService $gigadriveService
	 * @return Response
	 */
	public function accountDelete(Request $request, DataDeletionService $deletionService, GigadriveService $gigadriveService) {
		if ($this->csrf()) {
			$parameters = $request->request;

			/**
			 * @var User $user
			 */
			$user = $this->getUser();

			if ($parameters->has("password")) {
				$password = $parameters->get("password");

				$gigadriveData = $user->getGigadriveData();

				$correctPassword = $gigadriveData ? $gigadriveService->verifyPassword($gigadriveData->getAccountId(), $password) : password_verify($password, $user->getPassword());

				if ($correctPassword) {
					$deletionService->deleteUser($user);

					$response = $this->redirectToRoute("qpost_page_goodbye");
					$response->headers->clearCookie("sesstoken"); // TODO: Update cookie name

					return $response;
				} else {
					$this->addFlash(FlashMessageType::ERROR, __("login.error.invalidCredentials"));
				}
			} else {
				$this->addFlash(FlashMessageType::ERROR, __("error.fillAll"));
			}
		}

		return $this->renderAction(__("settings.account.delete.headline"), "settings/account/delete.html.twig", SettingsNavigationPoint::ACCOUNT_INFORMATION);
	}

	/**
	 * @Route("/settings/privacy")
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @return Response
	 */
	public function privacy(Request $request, EntityManagerInterface $entityManager) {
		if ($this->csrf()) {
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

					$this->addFlash(FlashMessageType::SUCCESS, __("success.changesSaved"));
				} else {
					$this->addFlash(FlashMessageType::ERROR, __("error.general"));
				}
			} else {
				$this->addFlash(FlashMessageType::ERROR, __("error.fillAll"));
			}
		}

		return $this->renderAction(__("settings.account.privacy.headline"), "settings/privacy/privacy.html.twig", SettingsNavigationPoint::PRIVACY);
	}

	private function renderAction(string $headline, string $template, ?string $activeMenuPoint, array $additionalParameters = []) {
		return $this->render($template, array_merge(Twig::param([
			"title" => $headline,
			SettingsNavigationPoint::VARIABLE_NAME => $activeMenuPoint
		]), $additionalParameters));
	}
}
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
					$this->addFlash(FlashMessageType::ERROR, "The display name must be between 1 and 24 characters long.");
				}
			}

			// Bio
			$bio = $parameters->get("bio");
			if ($bio !== $user->getBio()) {
				$bio = trim(Util::fixString($bio));

				if (!Util::isEmpty($bio) && !(strlen($bio) >= 0 && strlen($bio) <= 200)) {
					$save = false;
					$this->addFlash(FlashMessageType::ERROR, "The bio must be between 0 and 200 characters long.");
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
						$this->addFlash(FlashMessageType::ERROR, "You have to be at least 13 years old and at the most 120 years old.");
					}
				} else {
					$save = false;
					$this->addFlash(FlashMessageType::ERROR, "Please enter a valid birthday.");
				}
			}

			// Header
			$header = $parameters->get("header");
			if ($save && $header !== $user->getHeader() && $header !== "") { // new header has been uploaded
				try {
					$header = $imageService->upload($header, 5, 1500, 500);

					if (is_null($header)) {
						$save = false;
						$this->addFlash(FlashMessageType::ERROR, "An error occurred trying to upload the header image.");
					} else {
						$user->setHeader($header);
					}
				} catch (ProfileImageInvalidException $e) {
					$save = false;
					$this->addFlash(FlashMessageType::ERROR, "Please upload a valid header image.");
				} catch (ProfileImageTooBigException $e) {
					$save = false;
					$this->addFlash(FlashMessageType::ERROR, "The header image may not be bigger than 5MB.");
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
						$this->addFlash(FlashMessageType::ERROR, "An error occurred trying to upload the avatar image.");
					} else {
						$user->setAvatar($avatar);
					}
				} catch (ProfileImageInvalidException $e) {
					$save = false;
					$this->addFlash(FlashMessageType::ERROR, "Please upload a valid avatar image.");
				} catch (ProfileImageTooBigException $e) {
					$save = false;
					$this->addFlash(FlashMessageType::ERROR, "The avatar image may not be bigger than 2MB.");
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

				$this->addFlash(FlashMessageType::SUCCESS, "Your changes have been saved.");
			}
		}

		return $this->renderAction("Edit profile", "settings/profile/appearance.html.twig", SettingsNavigationPoint::PROFILE_APPEARANCE);
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

							$this->addFlash(FlashMessageType::SUCCESS, "The account has been unlinked.");
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

							$this->addFlash(FlashMessageType::SUCCESS, "Your changes have been saved.");
						}
					}
				}
			}
		}

		return $this->renderAction("Linked Accounts", "settings/profile/linkedAccounts.html.twig", SettingsNavigationPoint::PROFILE_LINKED_ACCOUNTS);
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

				$this->addFlash(FlashMessageType::SUCCESS, "Your changes have been saved.");
			} catch (FormException $e) {
				$this->addFlash(FlashMessageType::ERROR, $e->getMessage());
			}
		}

		return $this->renderAction("Appearance", "settings/preferences/appearance.html.twig", SettingsNavigationPoint::PREFERENCES_APPEARANCE, [
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
		return $this->renderAction("Following", "settings/relationships/following.html.twig", SettingsNavigationPoint::RELATIONSHIPS_FOLLOWING);
	}

	/**
	 * @Route("/settings/relationships/followers")
	 * @param Request $request
	 * @return Response
	 */
	public function relationshipsFollowers(Request $request) {
		return $this->renderAction("Followers", "settings/relationships/followers.html.twig", SettingsNavigationPoint::RELATIONSHIPS_FOLLOWERS);
	}

	/**
	 * @Route("/settings/relationships/blocked")
	 * @param Request $request
	 * @return Response
	 */
	public function relationshipsBlocked(Request $request) {
		return $this->renderAction("Blocked accounts", "settings/relationships/blocked.html.twig", SettingsNavigationPoint::RELATIONSHIP_BLOCKED);
	}

	/**
	 * @Route("/settings/account/information")
	 * @param Request $request
	 * @return Response
	 */
	public function accountInformation(Request $request) {
		return $this->renderAction("Account information", "settings/account/information.html.twig", SettingsNavigationPoint::ACCOUNT_INFORMATION);
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

		return $this->renderAction("Change username", "settings/account/username.html.twig", SettingsNavigationPoint::ACCOUNT_USERNAME);
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

		return $this->renderAction("Change password", "settings/account/password.html.twig", SettingsNavigationPoint::ACCOUNT_PASSWORD);
	}

	/**
	 * @Route("/settings/account/sessions")
	 * @param Request $request
	 * @return Response
	 */
	public function accountSessions(Request $request) {
		return $this->renderAction("Active sessions", "settings/account/sessions.html.twig", SettingsNavigationPoint::ACCOUNT_ACTIVE_SESSIONS);
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
					$response->headers->clearCookie("sesstoken");

					return $response;
				} else {
					$this->addFlash(FlashMessageType::ERROR, "Your password is incorrect.");
				}
			} else {
				$this->addFlash(FlashMessageType::ERROR, "Please fill all the fields.");
			}
		}

		return $this->renderAction("Delete your account", "settings/account/delete.html.twig", SettingsNavigationPoint::ACCOUNT_INFORMATION);
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

					$this->addFlash(FlashMessageType::SUCCESS, "Your changes have been saved.");
				} else {
					$this->addFlash(FlashMessageType::ERROR, "Please try again.");
				}
			} else {
				$this->addFlash(FlashMessageType::ERROR, "Please fill all the fields.");
			}
		}

		return $this->renderAction("Privacy", "settings/privacy/privacy.html.twig", SettingsNavigationPoint::PRIVACY);
	}

	private function renderAction(string $headline, string $template, ?string $activeMenuPoint, array $additionalParameters = []) {
		return $this->render($template, array_merge(Twig::param([
			"title" => $headline,
			SettingsNavigationPoint::VARIABLE_NAME => $activeMenuPoint
		]), $additionalParameters));
	}
}
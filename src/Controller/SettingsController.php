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

use qpost\Constants\MiscConstants;
use qpost\Constants\SettingsNavigationPoint;
use qpost\Twig\Twig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function array_merge;

class SettingsController extends AbstractController {
	/**
	 * @Route("/settings/profile/appearance")
	 */
	public function profileAppearance() {
		return $this->renderAction("Edit profile", "settings/profile/appearance.html.twig", SettingsNavigationPoint::PROFILE_APPEARANCE, $this->generateUrl(
			"qpost_settings_profileappearance", [], UrlGeneratorInterface::ABSOLUTE_URL
		));
	}

	/**
	 * @Route("/settings/preferences/appearance")
	 */
	public function preferencesAppearance() {
		return $this->renderAction("Appearance", "settings/preferences/appearance.html.twig", SettingsNavigationPoint::PREFERENCES_APPEARANCE, $this->generateUrl(
			"qpost_settings_profileappearance", [], UrlGeneratorInterface::ABSOLUTE_URL
		));
	}

	/**
	 * @Route("/settings/preferences/content")
	 */
	public function preferencesContent() {
		return $this->renderAction("Content settings", "settings/preferences/content.html.twig", SettingsNavigationPoint::PREFERENCES_CONTENT, $this->generateUrl(
			"qpost_settings_profileappearance", [], UrlGeneratorInterface::ABSOLUTE_URL
		));
	}

	/**
	 * @Route("/settings/relationships/following")
	 */
	public function relationshipsFollowing() {
		return $this->renderAction("Following", "settings/relationships/following.html.twig", SettingsNavigationPoint::RELATIONSHIPS_FOLLOWING, $this->generateUrl(
			"qpost_settings_profileappearance", [], UrlGeneratorInterface::ABSOLUTE_URL
		));
	}

	/**
	 * @Route("/settings/relationships/followers")
	 */
	public function relationshipsFollowers() {
		return $this->renderAction("Followers", "settings/relationships/followers.html.twig", SettingsNavigationPoint::RELATIONSHIPS_FOLLOWERS, $this->generateUrl(
			"qpost_settings_profileappearance", [], UrlGeneratorInterface::ABSOLUTE_URL
		));
	}

	/**
	 * @Route("/settings/relationships/blocked")
	 */
	public function relationshipsBlocked() {
		return $this->renderAction("Blocked accounts", "settings/relationships/blocked.html.twig", SettingsNavigationPoint::RELATIONSHIP_BLOCKED, $this->generateUrl(
			"qpost_settings_profileappearance", [], UrlGeneratorInterface::ABSOLUTE_URL
		));
	}

	/**
	 * @Route("/settings/account/information")
	 */
	public function accountInformation() {
		return $this->renderAction("Account information", "settings/account/information.html.twig", SettingsNavigationPoint::ACCOUNT_INFORMATION, $this->generateUrl(
			"qpost_settings_profileappearance", [], UrlGeneratorInterface::ABSOLUTE_URL
		));
	}

	/**
	 * @Route("/settings/account/username")
	 */
	public function accountUsername() {
		return $this->renderAction("Change username", "settings/account/username.html.twig", SettingsNavigationPoint::ACCOUNT_USERNAME, $this->generateUrl(
			"qpost_settings_profileappearance", [], UrlGeneratorInterface::ABSOLUTE_URL
		));
	}

	/**
	 * @Route("/settings/account/password")
	 */
	public function accountPassword() {
		return $this->renderAction("Change password", "settings/account/password.html.twig", SettingsNavigationPoint::ACCOUNT_PASSWORD, $this->generateUrl(
			"qpost_settings_profileappearance", [], UrlGeneratorInterface::ABSOLUTE_URL
		));
	}

	/**
	 * @Route("/settings/account/sessions")
	 */
	public function accountSessions() {
		return $this->renderAction("Active sessions", "settings/account/sessions.html.twig", SettingsNavigationPoint::ACCOUNT_ACTIVE_SESSIONS, $this->generateUrl(
			"qpost_settings_profileappearance", [], UrlGeneratorInterface::ABSOLUTE_URL
		));
	}

	/**
	 * @Route("/settings/privacy")
	 */
	public function privacy() {
		return $this->renderAction("Privacy", "settings/privacy/privacy.html.twig", SettingsNavigationPoint::PRIVACY, $this->generateUrl(
			"qpost_settings_profileappearance", [], UrlGeneratorInterface::ABSOLUTE_URL
		));
	}

	private function renderAction(string $headline, string $template, ?string $activeMenuPoint, string $canonicalURL, array $additionalParameters = []) {
		return $this->render($template, array_merge(Twig::param([
			"title" => $headline,
			MiscConstants::CANONICAL_URL => $canonicalURL,
			SettingsNavigationPoint::VARIABLE_NAME => $activeMenuPoint
		]), $additionalParameters));
	}
}
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
	 * @Route("/account/profile/appearance")
	 */
	public function profileAppearance() {
		return $this->renderAction("Edit profile", "settings/profile/appearance.html.twig", SettingsNavigationPoint::PROFILE_APPEARANCE, $this->generateUrl(
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
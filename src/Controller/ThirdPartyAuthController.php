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

use qpost\Service\OAuth\ThirdPartyIntegrationManagerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use function is_null;

class ThirdPartyAuthController extends AbstractController {
	/**
	 * @Route("/tpauth/{service}")
	 * @param string $service
	 * @param ThirdPartyIntegrationManagerService $integrationManagerService
	 * @return RedirectResponse
	 */
	public function auth(string $service, ThirdPartyIntegrationManagerService $integrationManagerService) {
		$integration = $integrationManagerService->getIntegrationService($service);

		if (is_null($integration)) {
			throw $this->createNotFoundException("Unknown service.");
		}

		$authURL = $integration->getAuthenticationURL();

		if (is_null($authURL)) {
			throw $this->createNotFoundException("Failed to find auth URL for this service.");
		}

		return $this->redirect($authURL);
	}
}
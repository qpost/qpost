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
use Exception;
use qpost\Entity\LinkedAccount;
use qpost\Entity\User;
use qpost\Service\OAuth\ThirdPartyIntegrationManagerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use function is_null;
use function strtoupper;

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

	/**
	 * @Route("/callbacks/{service}")
	 * @param string $service
	 * @param ThirdPartyIntegrationManagerService $integrationManagerService
	 * @param Request $request
	 * @return RedirectResponse
	 * @throws Exception
	 */
	public function callback(string $service, ThirdPartyIntegrationManagerService $integrationManagerService, Request $request) {
		if ($request->query->has("error")) {
			return $this->redirectToRoute("qpost_settings_profilelinkedaccounts");
		}

		/**
		 * @var User $user
		 */
		$user = $this->getUser();
		$integration = $integrationManagerService->getIntegrationService($service);

		if (is_null($integration)) {
			throw $this->createNotFoundException("Unknown service.");
		}

		$service = strtoupper($service);

		if (!$request->query->has("code")) {
			throw $this->createNotFoundException("No exchange code found.");
		}

		$code = $request->query->get("code");
		$codeResult = $integration->exchangeCode($code);

		if (is_null($codeResult)) {
			throw $this->createNotFoundException("Invalid code.");
		}

		$identificationResult = $integration->identify($codeResult);

		if (is_null($codeResult)) {
			throw new Exception("Failed to identify user.");
		}

		$linkedAccount = $user->getLinkedService($service);
		if (is_null($linkedAccount)) {
			$linkedAccount = (new LinkedAccount())
				->setUser($user)
				->setService($service)
				->setTime(new DateTime("now"));
		}

		$expiresIn = $codeResult->getExpiresIn();
		if (!is_null($expiresIn)) {
			$expiry = new DateTime("now");
			$expiry->add(new DateInterval("PT" . $codeResult->getExpiresIn() . "S"));

			$linkedAccount->setExpiry($expiry);
		}

		$linkedAccount->setAccessToken($codeResult->getAccessToken())
			->setRefreshToken($codeResult->getRefreshToken())
			->setClientId($codeResult->getClientId())
			->setClientSecret($codeResult->getClientSecret());

		$integration->updateIdentification($linkedAccount, $identificationResult);

		return $this->redirectToRoute("qpost_settings_profilelinkedaccounts");
	}
}
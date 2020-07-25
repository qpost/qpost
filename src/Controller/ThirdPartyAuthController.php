<?php
/**
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
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Gigadrive\Bundle\SymfonyExtensionsBundle\Controller\GigadriveController;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use qpost\Constants\LinkedAccountService;
use qpost\Entity\LinkedAccount;
use qpost\Entity\TemporaryOAuthCredentials;
use qpost\Entity\User;
use qpost\Service\OAuth\ThirdPartyIntegrationManagerService;
use qpost\Service\OAuth\TwitterIntegration;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use function is_null;
use function serialize;
use function strtoupper;
use function unserialize;

class ThirdPartyAuthController extends GigadriveController {
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

		/**
		 * @var User $user
		 */
		$user = $this->getUser();

		$authURL = $integration->getAuthenticationURL($user);

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
	 * @param EntityManagerInterface $entityManager
	 * @return RedirectResponse
	 * @throws Exception
	 */
	public function callback(string $service, ThirdPartyIntegrationManagerService $integrationManagerService, Request $request, EntityManagerInterface $entityManager) {
		if ($request->query->has("error") || $request->query->has("denied")) { // return to linked accounts page if user denied access
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

		if ($service === LinkedAccountService::TWITTER && $integration instanceof TwitterIntegration) {
			// Twitter OAuth 1.0a
			if (!$request->query->has("oauth_token") || !$request->query->has("oauth_verifier")) {
				throw $this->createNotFoundException("No tokens found.");
			}

			$temporaryCredentials = $entityManager->getRepository(TemporaryOAuthCredentials::class)->getTemporaryCredentialsByUser($user);

			if (is_null($temporaryCredentials)) {
				throw $this->createNotFoundException("No temporary credentials found.");
			}

			$token = $request->query->get("oauth_token");
			$verifier = $request->query->get("oauth_verifier");

			$server = $integration->getOAuthServer();

			$deserialized = unserialize($temporaryCredentials->getCredentials());
			if (!$deserialized instanceof TemporaryCredentials) {
				throw $this->createNotFoundException("Invalid credentials supplied.");
			}

			$tokenCredentials = $server->getTokenCredentials(unserialize($temporaryCredentials->getCredentials()), $token, $verifier);

			$linkedAccount = $user->getLinkedService($service);

			if (is_null($linkedAccount)) {
				$linkedAccount = (new LinkedAccount())
					->setUser($user)
					->setTime(new DateTime("now"))
					->setService($service);
			}

			$linkedAccount->setClientId($integration->getClientId())
				->setClientSecret($integration->getClientSecret())
				->setAccessToken($tokenCredentials->getIdentifier())
				->setRefreshToken($tokenCredentials->getSecret());

			$temporaryCredentials->setCredentials(serialize($tokenCredentials));

			$entityManager->persist($temporaryCredentials);
			$entityManager->flush();

			$identificationResult = $integration->identify($linkedAccount);

			$integration->updateIdentification($linkedAccount, $identificationResult);

			return $this->redirectToRoute("qpost_settings_profilelinkedaccounts");
		} else {
			$codeFieldName = $service === LinkedAccountService::LASTFM ? "token" : "code";

			// OAuth 2
			if (!$request->query->has($codeFieldName)) {
				throw $this->createNotFoundException("No exchange code found.");
			}

			$code = $request->query->get($codeFieldName);
			$codeResult = $integration->exchangeCode($code);

			if (is_null($codeResult)) {
				throw $this->createNotFoundException("Invalid code.");
			}

			$identificationResult = $integration->identify($codeResult);

			if (is_null($identificationResult)) {
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
}
<?php
/*
 * Copyright (C) 2018-2021 Gigadrive - All rights reserved.
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

namespace qpost\Service\OAuth;

use DateTime;
use Exception;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\Twitter;
use qpost\Constants\LinkedAccountService;
use qpost\Entity\LinkedAccount;
use qpost\Entity\TemporaryOAuthCredentials;
use qpost\Entity\User;
use function get_class;
use function is_null;
use function serialize;
use function unserialize;

class TwitterIntegration extends ThirdPartyIntegration {
	public function getServiceIdentifier(): ?string {
		return LinkedAccountService::TWITTER;
	}

	public function getScopes(): ?array {
		return ["identify"];
	}

	public function getAuthenticationURL(User $user): string {
		$server = $this->getOAuthServer();
		$credentialsRepository = $this->entityManager->getRepository(TemporaryOAuthCredentials::class);
		$credentials = $credentialsRepository->getTemporaryCredentialsByUser($user);

		if (is_null($credentials)) {
			$credentials = (new TemporaryOAuthCredentials())
				->setUser($user);
		}

		$value = $server->getTemporaryCredentials();

		$credentials->setCredentials(serialize($value))
			->setTime(new DateTime("now"));

		$this->entityManager->persist($credentials);
		$this->entityManager->flush();

		return $server->getAuthorizationUrl($value);
	}

	public function identify($credentials): ?ThirdPartyIntegrationIdentificationResult {
		try {
			if (!$credentials instanceof LinkedAccount) return null;
			$linkedAccount = $credentials;

			$server = $this->getOAuthServer($credentials->getClientId(), $credentials->getClientSecret());

			$credentialsRepository = $this->entityManager->getRepository(TemporaryOAuthCredentials::class);
			$credentials = $credentialsRepository->getTemporaryCredentialsByUser($credentials->getUser());

			$deserialized = null;

			if (is_null($credentials)) {
				$credentials = new TokenCredentials();
				$credentials->setIdentifier($linkedAccount->getAccessToken());
				$credentials->setSecret($linkedAccount->getRefreshToken());

				$deserialized = $credentials;
			} else {
				$deserialized = unserialize($credentials->getCredentials());
				if (!$deserialized instanceof TokenCredentials) {
					throw new Exception("Invalid credentials supplied.");
				}
			}

			$details = $server->getUserDetails($deserialized);

			if ($credentials instanceof TemporaryOAuthCredentials) {
				$this->entityManager->remove($credentials);
			}

			$this->logger->info("identification", [
				"details" => $details
			]);

			return new ThirdPartyIntegrationIdentificationResult(
				$details->uid,
				$details->nickname,
				$details->imageUrl
			);
		} catch (Exception $e) {
			$this->logger->error("Exception while updating Twitter identification (" . get_class($e) . "): " . $e->getMessage());
			return null;
		}
	}

	public function refreshToken(LinkedAccount $account): ?LinkedAccount {
		return $account;
	}

	public function getOAuthServer(?string $identifier = null, ?string $secret = null): Twitter {
		if (is_null($identifier)) {
			$identifier = $this->getClientId();
		}

		if (is_null($secret)) {
			$secret = $this->getClientSecret();
		}

		return new Twitter([
			"identifier" => $identifier,
			"secret" => $secret,
			"callback_uri" => $this->getRedirectURL()
		]);
	}
}
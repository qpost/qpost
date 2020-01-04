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

namespace qpost\Security;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use qpost\Entity\Token;
use qpost\Service\TokenService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class qpostAuthenticator extends AbstractGuardAuthenticator {
	use TargetPathTrait;

	private $entityManager;
	private $urlGenerator;
	private $csrfTokenManager;
	private $logger;
	private $tokenService;

	public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, LoggerInterface $logger, TokenService $tokenService) {
		$this->entityManager = $entityManager;
		$this->urlGenerator = $urlGenerator;
		$this->csrfTokenManager = $csrfTokenManager;
		$this->logger = $logger;
		$this->tokenService = $tokenService;
	}

	public function supports(Request $request) {
		return !is_null($this->tokenService->getTokenFromRequest($request));
	}

	public function getCredentials(Request $request) {
		$token = $this->tokenService->getTokenFromRequest($request);
		return !is_null($token) ? ["token" => $token->getId()] : ["token" => null];
	}

	public function getUser($credentials, UserProviderInterface $userProvider) {
		$tokenId = $credentials["token"];
		if (!is_null($tokenId)) {
			$token = $this->entityManager->getRepository(Token::class)->getTokenById($tokenId);

			if ($token && !$token->isExpired()) {
				$token->setLastAccessTime(new DateTime("now"));
				$this->entityManager->persist($token);
				$this->entityManager->flush();

				return $token->getUser();
			}
		}

		return null;
	}

	public function checkCredentials($credentials, UserInterface $user) {
		return true;
	}

	public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey) {
		if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
			return new RedirectResponse($targetPath);
		}

		return null;
	}

	public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
		$this->logger->error("Authentication failure", [
			"request" => $request,
			"exception" => $exception
		]);

		return null;
	}

	public function start(Request $request, AuthenticationException $authException = null) {
		return new RedirectResponse($this->getLoginUri());
	}

	protected function getLoginUri() {
		return $this->urlGenerator->generate("qpost_login_index");
	}

	public function supportsRememberMe() {
		return false;
	}
}
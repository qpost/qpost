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

namespace qpost\Service;

use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use qpost\Entity\Token;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use function array_merge;
use function array_slice;
use function is_string;
use function json_decode;
use function json_encode;
use function strlen;
use function substr;

class TokenService {
	/**
	 * @var LoggerInterface $logger
	 */
	private $logger;

	/**
	 * @var EntityManagerInterface $entityManager
	 */
	private $entityManager;

	/**
	 * @var RequestStack $requestStack
	 */
	private $requestStack;

	public const TOKEN_COOKIE_IDENTIFIER = "qpoststoredtokens";

	public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, RequestStack $requestStack) {
		$this->logger = $logger;
		$this->entityManager = $entityManager;
		$this->requestStack = $requestStack;
	}

	public function getCurrentToken(): ?Token {
		$request = $this->requestStack->getCurrentRequest();

		return $this->getTokenFromRequest($request);
	}

	public function getTokenFromRequest(Request $request): ?Token {
		$token = null;
		$authorizationHeader = $request->headers->get("Authorization");

		if ($authorizationHeader && is_string($authorizationHeader)) {
			$prefix = "Bearer ";

			// Check if starts with token type prefix
			if (strlen($authorizationHeader) > strlen($prefix) && substr($authorizationHeader, 0, strlen($prefix)) === $prefix) {
				$token = substr($authorizationHeader, strlen($prefix));
			}
		} else if ($request->cookies->has(self::TOKEN_COOKIE_IDENTIFIER)) {
			$cookieTokens = $this->getCookieTokens($request);

			if (count($cookieTokens) > 0) $token = $cookieTokens[0];
		}

		$token = $this->entityManager->getRepository(Token::class)->getTokenById($token);

		if ($token && $request->headers->has("User-Agent")) {
			$token->setUserAgent($request->headers->get("User-Agent"));

			$this->entityManager->persist($token);
			$this->entityManager->flush();
		}

		return $token;
	}

	public function getCookieTokens(Request $request): array {
		if (!$request->cookies->has(self::TOKEN_COOKIE_IDENTIFIER)) return [];

		return json_decode($request->cookies->get(self::TOKEN_COOKIE_IDENTIFIER));
	}

	public function addToken(string $token, Request $request, Response $response): void {
		$response->headers->setCookie(Cookie::create(self::TOKEN_COOKIE_IDENTIFIER, json_encode(array_slice(array_merge([$token], $this->getCookieTokens($request)), 0, 10)), (new DateTime("now"))->add(DateInterval::createFromDateString("1 year")), "/", null, null, false));
	}

	/**
	 * @return LoggerInterface
	 */
	public function getLogger(): LoggerInterface {
		return $this->logger;
	}

	/**
	 * @return EntityManagerInterface
	 */
	public function getEntityManager(): EntityManagerInterface {
		return $this->entityManager;
	}

	/**
	 * @return RequestStack
	 */
	public function getRequestStack(): RequestStack {
		return $this->requestStack;
	}
}
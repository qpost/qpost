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
use function apache_request_headers;
use function array_key_exists;
use function array_merge;
use function array_slice;
use function count;
use function function_exists;
use function is_null;
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
		$authorizationHeaderPrefix = "Bearer ";
		$authorizationHeader = $this->getAuthorizationHeader($request);

		if (!is_null($authorizationHeader)) {
			// Check if starts with token type prefix
			if (strlen($authorizationHeader) > strlen($authorizationHeaderPrefix) && substr($authorizationHeader, 0, strlen($authorizationHeaderPrefix)) === $authorizationHeaderPrefix) {
				$token = substr($authorizationHeader, strlen($authorizationHeaderPrefix));
			}
		}

		if (is_null($token) && $request->cookies->has(self::TOKEN_COOKIE_IDENTIFIER)) {
			$cookieTokens = $this->getCookieTokens($request);

			if (count($cookieTokens) > 0) $token = $cookieTokens[0];
		}

		$this->logger->info("Current token", ["token" => $token]);

		$token = $this->entityManager->getRepository(Token::class)->getTokenById($token);

		$this->logger->info("Current token object", ["token" => $token]);

		if ($token && $request->headers->has("User-Agent")) {
			$token->setUserAgent($request->headers->get("User-Agent"));

			$this->entityManager->persist($token);
			$this->entityManager->flush();
		}

		return $token;
	}

	private function getAuthorizationHeader(?Request $request = null): ?string {
		$headerName = "Authorization";

		// Check HeaderBag
		if (!is_null($request)) {
			$headers = $request->headers;

			if ($headers->has($headerName)) {
				return $headers->get($headerName);
			}
		}

		// Check Apache headers
		if (function_exists("apache_request_headers")) {
			$headers = apache_request_headers();

			if (array_key_exists($headerName, $headers)) {
				return $headers[$headerName];
			}
		}

		// Check globals
		if (isset($_SERVER["HTTP_AUTHORIZATION"])) {
			return $_SERVER["HTTP_AUTHORIZATION"];
		}

		return null;
	}

	public function getCookieTokens(Request $request): array {
		if (!$request->cookies->has(self::TOKEN_COOKIE_IDENTIFIER)) return [];

		$rawTokens = json_decode($request->cookies->get(self::TOKEN_COOKIE_IDENTIFIER));
		$tokens = [];

		$this->logger->info("Tokens found", ["tokens" => $rawTokens]);

		foreach ($rawTokens as $token) {
			if ($this->validateToken($token)) {
				$tokens[] = $token;
			}
		}

		$this->logger->info("Tokens found, validated", ["tokens" => $tokens]);

		return $tokens;
	}

	private function validateToken(string $token): bool {
		return !is_null($this->entityManager->getRepository(Token::class)->getTokenById($token));
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
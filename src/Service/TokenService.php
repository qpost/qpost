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

namespace qpost\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use qpost\Entity\Token;
use qpost\Util\Util;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use function is_string;
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
		if ($request->cookies->has("sesstoken")) {
			$token = $request->cookies->get("sesstoken");
		} else if ((Util::startsWith($request->getPathInfo(), "/api") || Util::startsWith($request->getPathInfo(), "/webpush")) && $request->headers->has("Authorization")) {
			$authorization = $request->headers->get("Authorization");

			if ($authorization && is_string($authorization)) {
				$prefix = "Bearer ";

				// Check if starts with token type prefix
				if (strlen($authorization) > strlen($prefix) && substr($authorization, 0, strlen($prefix)) === $prefix) {
					$token = substr($authorization, strlen($prefix));
				}
			}
		}

		$token = $this->entityManager->getRepository(Token::class)->getTokenById($token);

		if ($token && $request->headers->has("User-Agent")) {
			$token->setUserAgent($request->headers->get("User-Agent"));

			$this->entityManager->persist($token);
			$this->entityManager->flush();
		}

		return $token;
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
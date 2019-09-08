<?php
/**
 * Copyright (C) 2018-2019 Gigadrive - All rights reserved.
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

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use qpost\Entity\Token;
use qpost\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use function is_null;
use function is_string;
use function strlen;
use function substr;

class AuthorizationService {
	/**
	 * @var Request $request
	 */
	private $request;

	/**
	 * @var EntityManager $entityManager
	 */
	private $entityManager;

	/**
	 * @var User|null $user
	 */
	private $user;

	/**
	 * @var Token|null $token
	 */
	private $token;

	/**
	 * AuthorizationService constructor.
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(Request $request, EntityManagerInterface $entityManager) {
		$this->request = $request;
		$this->entityManager = $entityManager;

		$this->load();
	}

	private function load(): void {
		// Try loading from headers
		$headers = $this->request->headers;
		if ($headers->has("Authorization")) {
			$authorization = $headers->get("Authorization");

			if ($authorization && is_string($authorization)) {
				$prefix = "Bearer ";

				// Check if starts with token type prefix
				if (strlen($authorization) > strlen($prefix) && substr($authorization, 0, strlen($prefix)) === $prefix) {
					$tokenId = substr($authorization, strlen($prefix));
					if ($this->loadData($tokenId)) {
						return;
					}
				}
			}
		}

		// Try loading from cookies
		$cookies = $this->request->cookies;
		if ($cookies->has("sesstoken")) {
			$tokenId = $cookies->get("sesstoken");

			$this->loadData($tokenId);
		}
	}

	private function loadData(string $tokenId): bool {
		$token = $this->entityManager->getRepository(Token::class)->findOneBy([
			"id" => $tokenId
		]);

		if (!is_null($token)) {
			$this->token = $token;
			$this->user = $token->getUser();
			return true;
		}

		return false;
	}

	/**
	 * @return Request
	 */
	public function getRequest(): Request {
		return $this->request;
	}

	/**
	 * @return EntityManagerInterface
	 */
	public function getEntityManager(): EntityManagerInterface {
		return $this->entityManager;
	}

	/**
	 * @return User|null
	 */
	public function getUser(): ?User {
		return $this->user;
	}

	/**
	 * @return Token|null
	 */
	public function getToken(): ?Token {
		return $this->token;
	}

	/**
	 * @return bool
	 */
	public function isAuthorized(): bool {
		return !is_null($this->user) && !is_null($this->token);
	}
}
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

namespace qpost\Controller\API;

use DateTime;
use qpost\Constants\APIParameterType;
use qpost\Entity\Token;
use qpost\Exception\InvalidParameterTypeException;
use qpost\Exception\InvalidTokenException;
use qpost\Exception\MissingParameterException;
use qpost\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class TokenController extends APIController {
	/**
	 * @Route("/token", methods={"GET"})
	 *
	 * @return Response|null
	 * @throws InvalidTokenException
	 */
	public function tokens() {
		$this->validateAuth();

		return $this->response(
			$this->filterTokens(
				$this->entityManager->getRepository(Token::class)->getTokens($this->getUser())
			)
		);
	}

	/**
	 * @Route("/token", methods={"DELETE"})
	 *
	 * @return Response|null
	 * @throws InvalidTokenException
	 * @throws ResourceNotFoundException
	 * @throws InvalidParameterTypeException
	 * @throws MissingParameterException
	 */
	public function logout() {
		$this->validateAuth();
		$this->validateParameterType("id", APIParameterType::STRING);
		$user = $this->getUser();
		$parameters = $this->parameters();
		$token = $this->entityManager->getRepository(Token::class)->getTokenById($parameters->get("id"));

		if ($token && $token->getUser()->getId() === $user->getId() && !$token->isExpired()) {
			$token->setExpiry(new DateTime("now"));
			$this->entityManager->persist($token);

			foreach ($token->getPushSubscriptions() as $subscription) {
				$this->entityManager->remove($subscription);
			}

			$this->entityManager->flush();

			return $this->response();
		} else {
			throw new ResourceNotFoundException();
		}
	}

	/**
	 * @Route("/token/verify", methods={"POST"})
	 *
	 * @return Response
	 * @throws InvalidTokenException
	 */
	public function verify() {
		$this->validateAuth();
		$token = $this->tokenService->getCurrentToken();
		$user = $token->getUser();

		if (!$user->isSuspended()) {
			if (!$token->isExpired()) {
				return $this->apiService->json([
					"status" => "Token valid",
					"user" => $this->apiService->serialize($user),
					"token" => $token
				]);
			} else {
				return $this->error("Token expired", Response::HTTP_FORBIDDEN);
			}
		} else {
			return $this->error("User suspended", Response::HTTP_FORBIDDEN);
		}
	}
}

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

namespace qpost\Controller\API;

use DateTime;
use Exception;
use qpost\Entity\Token;
use qpost\Service\APIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function is_null;
use function is_string;

class TokenController extends AbstractController {
	/**
	 * @Route("/api/token", methods={"GET"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function tokens(APIService $apiService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$user = $apiService->getUser();

		$results = [];

		/**
		 * @var Token[] $tokens
		 */
		$tokens = $apiService->getEntityManager()->getRepository(Token::class)->getTokens($user);

		foreach ($tokens as $token) {
			if (!$token->isExpired()) {
				$results[] = $apiService->serialize($token);
			}
		}

		return $apiService->json(["results" => $results]);
	}

	/**
	 * @Route("/api/token", methods={"DELETE"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 * @throws Exception
	 */
	public function logout(APIService $apiService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$user = $apiService->getUser();

		$parameters = $apiService->parameters();

		if ($parameters->has("id")) {
			$id = $parameters->get("id");

			if (is_string($id)) {
				$entityManager = $apiService->getEntityManager();

				/**
				 * @var Token $token
				 */
				$token = $entityManager->getRepository(Token::class)->getTokenById($id);

				if ($token && $token->getUser()->getId() === $user->getId() && !$token->isExpired()) {
					$token->setExpiry(new DateTime("now"));
					$entityManager->persist($token);

					foreach ($token->getPushSubscriptions() as $subscription) {
						$entityManager->remove($subscription);
					}

					$entityManager->flush();

					return $apiService->noContent();
				} else {
					return $apiService->json(["error" => "The requested resource could not be found."], 404);
				}
			} else {
				return $apiService->json(["error" => "'id' has to be a string."], 400);
			}
		} else {
			return $apiService->json(["error" => "'id' is required."], 400);
		}
	}

	/**
	 * @Route("/api/token/verify", methods={"POST"})
	 *
	 * @return Response
	 */
	public function verify(APIService $apiService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$token = $apiService->getToken();
		$user = $apiService->getUser();

		if (!$user->isSuspended()) {
			if (!$token->isExpired()) {
				return $apiService->json([
					"status" => "Token valid",
					"user" => $apiService->serialize($user)
				]);
			} else {
				return $apiService->json(["error" => "Token expired"], 403);
			}
		} else {
			return $apiService->json(["error" => "User suspended"], 403);
		}
	}
}

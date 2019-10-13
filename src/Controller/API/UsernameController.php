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

namespace qpost\Controller\API;

use DateTime;
use Exception;
use qpost\Entity\User;
use qpost\Service\APIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function ctype_alnum;
use function is_null;
use function is_string;
use function strlen;
use function strtoupper;

class UsernameController extends AbstractController {
	/**
	 * @Route("/api/username", methods={"POST"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 * @throws Exception
	 */
	public function changeUsername(APIService $apiService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$parameters = $apiService->parameters();
		$user = $apiService->getUser();

		if ($parameters->has("username")) {
			$username = $parameters->get("username");

			if (is_string($username)) {
				if (ctype_alnum($username)) {
					if (strlen($username) >= 3) {
						if (strlen($username) <= 16) {
							$lastChange = $user->getLastUsernameChange();
							$now = new DateTime("now");

							if (is_null($lastChange) || $lastChange->diff($now)->days > 30) {
								if ($username !== $user->getUsername()) {
									$entityManager = $apiService->getEntityManager();

									// allow users to change username capitalization
									if (strtoupper($username) === strtoupper($user->getUsername()) || $entityManager->getRepository(User::class)->isUsernameAvailable($username)) {
										$user->setUsername($username)
											->setLastUsernameChange($now);

										$entityManager->persist($user);
										$entityManager->flush();

										return $apiService->json($apiService->serialize($user));
									} else {
										return $apiService->json(["error" => "That username is not available anymore."], 400);
									}
								} else {
									return $apiService->json(["error" => "You already have this username."], 200);
								}
							} else {
								return $apiService->json(["error" => "You can only change your username every 30 days."], 200);
							}
						} else {
							return $apiService->json(["error" => "The username cannot be longer than 16 characters."], 400);
						}
					} else {
						return $apiService->json(["error" => "The username has to be at least 3 characters long."], 400);
					}
				} else {
					return $apiService->json(["error" => "The username has to be alphanumeric."], 400);
				}
			} else {
				return $apiService->json(["error" => "'username' has to be a string."], 400);
			}
		} else {
			return $apiService->json(["error" => "'username' is required."], 400);
		}
	}
}
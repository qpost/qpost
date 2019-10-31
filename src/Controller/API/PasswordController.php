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

use qpost\Service\APIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function is_null;
use function is_string;
use function password_hash;
use function password_verify;
use const PASSWORD_BCRYPT;

class PasswordController extends AbstractController {
	/**
	 * @Route("/api/password", methods={"POST"})
	 *
	 * @param APIService $apiService
	 * @return Response
	 */
	public function change(APIService $apiService): Response {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$user = $apiService->getUser();
		$parameters = $apiService->parameters();

		if ($parameters->has("oldPassword")) {
			$oldPassword = $parameters->get("oldPassword");

			if (is_string($oldPassword)) {
				if ($parameters->has("newPassword")) {
					$newPassword = $parameters->get("newPassword");

					if (is_string($newPassword)) {
						if ($parameters->has("newPassword2")) {
							$newPassword2 = $parameters->get("newPassword2");

							if (is_string($newPassword2)) {
								if ($newPassword === $newPassword2) {
									if (!$user->getGigadriveData() && $user->getPassword()) {
										if (password_verify($oldPassword, $user->getPassword())) {
											$entityManager = $apiService->getEntityManager();

											$newHash = password_hash($newPassword, PASSWORD_BCRYPT);

											$user->setPassword($newHash);

											$entityManager->persist($user);
											$entityManager->flush();

											return $apiService->json(["result" => $apiService->serialize($user)]);
										} else {
											return $apiService->json(["error" => "Incorrect password."], 403);
										}
									} else {
										return $apiService->json(["error" => "You can not change your password."], 403);
									}
								} else {
									return $apiService->json(["error" => "The passwords don't match."], 400);
								}
							} else {
								return $apiService->json(["error" => "'newPassword2' has to be a string."], 400);
							}
						} else {
							return $apiService->json(["error" => "'newPassword2' is required."], 400);
						}
					} else {
						return $apiService->json(["error" => "'newPassword' has to be a string."], 400);
					}
				} else {
					return $apiService->json(["error" => "'newPassword' is required."], 400);
				}
			} else {
				return $apiService->json(["error" => "'oldPassword' has to be a string."], 400);
			}
		} else {
			return $apiService->json(["error" => "'oldPassword' is required."], 400);
		}
	}
}
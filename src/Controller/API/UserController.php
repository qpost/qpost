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

use qpost\Entity\User;
use qpost\Service\APIService;
use qpost\Service\DataDeletionService;
use qpost\Service\GigadriveService;
use qpost\Util\Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function count;
use function is_null;
use function is_string;
use function password_verify;

class UserController extends AbstractController {
	/**
	 * @Route("/api/user", methods={"GET"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function info(APIService $apiService) {
		$response = $apiService->validate(false);
		if (!is_null($response)) return $response;

		$parameters = $apiService->parameters();

		if ($parameters->has("user")) {
			$username = $parameters->get("user");

			if (!Util::isEmpty($username)) {
				$user = $apiService->getEntityManager()->getRepository(User::class)->getUserByUsername($username);

				if (!is_null($user) && $apiService->mayView($user)) {
					return $apiService->json(["result" => $apiService->serialize($user)]);
				} else {
					return $apiService->json(["error" => "Unknown user"], 404);
				}
			} else {
				return $apiService->json(["error" => "Unknown user"], 404);
			}
		} else {
			return $apiService->json(["error" => "Unknown user"], 404);
		}
	}

	/**
	 * @Route("/api/user", methods={"DELETE"})
	 *
	 * @param APIService $apiService
	 * @param GigadriveService $gigadriveService
	 * @param DataDeletionService $dataDeletionService
	 * @return Response|null
	 */
	public function delete(APIService $apiService, GigadriveService $gigadriveService, DataDeletionService $dataDeletionService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$user = $apiService->getUser();
		$parameters = $apiService->parameters();

		if ($parameters->has("password")) {
			$password = $parameters->get("password");

			if (is_string("password")) {
				$gigadriveData = $user->getGigadriveData();

				$correctPassword = $gigadriveData ? $gigadriveService->verifyPassword($gigadriveData->getAccountId(), $password) : password_verify($password, $user->getPassword());

				if ($correctPassword) {
					$dataDeletionService->deleteUser($user);

					return $apiService->noContent();
				} else {
					return $apiService->json(["error" => "Invalid password."], 400);
				}
			} else {
				return $apiService->json(["error" => "'password' has to be a string."], 400);
			}
		} else {
			return $apiService->json(["error" => "'password' is required."], 400);
		}
	}

	/**
	 * @Route("/api/user/suggested", methods={"GET"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function suggested(APIService $apiService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$token = $apiService->getToken();
		$user = $apiService->getUser();

		/**
		 * @var User[] $suggestedUsers
		 */
		$suggestedUsers = $apiService->getEntityManager()->getRepository(User::class)->getSuggestedUsers($user);

		$results = [];
		for ($i = 0; $i < count($suggestedUsers); $i++) {
			if (count($results) === 5) break;

			$user = $suggestedUsers[$i];
			if (!$apiService->mayView($user)) continue;
			/*if (!$user->mayView($currentUser)) {
				unset($suggestedUsers[$i]);
			}*/
			array_push($results, $apiService->serialize($user));
		}

		return $apiService->json(["results" => $results]);
	}
}

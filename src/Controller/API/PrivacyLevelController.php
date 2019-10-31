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

use qpost\Constants\PrivacyLevel;
use qpost\Service\APIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function is_null;
use function is_string;
use function strtoupper;

class PrivacyLevelController extends AbstractController {
	/**
	 * @Route("/api/privacyLevel", methods={"POST"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function change(APIService $apiService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$entityManager = $apiService->getEntityManager();
		$user = $apiService->getUser();

		$parameters = $apiService->parameters();

		if ($parameters->has("level")) {
			$level = $parameters->get("level");

			if (is_string($level) && ($level = strtoupper($level)) && PrivacyLevel::isValid($level)) {
				$user->setPrivacyLevel($level);

				$entityManager->persist($user);
				$entityManager->flush();

				return $apiService->json([
					"result" => $apiService->serialize($user)
				]);
			} else {
				return $apiService->json(["error" => "'level' has to be either 'PUBLIC', 'PRIVATE' or 'CLOSED'."], 400);
			}
		} else {
			return $apiService->json(["error" => "'level' is required."], 400);
		}
	}
}
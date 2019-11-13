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

use qpost\Entity\TrendingHashtagData;
use qpost\Service\APIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function intval;
use function is_null;
use function is_numeric;

class TrendsController extends AbstractController {
	/**
	 * @Route("/api/trends", methods={"GET"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function info(APIService $apiService) {
		$response = $apiService->validate(false);
		if (!is_null($response)) return $response;

		$parameters = $apiService->parameters();

		$limit = 10;
		if ($parameters->has("limit")) {
			$limit = $parameters->get("limit");

			if (!is_numeric($limit)) {
				return $apiService->json(["error" => "'limit' has to be an integer."], 400);
			} else {
				$limit = intval($limit);
			}

			if (!($limit >= 1 && $limit <= 20)) {
				return $apiService->json(["error" => "'limit' has to be between 1 and 20."], 400);
			}
		}

		$results = [];

		foreach ($apiService->getEntityManager()->getRepository(TrendingHashtagData::class)->getTrends($limit) as $trend) {
			$results[] = $apiService->serialize($trend);
		}

		return $apiService->json(["results" => $results], 200);
	}
}
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

use qpost\Constants\PrivacyLevel;
use qpost\Entity\FeedEntry;
use qpost\Entity\User;
use qpost\Service\APIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function array_push;
use function is_null;
use function is_numeric;

class FeedController extends AbstractController {
	/**
	 * @Route("/api/feed", methods={"GET"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function feed(APIService $apiService) {
		$entityManager = $apiService->getEntityManager();
		$entryRepository = $entityManager->getRepository(FeedEntry::class);
		$userRepository = $entityManager->getRepository(User::class);

		$parameters = $apiService->parameters();
		$apiService->getLogger()->info("param", ["param" => $parameters]);

		$user = $apiService->getUser();
		$target = null;
		$max = null;
		$min = null;

		// verify target
		if ($parameters->has("user")) {
			$target = $userRepository->getUserById($parameters->get("user"));

			if (is_null($target) || !$apiService->mayView($target)) {
				return $apiService->json(["error" => "The requested user could not be found."], 404);
			} else if (!$this->privacyLevelCheck($apiService, $user, $target)) {
				return $apiService->json(["error" => "You are not allowed to view this resource."], 403);
			}
		}

		// verify authorization
		if (is_null($target)) {
			$response = $apiService->validate(true);
			if (!is_null($response)) return $response;
		}

		// verify max entry id
		if ($parameters->has("max")) {
			$max = $parameters->get("max");

			if (!is_numeric($max)) {
				return $apiService->json(["error" => "'max' has to be an integer."], 400);
			}
		}

		// verify min entry id
		if ($parameters->has("min")) {
			if (!is_null($max)) {
				return $apiService->json(["error" => "'min' and 'max' may not be used together."], 400);
			}

			$min = $parameters->get("min");

			if (!is_numeric($min)) {
				return $apiService->json(["error" => "'min' has to be an integer."], 400);
			}
		}

		$results = [];

		/**
		 * @var FeedEntry[] $feedEntries
		 */
		$feedEntries = $entryRepository->getFeed($user, $target, $min, $max);

		foreach ($feedEntries as $feedEntry) {
			if (!$apiService->mayView($feedEntry)) continue;
			array_push($results, $apiService->serialize($feedEntry));
		}

		return $apiService->json(["results" => $results]);
	}

	private function privacyLevelCheck(APIService $apiService, ?User $from, User $to): bool {
		if ($to->getPrivacyLevel() === PrivacyLevel::PRIVATE) {
			if ($from) {
				return $from->getId() === $to->getId() || $apiService->isFollowing($from, $to);
			}

			return false;
		}

		return true;
	}
}

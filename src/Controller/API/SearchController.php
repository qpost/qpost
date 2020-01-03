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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use qpost\Constants\MiscConstants;
use qpost\Entity\FeedEntry;
use qpost\Entity\User;
use qpost\Service\APIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function addcslashes;
use function is_int;
use function is_null;
use function is_numeric;
use function is_string;
use function strlen;

class SearchController extends AbstractController {
	/**
	 * @Route("/api/search", methods={"GET"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function search(APIService $apiService) {
		$response = $apiService->validate(false);
		if (!is_null($response)) return $response;

		$parameters = $apiService->parameters();

		if ($parameters->has("type")) {
			$type = $parameters->get("type");

			if (is_string($type)) {
				if ($type === "user" || $type === "post") {
					if ($parameters->has("query")) {
						$query = $parameters->get("query");

						if (is_string($query)) {
							if (strlen($query) >= 3 && strlen($query) <= 56) {
								$offset = 0;
								if ($parameters->has("offset")) {
									$offset = $parameters->get("offset");

									if (is_numeric($offset) && ($offset = (int)$offset) && is_int($offset)) {
										if ($offset < 0) {
											return $apiService->json(["error" => "'offset' has to be at least 0."], 400);
										}
									} else {
										return $apiService->json(["error" => "'offset' has to be an integer."], 400);
									}
								}

								$limit = 15;
								if ($parameters->has("limit")) {
									$limit = $parameters->get("limit");

									if (is_numeric($limit) && ($limit = (int)$limit) && is_int($limit)) {
										if ($limit < 0 || $limit > 30) {
											return $apiService->json(["error" => "'limit' has to be between 1 and 30."], 400);
										}
									} else {
										return $apiService->json(["error" => "'limit' has to be an integer."], 400);
									}
								}

								$entityManager = $apiService->getEntityManager();

								/**
								 * @var QueryBuilder $databaseQuery
								 */
								$databaseQuery = $type === "user" ? $this->userQuery($entityManager, $query) : $this->postQuery($entityManager, $query);

								/**
								 * @var $result FeedEntry[]|User[]
								 */
								$result = $databaseQuery->setFirstResult($offset)
									->setMaxResults($limit)
									->getQuery()
									->useQueryCache(true)
									->setResultCacheLifetime(MiscConstants::RESULT_CACHE_LIFETIME)
									->useResultCache(true)
									->getResult();

								$apiService->getLogger()->info("result", [
									"result" => $result
								]);

								if (is_array($result)) {
									$results = [];

									foreach ($result as $entity) {
										if ($entity instanceof User || $entity instanceof FeedEntry) {
											if (!$apiService->mayView($entity)) continue;
										}

										$results[] = $apiService->serialize($entity);
									}

									return $apiService->json(["results" => $results]);
								} else {
									return $apiService->json(["error" => "An error occurred."], 500);
								}
							} else {
								return $apiService->json(["error" => "The query must be between 3 and 56 characters long."], 400);
							}
						} else {
							return $apiService->json(["error" => "'query' has to be a string."], 400);
						}
					} else {
						return $apiService->json(["error" => "'query' is required."], 400);
					}
				} else {
					return $apiService->json(["error" => "'type' has to be 'user' or 'post'."], 400);
				}
			} else {
				return $apiService->json(["error" => "'type' has to be a string."], 400);
			}
		} else {
			return $apiService->json(["error" => "'type' is required."], 400);
		}
	}

	private function userQuery(EntityManagerInterface $entityManager, string $query): QueryBuilder {
		return $entityManager->getRepository(User::class)->createQueryBuilder("u")
			->where("u.username LIKE :query")
			->orWhere("u.displayName LIKE :query")
			->orWhere("u.bio LIKE :query")
			->setParameter("query", $this->wrap($query));
	}

	private function wrap(string $query): string {
		// https://stackoverflow.com/a/48041835/4117923
		return "%" . addcslashes($query, "%_") . "%";
	}

	private function postQuery(EntityManagerInterface $entityManager, string $query): QueryBuilder {
		return $entityManager->getRepository(FeedEntry::class)->createQueryBuilder("f")
			->innerJoin("f.user", "u")
			->where("f.text IS NOT NULL AND f.text LIKE :query")
			->orWhere("u.username LIKE :query")
			->orWhere("u.displayName LIKE :query")
			->orWhere("u.bio LIKE :query")
			->setParameter("query", $this->wrap($query))
			->addOrderBy("f.time", "DESC");
	}
}
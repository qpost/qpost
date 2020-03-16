<?php
/**
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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use qpost\Constants\APIParameterType;
use qpost\Constants\MiscConstants;
use qpost\Entity\FeedEntry;
use qpost\Entity\User;
use qpost\Exception\GeneralErrorException;
use qpost\Exception\InvalidParameterIntegerRangeException;
use qpost\Exception\InvalidParameterStringLengthException;
use qpost\Exception\InvalidParameterTypeException;
use qpost\Exception\MissingParameterException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function addcslashes;
use function count;
use function is_array;

/**
 * @Route("/api")
 */
class SearchController extends APIController {
	/**
	 * @Route("/search", methods={"GET"})
	 *
	 * @return Response|null
	 * @throws GeneralErrorException
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws InvalidParameterStringLengthException
	 * @throws MissingParameterException
	 */
	public function search() {
		$this->validateParameterType("type", APIParameterType::STRING);
		$this->validateParameterType("query", APIParameterType::STRING);
		$this->validateParameterStringLength("query", 3, 56);

		$parameters = $this->parameters();
		$type = $parameters->get("type");
		$query = $parameters->get("query");

		if ($type !== "user" && $type !== "post") {
			return $this->error("'type' has to be 'user' or 'post'.", Response::HTTP_BAD_REQUEST);
		}

		$offset = $this->offset();
		$limit = $this->limit(15);

		/**
		 * @var QueryBuilder $databaseQuery
		 */
		$databaseQuery = $type === "user" ? $this->userQuery($this->entityManager, $query) : $this->postQuery($this->entityManager, $query);

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

		if (!is_array($result)) {
			throw new GeneralErrorException();
		}

		if (count($result) > 0) {
			if ($result[0] instanceof User) {
				$result = $this->filterUsers($result);
			} else if ($result[0] instanceof FeedEntry) {
				$result = $this->filterFeedEntries($result);
			}
		}

		return $this->response($result);
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
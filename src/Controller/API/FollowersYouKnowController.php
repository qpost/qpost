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

use qpost\Constants\MiscConstants;
use qpost\Entity\User;
use qpost\Exception\InvalidParameterIntegerRangeException;
use qpost\Exception\InvalidParameterTypeException;
use qpost\Exception\InvalidTokenException;
use qpost\Exception\MissingParameterException;
use qpost\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class FollowersYouKnowController extends APIController {
	/**
	 * @Route("/followersyouknow", methods={"GET"})
	 *
	 * @return Response|null
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws MissingParameterException
	 * @throws ResourceNotFoundException
	 * @throws InvalidTokenException
	 */
	public function followersYouKnow() {
		$this->validateAuth();
		$user = $this->getUser();
		$target = $this->user("target");
		$offset = $this->offset();
		$limit = $this->limit(30);

		return $this->response(
			$this->entityManager->getRepository(User::class)->createQueryBuilder("u")
				->innerJoin("u.following", "t")
				->innerJoin("u.followers", "f")
				->where("t.receiver = :target")
				->andWhere("f.sender = :user")
				->setParameter("target", $target)
				->setParameter("user", $user)
				->setFirstResult($offset)
				->setMaxResults($limit)
				->setCacheable(true)
				->getQuery()
				->useQueryCache(true)
				->setResultCacheLifetime(MiscConstants::RESULT_CACHE_LIFETIME)
				->useResultCache(true)
				->getResult()
		);
	}
}
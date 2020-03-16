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

use qpost\Constants\APIParameterType;
use qpost\Constants\PrivacyLevel;
use qpost\Entity\FeedEntry;
use qpost\Entity\User;
use qpost\Exception\AccessNotAllowedException;
use qpost\Exception\InvalidParameterIntegerRangeException;
use qpost\Exception\InvalidParameterTypeException;
use qpost\Exception\InvalidTokenException;
use qpost\Exception\MissingParameterException;
use qpost\Exception\ResourceNotFoundException;
use qpost\Service\APIService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function is_null;

/**
 * @Route("/api")
 */
class FeedController extends APIController {
	/**
	 * @Route("/feed", methods={"GET"})
	 *
	 * @return Response|null
	 * @throws AccessNotAllowedException
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws MissingParameterException
	 * @throws ResourceNotFoundException
	 * @throws InvalidTokenException
	 */
	public function feed() {
		$parameters = $this->parameters();
		$user = $this->getUser();

		$target = null;
		$max = $this->max();
		$min = $this->min();
		$type = "posts";

		// verify target
		if ($parameters->has("user")) {
			$target = $this->user("user");

			if (!$this->privacyLevelCheck($this->apiService, $user, $target)) {
				throw new AccessNotAllowedException();
			}
		}

		// verify authorization
		if (is_null($target)) {
			$this->validateAuth();
		}

		if (!is_null($max) && !is_null($min)) {
			return $this->error("'min' and 'max' may not be used together.", Response::HTTP_BAD_REQUEST);
		}

		$this->validateParameterType("type", APIParameterType::STRING, false);

		// verify type
		if ($parameters->has("type")) {
			$type = $parameters->get("type");

			if (!($type === "posts" || $type === "replies")) {
				return $this->error("'type' has to be either 'posts' or 'replies'.", Response::HTTP_BAD_REQUEST);
			}
		}

		return $this->response(
			$this->filterFeedEntries(
				$this->entityManager->getRepository(FeedEntry::class)->getFeed($user, $target, $min, $max, $type)
			)
		);
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

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
use qpost\Entity\User;
use qpost\Exception\InvalidParameterTypeException;
use qpost\Exception\InvalidTokenException;
use qpost\Exception\MissingParameterException;
use qpost\Exception\ResourceNotFoundException;
use qpost\Util\Util;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function array_slice;
use function is_null;

/**
 * @Route("/api")
 */
class UserController extends APIController {
	/**
	 * @Route("/user", methods={"GET"})
	 *
	 * @return Response|null
	 * @throws ResourceNotFoundException
	 * @throws InvalidParameterTypeException
	 * @throws MissingParameterException
	 */
	public function info() {
		$this->validateParameterType("user", APIParameterType::STRING);
		$parameters = $this->parameters();

		if ($parameters->has("user")) {
			$username = $parameters->get("user");

			if (!Util::isEmpty($username)) {
				$user = $this->entityManager->getRepository(User::class)
					->getUserByUsername($username);

				if (!is_null($user) && $this->apiService->mayView($user)) {
					return $this->response($user);
				}
			}
		}

		throw new ResourceNotFoundException();
	}

	/**
	 * @Route("/user/suggested", methods={"GET"})
	 *
	 * @return Response|null
	 * @throws InvalidTokenException
	 */
	public function suggested() {
		$this->validateAuth();

		return $this->response(
			array_slice(
				$this->filterUsers(
					$this->entityManager->getRepository(User::class)
						->getSuggestedUsers($this->getUser())
				),
				0, 5
			)
		);
	}
}

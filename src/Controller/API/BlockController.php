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

use DateTime;
use qpost\Entity\Block;
use qpost\Exception\InvalidParameterIntegerRangeException;
use qpost\Exception\InvalidParameterTypeException;
use qpost\Exception\InvalidTokenException;
use qpost\Exception\MissingParameterException;
use qpost\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function is_null;

/**
 * @Route("/api")
 */
class BlockController extends APIController {
	/**
	 * @Route("/block", methods={"POST"})
	 *
	 * @return Response|null
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws MissingParameterException
	 * @throws ResourceNotFoundException
	 * @throws InvalidTokenException
	 */
	public function block() {
		$this->validateAuth();
		$user = $this->getUser();
		$target = $this->user("target");

		if ($user->getId() === $target->getId()) {
			return $this->error("You may not block yourself.", Response::HTTP_BAD_REQUEST);
		}

		$block = $this->entityManager->getRepository(Block::class)->findOneBy([
			"user" => $user,
			"target" => $target
		]);

		if (!is_null($block)) {
			return $this->error("You have already blocked this user.", Response::HTTP_CONFLICT);
		}

		$this->apiService->unfollow($user, $target);
		$this->apiService->unfollow($target, $user);

		$block = (new Block())
			->setUser($user)
			->setTarget($target)
			->setTime(new DateTime("now"));

		$this->entityManager->persist($block);
		$this->entityManager->flush();

		return $this->response($block);
	}

	/**
	 * @Route("/block", methods={"DELETE"})
	 *
	 * @return Response|null
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws ResourceNotFoundException
	 * @throws MissingParameterException
	 * @throws InvalidTokenException
	 */
	public function unblock() {
		$this->validateAuth();
		$user = $this->getUser();
		$target = $this->user("target");

		$block = $this->entityManager->getRepository(Block::class)->getBlock($user, $target);

		if (is_null($block)) {
			throw new ResourceNotFoundException();
		}

		$this->entityManager->remove($block);
		$this->entityManager->flush();

		return $this->response();
	}

	/**
	 * @Route("/block", methods={"GET"})
	 *
	 * @return Response|null
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws MissingParameterException
	 * @throws ResourceNotFoundException
	 * @throws InvalidTokenException
	 */
	public function info() {
		$this->validateAuth();
		$user = $this->getUser();
		$target = $this->user("target");

		$block = $this->entityManager->getRepository(Block::class)->getBlock($user, $target);

		if (is_null($block)) {
			throw new ResourceNotFoundException();
		}

		return $this->response($block);
	}

	/**
	 * @Route("/blocks", methods={"GET"})
	 *
	 * @return Response|null
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws MissingParameterException
	 * @throws InvalidTokenException
	 */
	public function blocks() {
		$this->validateAuth();

		return $this->response(
			$this->entityManager->getRepository(Block::class)->getBlocks($this->getUser(), $this->max())
		);
	}
}
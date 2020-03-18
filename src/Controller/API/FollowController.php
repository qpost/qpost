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

use Doctrine\DBAL\Types\Type;
use qpost\Constants\FollowStatus;
use qpost\Constants\PrivacyLevel;
use qpost\Entity\Follower;
use qpost\Entity\FollowRequest;
use qpost\Entity\Notification;
use qpost\Exception\GeneralErrorException;
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
class FollowController extends APIController {
	/**
	 * @Route("/follow", methods={"GET"})
	 *
	 * @return Response|null
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws MissingParameterException
	 * @throws ResourceNotFoundException
	 */
	public function info() {
		$from = $this->user("from");
		$to = $this->user("to");

		/**
		 * @var Follower $follower
		 */
		$follower = $this->entityManager->getRepository(Follower::class)->findOneBy([
			"sender" => $from,
			"receiver" => $to
		]);

		if (!is_null($follower)) {
			return $this->response($follower);
		} else {
			if ($to->getPrivacyLevel() === PrivacyLevel::PRIVATE && $this->entityManager->getRepository(FollowRequest::class)->hasSentFollowRequest($from, $to)) {
				return $this->apiService->json(["status" => FollowStatus::PENDING]);
			}

			throw new ResourceNotFoundException();
		}
	}

	/**
	 * @Route("/follow", methods={"POST"})
	 *
	 * @return Response|null
	 * @throws GeneralErrorException
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws MissingParameterException
	 * @throws ResourceNotFoundException
	 * @throws InvalidTokenException
	 */
	public function follow() {
		$this->validateAuth();
		$user = $this->getUser();
		$to = $this->user("to");

		if ($user->getId() === $to->getId()) {
			return $this->error("You can not follow yourself.", Response::HTTP_CONFLICT);
		}

		$followerRepository = $this->entityManager->getRepository(Follower::class);
		$followRequestRepository = $this->entityManager->getRepository(FollowRequest::class);

		if (!$followerRepository->isFollowing($user, $to)) {
			if ($to->getPrivacyLevel() === PrivacyLevel::PUBLIC) {
				if ($this->apiService->follow($user, $to)) {
					return $this->apiService->json(["status" => FollowStatus::FOLLOWING]);
				} else {
					throw new GeneralErrorException();
				}
			} else if ($to->getPrivacyLevel() === PrivacyLevel::PRIVATE) {
				if (!$followRequestRepository->hasSentFollowRequest($user, $to)) {
					if ($this->apiService->follow($user, $to)) {
						return $this->apiService->json(["status" => FollowStatus::PENDING]);
					} else {
						throw new GeneralErrorException();
					}
				} else {
					return $this->error("You have already sent a request to this user.", Response::HTTP_CONFLICT);
				}
			} else {
				// should not happen
				throw new GeneralErrorException();
			}
		} else {
			return $this->error("You are already following this user.", Response::HTTP_CONFLICT);
		}
	}

	/**
	 * @Route("/follow", methods={"DELETE"})
	 *
	 * @return Response|null
	 * @throws GeneralErrorException
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws MissingParameterException
	 * @throws ResourceNotFoundException
	 * @throws InvalidTokenException
	 */
	public function unfollow() {
		$this->validateAuth();

		$user = $this->getUser();
		$to = $this->user("to");

		$followerRepository = $this->entityManager->getRepository(Follower::class);
		$followRequestRepository = $this->entityManager->getRepository(FollowRequest::class);

		if ($followerRepository->isFollowing($user, $to)) {
			if ($this->apiService->unfollow($user, $to)) { // TODO
				return $this->apiService->json(["status" => FollowStatus::NOT_FOLLOWING]);
			} else {
				throw new GeneralErrorException();
			}
		} else if ($to->getPrivacyLevel() === PrivacyLevel::PRIVATE) {
			if ($followRequestRepository->hasSentFollowRequest($user, $to)) {
				/**
				 * @var FollowRequest $followRequest
				 */
				$followRequest = $followRequestRepository->findOneBy([
					"sender" => $user,
					"receiver" => $to
				]);

				if (!is_null($followRequest)) {
					$notification = $this->entityManager->getRepository(Notification::class)->findOneBy([
						"referencedFollowRequest" => $followRequest
					]);

					if (!is_null($notification)) {
						$this->entityManager->remove($notification);
					}

					$this->entityManager->remove($followRequest);
					$this->entityManager->flush();

					return $this->apiService->json(["status" => FollowStatus::NOT_FOLLOWING]);
				} else {
					throw new GeneralErrorException();
				}
			} else {
				throw new ResourceNotFoundException();
			}
		} else {
			throw new ResourceNotFoundException();
		}
	}

	/**
	 * @Route("/follows", methods={"GET"})
	 *
	 * @return Response|null
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws MissingParameterException
	 * @throws ResourceNotFoundException
	 */
	public function follows() {
		$parameters = $this->parameters();

		if ($parameters->has("from") || $parameters->has("to")) {
			$max = $this->max();

			$user = null;

			$builder = $this->entityManager->getRepository(Follower::class)
				->createQueryBuilder("f");

			if ($parameters->has("from")) {
				$user = $this->user("from");

				$builder->where("f.sender = :user")
					->setParameter("user", $user);
			} else if ($parameters->has("to")) {
				$user = $this->user("to");

				$builder->where("f.receiver = :user")
					->setParameter("user", $user);
			}

			if ($max) {
				$builder->andWhere("f.id < :id")
					->setParameter("id", $max, Type::INTEGER);
			}

			return $this->response(
				$this->filterFollowers(
					$builder->orderBy("f.time", "DESC")
						->setMaxResults(30)
						->getQuery()
						->useQueryCache(true)
						->getResult()
				)
			);
		} else {
			return $this->error("'from' or 'to' are required.", Response::HTTP_BAD_REQUEST);
		}
	}
}

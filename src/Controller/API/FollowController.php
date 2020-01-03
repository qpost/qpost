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

use Doctrine\DBAL\Types\Type;
use qpost\Constants\FollowStatus;
use qpost\Constants\PrivacyLevel;
use qpost\Entity\Follower;
use qpost\Entity\FollowRequest;
use qpost\Entity\User;
use qpost\Repository\FollowerRepository;
use qpost\Repository\FollowRequestRepository;
use qpost\Repository\UserRepository;
use qpost\Service\APIService;
use qpost\Util\Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function array_push;
use function is_null;
use function is_numeric;

class FollowController extends AbstractController {
	/**
	 * @Route("/api/follow", methods={"GET"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function info(APIService $apiService) {
		$response = $apiService->validate(false);
		if (!is_null($response)) return $response;

		$parameters = $apiService->parameters();

		if ($parameters->has("from")) {
			$fromId = $parameters->get("from");

			if (!Util::isEmpty($fromId)) {
				if (is_numeric($fromId)) {
					$entityManager = $apiService->getEntityManager();

					/**
					 * @var UserRepository $userRepository
					 */
					$userRepository = $entityManager->getRepository(User::class);
					$from = $userRepository->getUserById($fromId);

					if (!is_null($from) && $apiService->mayView($from)) {
						if ($parameters->has("to")) {
							$toId = $parameters->get("to");

							if (!Util::isEmpty($toId)) {
								if (is_numeric($toId)) {
									/**
									 * @var UserRepository $userRepository
									 */
									$to = $userRepository->getUserById($toId);

									if (!is_null($to) && $apiService->mayView($to)) {
										/**
										 * @var Follower $follower
										 */
										$follower = $entityManager->getRepository(Follower::class)->findOneBy([
											"sender" => $from,
											"receiver" => $to
										]);

										if (!is_null($follower)) {
											return $apiService->json($apiService->serialize($follower));
										} else {
											return $to->getPrivacyLevel() === PrivacyLevel::PRIVATE &&
											$entityManager->getRepository(FollowRequest::class)->hasSentFollowRequest($from, $to) ?
												$apiService->json(["status" => FollowStatus::PENDING]) :
												$apiService->json(["error" => "The requested resource could not be found."], 404);
										}
									} else {
										return $apiService->json(["error" => "The requested user could not be found."], 404);
									}
								} else {
									return $apiService->json(["error" => "'to' has to be an integer."], 400);
								}
							} else {
								return $apiService->json(["error" => "'to' is required."], 400);
							}
						} else {
							return $apiService->json(["error" => "'to' is required."], 400);
						}
					} else {
						return $apiService->json(["error" => "The requested user could not be found."], 404);
					}
				} else {
					return $apiService->json(["error" => "'from' has to be an integer."], 400);
				}
			} else {
				return $apiService->json(["error" => "'from' is required."], 400);
			}
		} else {
			return $apiService->json(["error" => "'from' is required."], 400);
		}
	}

	/**
	 * @Route("/api/follow", methods={"POST"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function follow(APIService $apiService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$user = $apiService->getUser();
		$parameters = $apiService->parameters();

		if ($parameters->has("to")) {
			$toId = $parameters->get("to");

			if (!Util::isEmpty($toId)) {
				if (is_numeric($toId)) {
					$entityManager = $apiService->getEntityManager();

					/**
					 * @var UserRepository $userRepository
					 */
					$userRepository = $entityManager->getRepository(User::class);

					/**
					 * @var UserRepository $userRepository
					 */
					$to = $userRepository->getUserById($toId);

					if (!is_null($to) && $to->getPrivacyLevel() !== PrivacyLevel::CLOSED && $apiService->mayView($to)) {
						/**
						 * @var FollowerRepository $followerRepository
						 */
						$followerRepository = $entityManager->getRepository(Follower::class);

						/**
						 * @var FollowRequestRepository $followRequestRepository
						 */
						$followRequestRepository = $entityManager->getRepository(FollowRequest::class);

						if (!$followerRepository->isFollowing($user, $to)) {
							if ($to->getPrivacyLevel() === PrivacyLevel::PUBLIC) {
								if ($apiService->follow($user, $to)) {
									return $apiService->json(["status" => FollowStatus::FOLLOWING]);
								} else {
									return $apiService->json(["error" => "An error occurred."], 500);
								}
							} else if ($to->getPrivacyLevel() === PrivacyLevel::PRIVATE) {
								if (!$followRequestRepository->hasSentFollowRequest($user, $to)) {
									if ($apiService->follow($user, $to)) {
										return $apiService->json(["status" => FollowStatus::PENDING]);
									} else {
										return $apiService->json(["error" => "An error occurred."], 500);
									}
								} else {
									return $apiService->json(["error" => "You have already sent a request to this user."], 400);
								}
							} else {
								// should not happen
								return $apiService->json(["error" => "An error occurred."], 500);
							}
						} else {
							return $apiService->json(["error" => "You are already following this user."], 409);
						}
					} else {
						return $apiService->json(["error" => "The requested user could not be found."], 404);
					}
				} else {
					return $apiService->json(["error" => "'to' has to be an integer."], 400);
				}
			} else {
				return $apiService->json(["error" => "'to' is required."], 400);
			}
		} else {
			return $apiService->json(["error" => "'to' is required."], 400);
		}
	}

	/**
	 * @Route("/api/follow", methods={"DELETE"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function unfollow(APIService $apiService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$user = $apiService->getUser();
		$parameters = $apiService->parameters();

		if ($parameters->has("to")) {
			$toId = $parameters->get("to");

			if (!Util::isEmpty($toId)) {
				if (is_numeric($toId)) {
					$entityManager = $apiService->getEntityManager();

					/**
					 * @var UserRepository $userRepository
					 */
					$userRepository = $entityManager->getRepository(User::class);

					/**
					 * @var UserRepository $userRepository
					 */
					$to = $userRepository->getUserById($toId);

					if (!is_null($to) && $to->getPrivacyLevel() !== PrivacyLevel::CLOSED && $apiService->mayView($to)) {
						$entityManager = $apiService->getEntityManager();

						/**
						 * @var FollowerRepository $followerRepository
						 */
						$followerRepository = $entityManager->getRepository(Follower::class);

						/**
						 * @var FollowRequestRepository $followRequestRepository
						 */
						$followRequestRepository = $entityManager->getRepository(FollowRequest::class);

						if ($followerRepository->isFollowing($user, $to)) {
							if ($apiService->unfollow($user, $to)) { // TODO
								return $apiService->json(["status" => FollowStatus::NOT_FOLLOWING]);
							} else {
								return $apiService->json(["error" => "An error occurred."], 500);
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
									$entityManager->remove($followRequest);
									$entityManager->flush();

									return $apiService->json(["status" => FollowStatus::NOT_FOLLOWING]);
								} else {
									return $apiService->json(["error" => "An error occurred."], 500);
								}
							} else {
								return $apiService->json(["error" => "The requested resource could not be found."], 404);
							}
						} else {
							return $apiService->json(["error" => "The requested resource could not be found."], 404);
						}
					} else {
						return $apiService->json(["error" => "The requested user could not be found."], 404);
					}
				} else {
					return $apiService->json(["error" => "'to' has to be an integer."], 400);
				}
			} else {
				return $apiService->json(["error" => "'to' is required."], 400);
			}
		} else {
			return $apiService->json(["error" => "'to' is required."], 400);
		}
	}

	/**
	 * @Route("/api/follows", methods={"GET"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function follows(APIService $apiService) {
		$response = $apiService->validate(false);
		if (!is_null($response)) return $response;

		$parameters = $apiService->parameters();

		if ($parameters->has("from") || $parameters->has("to")) {
			$max = null;
			if ($parameters->has("max")) {
				$max = $parameters->get("max");
				if (!is_numeric($max)) {
					return $apiService->json(["error" => "'max' has to be an integer."], 400);
				}
			}

			$entityManager = $apiService->getEntityManager();
			$user = null;

			$builder = $entityManager->getRepository(Follower::class)->createQueryBuilder("f");

			if ($parameters->has("from")) {
				$user = $entityManager->getRepository(User::class)->findOneBy([
					"id" => $parameters->get("from")
				]);

				if ($user && $apiService->mayView($user)) {
					$builder->where("f.sender = :user")
						->setParameter("user", $user);
				} else {
					return $apiService->json(["error" => "The requested resource could not be found."], 404);
				}
			} else if ($parameters->has("to")) {
				$user = $entityManager->getRepository(User::class)->findOneBy([
					"id" => $parameters->get("to")
				]);

				if ($user && $apiService->mayView($user)) {
					$builder->where("f.receiver = :user")
						->setParameter("user", $user);
				} else {
					return $apiService->json(["error" => "The requested resource could not be found."], 404);
				}
			}

			if ($max) {
				$builder->andWhere("f.id < :id")
					->setParameter("id", $max, Type::INTEGER);
			}

			$results = [];

			/**
			 * @var Follower[] $followers
			 */
			$followers = $builder->orderBy("f.time", "DESC")
				->getQuery()
				->useQueryCache(true)
				->getResult();

			foreach ($followers as $follower) {
				if (!$apiService->mayView($parameters->has("from") ? $follower->getReceiver() : $follower->getSender())) continue;
				array_push($results, $apiService->serialize($follower));
			}

			return $apiService->json(["results" => $results]);
		} else {
			return $apiService->json(["error" => "'from' or 'to' are required."], 400);
		}
	}
}

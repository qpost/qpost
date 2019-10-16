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

use DateTime;
use Doctrine\DBAL\Types\Type;
use Exception;
use Gumlet\ImageResize;
use qpost\Entity\User;
use qpost\Service\APIService;
use qpost\Service\GigadriveService;
use qpost\Util\Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function base64_decode;
use function dirname;
use function file_exists;
use function file_put_contents;
use function filesize;
use function getimagesize;
use function getrandmax;
use function is_null;
use function is_string;
use function mkdir;
use function rand;
use function strlen;
use function strtotime;
use function sys_get_temp_dir;

class UserController extends AbstractController {
	/**
	 * @Route("/api/user", methods={"GET"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function info(APIService $apiService) {
		$response = $apiService->validate(false);
		if (!is_null($response)) return $response;

		$parameters = $apiService->parameters();

		if ($parameters->has("user")) {
			$username = $parameters->get("user");

			if (!Util::isEmpty($username)) {
				$user = $apiService->getEntityManager()->getRepository(User::class)->getUserByUsername($username);

				if (!is_null($user)) { // TODO: Add mayView check
					return $apiService->json(["result" => $apiService->serialize($user)]);
				} else {
					return $apiService->json(["error" => "Unknown user"], 404);
				}
			} else {
				return $apiService->json(["error" => "Unknown user"], 404);
			}
		} else {
			return $apiService->json(["error" => "Unknown user"], 404);
		}
	}

	/**
	 * @Route("/api/user", methods={"POST"})
	 *
	 * @param APIService $apiService
	 * @param GigadriveService $gigadriveService
	 * @return Response|null
	 * @throws Exception
	 */
	public function edit(APIService $apiService, GigadriveService $gigadriveService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$parameters = $apiService->parameters();
		$user = $apiService->getUser();

		if ($parameters->has("displayName")) {
			$displayName = $parameters->get("displayName");

			if (!Util::isEmpty($displayName)) {
				if (strlen($displayName) >= 1 && strlen($displayName) <= 24) {
					if ($parameters->has("bio")) {
						$bio = $parameters->get("bio");

						if (is_null($bio) || (strlen($bio) >= 0 && strlen($bio) <= 200)) {
							if ($parameters->has("birthday")) {
								$birthday = $parameters->get("birthday");

								if (is_null($birthday) || strtotime($birthday)) {
									$user->setDisplayName($displayName)
										->setBio(Util::isEmpty($bio) ? null : $bio)
										->setBirthday(Util::isEmpty($birthday) ? null : new DateTime($birthday));

									if ($parameters->has("avatar")) {
										$base64 = $parameters->get("avatar");

										if (is_string($base64) && ($avatarFile = @base64_decode($base64))) {
											$path = null;
											while (is_null($path) || file_exists($path)) $path = sys_get_temp_dir() . "/qpost/avatar/" . rand(0, getrandmax()) . ".png";

											$directoryPath = dirname($path);
											if (!file_exists($directoryPath)) {
												mkdir($directoryPath, 0777, true);
											}

											file_put_contents($path, $avatarFile);

											if (!(@getimagesize($path))) {
												return $apiService->json(["error" => "'avatar' is not a valid image."], 400);
											}

											// Check if file is smaller than 2MB
											$fileSize = @filesize($path);
											if (!($fileSize) || !(($fileSize / 1024 / 1024) < 2)) {
												return $apiService->json(["error" => "'avatar' may not be bigger than 2MB."], 400);
											}

											try {
												$image = new ImageResize($path);

												$image->crop(300, 300, true);

												$avatarFile = $image->getImageAsString();

												$url = $gigadriveService->storeFileOnCDN($avatarFile);
												if (!is_null($url)) {
													$user->setAvatar($url);
												} else {
													return $apiService->json(["error" => "An error occurred."], 500);
												}
											} catch (Exception $e) {
												return $apiService->json(["error" => "'avatar' is not a valid image."], 400);
											}
										} else {
											if (is_null($base64)) {
												$user->setAvatar(null);
											} else {
												return $apiService->json(["error" => "'avatar' has to be a base64 string."], 400);
											}
										}
									}

									$entityManager = $apiService->getEntityManager();

									$entityManager->persist($user);
									$entityManager->flush();

									return $apiService->json(["result" => $apiService->serialize($user)]);
								} else {
									return $apiService->json(["error" => "The birthday must be a valid date."], 400);
								}
							} else {
								return $apiService->json(["error" => "'birthday' is required."], 400);
							}
						} else {
							return $apiService->json(["error" => "The bio must be between 0 and 200 characters long."], 400);
						}
					} else {
						return $apiService->json(["error" => "'bio' is required."], 400);
					}
				} else {
					return $apiService->json(["error" => "The display name must be between 1 and 24 characters long."], 400);
				}
			} else {
				return $apiService->json(["error" => "'displayName' is required."], 400);
			}
		} else {
			return $apiService->json(["error" => "'displayName' is required."], 400);
		}
	}

	/**
	 * @Route("/api/user/suggested", methods={"GET"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function suggested(APIService $apiService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$token = $apiService->getToken();
		$user = $apiService->getUser();

		// query is a combination of https://stackoverflow.com/a/12915720 and https://stackoverflow.com/a/24165699
		/**
		 * @var User[] $suggestedUsers
		 */
		$suggestedUsers = $apiService->getEntityManager()->getRepository(User::class)->createQueryBuilder("u")
			->innerJoin("u.followers", "t")
			->innerJoin("t.sender", "their_friends")
			->innerJoin("their_friends.followers", "m")
			->innerJoin("m.sender", "me")
			->where("u.id != :id")
			->setParameter("id", $user->getId(), Type::INTEGER)
			->andWhere("u.emailActivated = :activated")
			->setParameter("activated", true, Type::BOOLEAN)
			->andWhere("me.id = :id")
			->setParameter("id", $user->getId(), Type::INTEGER)
			->andWhere("their_friends.id != :id")
			->setParameter("id", $user->getId(), Type::INTEGER)
			->andWhere("not exists (select 1 from qpost\Entity\Follower f where f.sender = :id and f.receiver = t.receiver)")
			->setParameter("id", $user->getId(), Type::INTEGER)
			->groupBy("me.id, t.receiver")
			->setMaxResults(10)
			->getQuery()
			->getResult();

		$results = [];
		for ($i = 0; $i < count($suggestedUsers); $i++) {
			$user = $suggestedUsers[$i];
			// TODO: mayView check
			/*if (!$user->mayView($currentUser)) {
				unset($suggestedUsers[$i]);
			}*/
			array_push($results, $apiService->serialize($user));
		}

		return $apiService->json(["results" => $results]);
	}
}
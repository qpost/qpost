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

use DateTime;
use Exception;
use qpost\Entity\Block;
use qpost\Entity\User;
use qpost\Service\APIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function array_push;
use function is_null;
use function is_numeric;

class BlockController extends AbstractController {
	/**
	 * @Route("/api/block", methods={"POST"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 * @throws Exception
	 */
	public function block(APIService $apiService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$user = $apiService->getUser();
		$parameters = $apiService->parameters();

		if ($parameters->has("target")) {
			$entityManager = $apiService->getEntityManager();

			$target = $entityManager->getRepository(User::class)->getUserById($parameters->get("target"));

			if ($target && $apiService->mayView($target)) {
				$block = $entityManager->getRepository(Block::class)->findOneBy([
					"user" => $user,
					"target" => $target
				]);

				if (!$block) {
					$apiService->unfollow($user, $target);
					$apiService->unfollow($target, $user);

					$block = (new Block())
						->setUser($user)
						->setTarget($target)
						->setTime(new DateTime("now"));

					$entityManager->persist($block);
					$entityManager->flush();

					return $apiService->json(["result" => $apiService->serialize($block)]);
				} else {
					return $apiService->json(["error" => "You have already blocked this user."], 409);
				}
			} else {
				return $apiService->json(["error" => "The requested resource could not be found."], 404);
			}
		} else {
			return $apiService->json(["error" => "'target' is required."], 400);
		}
	}

	/**
	 * @Route("/api/block", methods={"DELETE"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function unblock(APIService $apiService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$user = $apiService->getUser();
		$parameters = $apiService->parameters();

		if ($parameters->has("target")) {
			$entityManager = $apiService->getEntityManager();

			$target = $entityManager->getRepository(User::class)->getUserById($parameters->get("target"));

			if ($target) {
				$block = $entityManager->getRepository(Block::class)->getBlock($user, $target);

				if ($block) {
					$entityManager->remove($block);
					$entityManager->flush();

					return $apiService->noContent();
				} else {
					return $apiService->json(["error" => "The requested resource could not be found."], 404);
				}
			} else {
				return $apiService->json(["error" => "The requested resource could not be found."], 404);
			}
		} else {
			return $apiService->json(["error" => "'target' is required."], 400);
		}
	}

	/**
	 * @Route("/api/block", methods={"GET"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function info(APIService $apiService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$user = $apiService->getUser();
		$parameters = $apiService->parameters();

		if ($parameters->has("target")) {
			$entityManager = $apiService->getEntityManager();

			$target = $entityManager->getRepository(User::class)->getUserById($parameters->get("target"));

			if ($target) {
				$block = $entityManager->getRepository(Block::class)->getBlock($user, $target);

				if ($block) {
					return $apiService->json(["result" => $apiService->serialize($block)]);
				} else {
					return $apiService->json(["error" => "The requested resource could not be found."], 404);
				}
			} else {
				return $apiService->json(["error" => "The requested resource could not be found."], 404);
			}
		} else {
			return $apiService->json(["error" => "'target' is required."], 400);
		}
	}

	/**
	 * @Route("/api/blocks", methods={"GET"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function blocks(APIService $apiService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$user = $apiService->getUser();
		$parameters = $apiService->parameters();

		$max = null;
		if ($parameters->has("max")) {
			$max = $parameters->get("max");
			if (!is_numeric($max)) {
				return $apiService->json(["error" => "'max' has to be an integer."], 400);
			}
		}

		$entityManager = $apiService->getEntityManager();
		$results = [];

		/**
		 * @var Block[] $blocks
		 */
		$blocks = $entityManager->getRepository(Block::class)->getBlocks($user, $max);

		foreach ($blocks as $block) {
			array_push($results, $apiService->serialize($block));
		}

		return $apiService->json(["results" => $results]);
	}
}
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

use Doctrine\DBAL\Types\Type;
use qpost\Entity\User;
use qpost\Service\APIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function is_null;

class UserController extends AbstractController {
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

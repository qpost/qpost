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

use DateInterval;
use DateTime;
use Doctrine\DBAL\Types\Type;
use Exception;
use qpost\Entity\User;
use qpost\Service\APIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function is_null;

class BirthdayController extends AbstractController {
	/**
	 * @Route("/api/birthdays", methods={"GET"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 * @throws Exception
	 */
	public function birthdays(APIService $apiService) {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$user = $apiService->getUser();
		$entityManager = $apiService->getEntityManager();

		$date = new DateTime("now"); // TODO

		$limit = new DateTime("now");
		$limit->add(DateInterval::createFromDateString("30 day"));

		$results = [];

		$users = $entityManager->getRepository(User::class)->createQueryBuilder("u")
			->where("u != :user")
			->innerJoin("u.followers", "f")
			->where("f.sender = :user")
			->setParameter("user", $user)
			->andWhere("u.birthday is not null")
			->andWhere("DAYOFYEAR(u.birthday) BETWEEN DAYOFYEAR(:date) AND DAYOFYEAR(:limit)")
			->setParameter("date", $date, Type::DATETIME)
			->setParameter("limit", $limit, Type::DATETIME)
			->setMaxResults(5)
			->setCacheable(true)
			->getQuery()
			->getResult();

		foreach ($users as $u) {
			if (!$apiService->mayView($u)) continue;
			$results[] = $apiService->serialize($u);
		}

		return $apiService->json(["results" => $results]);
	}
}
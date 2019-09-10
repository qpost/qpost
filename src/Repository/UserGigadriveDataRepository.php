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

namespace qpost\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use qpost\Cache\CacheHandler;
use qpost\Entity\UserGigadriveData;
use qpost\Factory\HttpClientFactory;
use qpost\Util\Util;
use function is_null;
use function json_decode;

/**
 * @method UserGigadriveData|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserGigadriveData|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserGigadriveData[]    findAll()
 * @method UserGigadriveData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserGigadriveDataRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, UserGigadriveData::class);
	}

	/**
	 * @param string $code
	 * @return string|null
	 */
	public function getGigadriveTokenFromCode(string $code): ?string {
		if (Util::isEmpty($code)) return null;

		$cacheName = "gigadriveToken_" . $code;
		if (CacheHandler::existsInCache($cacheName)) {
			return CacheHandler::getFromCache($cacheName);
		} else {
			$token = null;
			$client = HttpClientFactory::create();
			$secret = $_ENV["GIGADRIVE_APP_SECRET"];

			$response = $client->get("https://gigadrivegroup.com/api/v3/token", [
				"query" => [
					"secret" => $secret,
					"code" => $code
				]
			]);

			$body = $response->getBody();
			if (!is_null($body)) {
				$content = $body->getContents();
				$body->close();
				if (!is_null($content)) {
					$j = @json_decode($content, true);
					if ($j) {
						if (isset($j["success"]) && !empty($j["success"]) && isset($j["token"])) {
							$token = $j["token"];
							CacheHandler::setToCache($cacheName, $token, 3 * 60);
						}
					}
				}
			}

			return $token;
		}
	}

	public function getGigadriveUserData(string $token): ?array {
		if (Util::isEmpty($token)) return null;

		$cacheName = "gigadriveData_" . $token;
		if (CacheHandler::existsInCache($cacheName)) {
			return CacheHandler::getFromCache($cacheName);
		} else {
			$data = null;

			$client = HttpClientFactory::create();
			$secret = $_ENV["GIGADRIVE_APP_SECRET"];

			$response = $client->get("https://gigadrivegroup.com/api/v3/user", [
				"query" => [
					"secret" => $secret,
					"token" => $token
				]
			]);
			$body = $response->getBody();
			if (!is_null($body)) {
				$content = $body->getContents();
				$body->close();
				if (!is_null($content)) {
					$j = @json_decode($content, true);
					if ($j) {
						if (isset($j["success"]) && !empty($j["success"]) && isset($j["user"])) {
							$data = $j["user"];
							CacheHandler::setToCache($cacheName, $data, 3 * 60);
						}
					}
				}
			}

			return $data;
		}
	}

	// /**
	//  * @return UserGigadriveData[] Returns an array of UserGigadriveData objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('u')
			->andWhere('u.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('u.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/

	/*
	public function findOneBySomeField($value): ?UserGigadriveData
	{
		return $this->createQueryBuilder('u')
			->andWhere('u.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}

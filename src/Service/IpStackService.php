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

namespace qpost\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use qpost\Entity\IpStackResult;
use qpost\Entity\Token;
use qpost\Factory\HttpClientFactory;
use function is_null;
use function json_decode;

class IpStackService {
	/**
	 * @var LoggerInterface $logger
	 */
	private $logger;

	/**
	 * @var EntityManagerInterface $entityManager
	 */
	private $entityManager;

	/**
	 * @var string $apiKey
	 */
	private $apiKey;

	/**
	 * @var Client $httpClient
	 */
	private $httpClient;

	public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager) {
		$this->logger = $logger;
		$this->entityManager = $entityManager;
		$this->apiKey = $_ENV["IPSTACK_KEY"];
		$this->httpClient = HttpClientFactory::create();
	}

	/**
	 * @return LoggerInterface
	 */
	public function getLogger(): LoggerInterface {
		return $this->logger;
	}

	/**
	 * @return EntityManagerInterface
	 */
	public function getEntityManager(): EntityManagerInterface {
		return $this->entityManager;
	}

	/**
	 * @return string
	 */
	public function getAPIKey(): string {
		return $this->apiKey;
	}

	/**
	 * @return Client
	 */
	public function getClient(): Client {
		return $this->httpClient;
	}

	/**
	 * Creates an IpStackResult object to use from a token.
	 *
	 * @param Token $token
	 * @return IpStackResult|null
	 */
	public function createIpStackResult(Token $token): ?IpStackResult {
		$ip = $token->getLastIP();
		if (!$ip || ($ip === "127.0.0.1" || $ip === "127.0.1.1" || $ip === "::1" || $ip === "localhost")) { // Use example IP in dev environment
			$ip = "134.201.250.155";
		}

		$response = $this->httpClient->get("http://api.ipstack.com/" . $ip, [
			"query" => [
				"access_key" => $this->apiKey
			]
		]);

		$ipStackResult = null;

		$body = $response->getBody();
		if (!is_null($body)) {
			$content = $body->getContents();
			$body->close();
			if (!is_null($content)) {
				$data = @json_decode($content, true);
				if ($data) {
					$this->logger->info("Creating ip stack result", [
						"data" => $data
					]);

					$ipStackResult = new IpStackResult();

					if (isset($data["ip"])) $ipStackResult->setIp($data["ip"]);
					if (isset($data["type"])) $ipStackResult->setType($data["type"]);
					if (isset($data["continent_code"])) $ipStackResult->setContinentCode($data["continent_code"]);
					if (isset($data["continent_name"])) $ipStackResult->setContinentName($data["continent_name"]);
					if (isset($data["country_code"])) $ipStackResult->setCountryCode($data["country_code"]);
					if (isset($data["country_name"])) $ipStackResult->setCountryName($data["country_name"]);
					if (isset($data["region_code"])) $ipStackResult->setRegionCode($data["region_code"]);
					if (isset($data["region_name"])) $ipStackResult->setRegionName($data["region_name"]);
					if (isset($data["city"])) $ipStackResult->setCity($data["city"]);
					if (isset($data["zip"])) $ipStackResult->setZipCode($data["zip"]);
					if (isset($data["latitude"])) $ipStackResult->setLatitude($data["latitude"]);
					if (isset($data["longitude"])) $ipStackResult->setLongitude($data["longitude"]);

					$ipStackResult->setToken($token)
						->setTime(new DateTime("now"));
				}
			}
		}

		return $ipStackResult;
	}
}
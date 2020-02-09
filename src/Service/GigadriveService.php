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

namespace qpost\Service;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use qpost\Factory\HttpClientFactory;
use function base64_encode;
use function file_get_contents;
use function is_null;
use function json_decode;

class GigadriveService {
	/**
	 * @var LoggerInterface $logger
	 */
	private $logger;

	/**
	 * @var Client $httpClient
	 */
	private $httpClient;

	public function __construct(LoggerInterface $logger) {
		$this->logger = $logger;
		$this->httpClient = HttpClientFactory::create();
	}

	/**
	 * @return LoggerInterface
	 */
	public function getLogger(): LoggerInterface {
		return $this->logger;
	}

	/**
	 * @return Client
	 */
	public function getHTTPClient(): Client {
		return $this->httpClient;
	}

	/**
	 * Stores a file from a path on the Gigadrive CDN.
	 * @param string $path The path of the file to be stored.
	 * @return string|null The final URL on Gigadrive CDN, null if the file could not be stored.
	 * @see file_get_contents()
	 *
	 */
	public function storeFileFromSystemOnCDN(string $path): ?string {
		return $this->storeFileOnCDN(file_get_contents($path));
	}

	/**
	 * Stores a file on the Gigadrive CDN.
	 *
	 * @param string $fileContent The content of the file to be stored.
	 * @return string|null The final URL on the Gigadrive CDN, null if the file could not be stored.
	 */
	public function storeFileOnCDN(string $fileContent): ?string {
		$base64 = (base64_encode(base64_decode($fileContent)) === $fileContent) ? $fileContent : @base64_encode($fileContent);
		$apiKey = $_ENV["GIGADRIVE_APP_SECRET"];

		if ($base64) {
			$response = $this->httpClient->post("https://gigadrivegroup.com/api/v3/file", [
				"form_params" => [
					"secret" => $apiKey,
					"data" => $base64
				]
			]);

			$body = $response->getBody();
			if (!is_null($body)) {
				$content = $body->getContents();
				$body->close();

				if (!is_null($content)) {
					$data = @json_decode($content, true);
					if ($data) {
						if (isset($data["success"]) && isset($data["file"]) && isset($data["file"]["url"])) {
							return $data["file"]["url"];
						} else {
							$this->logger->error("Storage was not successful", [
								"data" => $data
							]);
						}
					} else {
						$this->logger->error("Response body is invalid json.");
					}
				} else {
					$this->logger->error("Response body is empty.");
				}
			} else {
				$this->logger->error("Failed to get response body.");
			}
		} else {
			$this->logger->error("Failed to generate base64.");
		}

		return null;
	}

	/**
	 * Contacts the Gigadrive legacy API to verify a username/password combination.
	 *
	 * @param string $username The username, email or ID may be used instead.
	 * @param string $password The password
	 * @return bool
	 */
	public function verifyPassword(string $username, string $password): bool {
		$apiKey = $_ENV["GIGADRIVE_LEGACY_API_KEY"];

		$response = $this->httpClient->get("https://api.gigadrivegroup.com/v1/login", [
			"query" => [
				"apiKey" => $apiKey,
				"username" => $username,
				"password" => $password
			]
		]);

		$body = $response->getBody();
		if (!is_null($body)) {
			$content = $body->getContents();
			$body->close();

			if (!is_null($content)) {
				$data = @json_decode($content, true);
				if ($data) {
					if (isset($data["success"]) && isset($data["username"])) {
						return true;
					} else {
						$this->logger->error("Failed to verify credentials", [
							"data" => $data
						]);
					}
				} else {
					$this->logger->error("Response body is invalid json.");
				}
			} else {
				$this->logger->error("Response body is empty.");
			}
		} else {
			$this->logger->error("Failed to get response body.");
		}

		return false;
	}
}
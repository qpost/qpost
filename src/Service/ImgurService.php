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

use Exception;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use qpost\Factory\HttpClientFactory;
use function is_null;
use function json_decode;

class ImgurService {
	/**
	 * @var LoggerInterface $logger
	 */
	private $logger;

	/**
	 * @var Client $httpClient
	 */
	private $httpClient;

	/**
	 * @var string $clientId
	 */
	private $clientId;

	/**
	 * @var string $clientSecret
	 */
	private $clientSecret;

	public function __construct(LoggerInterface $logger) {
		$this->logger = $logger;
		$this->httpClient = HttpClientFactory::create();
		$this->clientId = $_ENV["IMGUR_CLIENT_ID"];
		$this->clientSecret = $_ENV["IMGUR_CLIENT_SECRET"];
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
	 * @return string
	 */
	public function getClientId(): string {
		return $this->clientId;
	}

	/**
	 * @return string
	 */
	public function getClientSecret(): string {
		return $this->clientSecret;
	}

	/**
	 * Uploads an image to imgur.com
	 *
	 * @param string $data The binary file data or base64 string of the image
	 * @return string|null The final URL, null if it could not be uploaded.
	 */
	public function uploadImage(string $data): ?string {
		try {
			$response = $this->httpClient->post("https://api.imgur.com/3/image", [
				"form_params" => [
					"image" => $data
				],

				"headers" => [
					"Authorization" => "Client-ID " . $this->clientId
				]
			]);

			$body = $response->getBody();
			if (!is_null($body)) {
				$content = $body->getContents();
				$body->close();

				if (!is_null($content)) {
					$data = @json_decode($content, true);
					if ($data) {
						if (isset($data["data"]) && isset($data["data"]["link"])) {
							return $data["data"]["link"];
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
		} catch (Exception $e) {
			$this->logger->error("An error occurred while uploading to imgur.", [
				"exception" => $e
			]);
			return null;
		}

		return null;
	}
}
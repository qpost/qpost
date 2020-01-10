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

use DateTime;
use Exception;
use Google\Cloud\Storage\Bucket;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Storage;
use Psr\Log\LoggerInterface;
use const DIRECTORY_SEPARATOR;

class FirebaseService {
	/**
	 * @var LoggerInterface $logger
	 */
	private $logger;

	/**
	 * @var string $serviceAccountFile
	 */
	private $serviceAccountFile;

	/**
	 * @var Factory $factory
	 */
	private $factory;

	/**
	 * @var Storage $storage
	 */
	private $storage;

	/**
	 * @var Bucket $bucket
	 */
	private $bucket;

	public function __construct(LoggerInterface $logger) {
		$this->logger = $logger;
		$this->serviceAccountFile = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . $_ENV["FIREBASE_SERVICE_ACCOUNT_CREDENTIALS"];

		$this->factory = (new Factory())
			->withServiceAccount($this->serviceAccountFile);

		$this->storage = $this->factory->createStorage();

		$this->bucket = $this->storage->getBucket($_ENV["FIREBASE_BUCKET_NAME"]);
	}

	public function uploadImage(string $data): ?string {
		try {
			$object = $this->bucket->upload($data, [
				"name" => $_ENV["APP_ENV"] . "-" . (new DateTime("now"))->format("Y-m-d-H-i-s") . ".png"
			]);

			if ($object) {
				$time = new DateTime("+50 years");

				return $object->signedUrl($time);
			}
		} catch (Exception $e) {
			$this->logger->error("An error occurred while uploading to Firebase.", [
				"exception" => $e
			]);

			return null;
		}

		return null;
	}

	/**
	 * @return LoggerInterface
	 */
	public function getLogger(): LoggerInterface {
		return $this->logger;
	}

	/**
	 * @return string
	 */
	public function getServiceAccountFile(): string {
		return $this->serviceAccountFile;
	}

	/**
	 * @return Factory
	 */
	public function getFactory(): Factory {
		return $this->factory;
	}
}
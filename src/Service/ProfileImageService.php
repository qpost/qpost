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
use Gumlet\ImageResize;
use Psr\Log\LoggerInterface;
use qpost\Exception\ProfileImageInvalidException;
use qpost\Exception\ProfileImageTooBigException;
use function base64_decode;
use function dirname;
use function file_exists;
use function file_put_contents;
use function filesize;
use function getimagesize;
use function getrandmax;
use function is_null;
use function mkdir;
use function rand;
use function sys_get_temp_dir;

class ProfileImageService {
	/**
	 * @var LoggerInterface $logger
	 */
	private $logger;

	/**
	 * @var StorageService $storageService
	 */
	private $storageService;

	public function __construct(LoggerInterface $logger, StorageService $storageService) {
		$this->logger = $logger;
		$this->storageService = $storageService;
	}

	/**
	 * @param string $base64 The uploaded file in base64
	 * @param int $maxFileSize The maximum allowed file size in MB
	 * @param int $width The desired image width
	 * @param int $height The desired image height
	 * @return string|null The final URL of the image, null if it could not be uploaded
	 * @throws ProfileImageInvalidException Thrown when the uploaded file is not a valid image
	 * @throws ProfileImageTooBigException Thrown when the uploaded file is too big
	 */
	public function upload(string $base64, int $maxFileSize, int $width, int $height): ?string {
		if (!($file = @base64_decode($base64))) return null;

		$path = null;
		while (is_null($path) || file_exists($path)) $path = sys_get_temp_dir() . "/qpost/tmp/" . rand(0, getrandmax()) . ".png";

		$directoryPath = dirname($path);
		if (!file_exists($directoryPath)) {
			mkdir($directoryPath, 0777, true);
		}

		file_put_contents($path, $file);

		if (!(@getimagesize($path))) {
			throw new ProfileImageInvalidException;
		}

		// Check if file is too big
		$fileSize = @filesize($path);
		if (!($fileSize) || !(($fileSize / 1024 / 1024) < $maxFileSize)) {
			throw new ProfileImageTooBigException($maxFileSize);
		}

		try {
			$image = new ImageResize($path);

			$image->crop($width, $height, true);

			$avatarFile = $image->getImageAsString();

			$url = $this->storageService->uploadImage($avatarFile);
			if (!is_null($url)) {
				return $url;
			} else {
				throw new Exception("An error occurred.");
			}
		} catch (Exception $e) {
			throw new ProfileImageInvalidException;
		}
	}

	/**
	 * @return LoggerInterface
	 */
	public function getLogger(): LoggerInterface {
		return $this->logger;
	}

	/**
	 * @return StorageService
	 */
	public function getStorageService(): StorageService {
		return $this->storageService;
	}
}
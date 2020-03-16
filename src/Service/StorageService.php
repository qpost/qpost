<?php
/**
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpostapp.com
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

use Psr\Log\LoggerInterface;

class StorageService {
	/**
	 * @var LoggerInterface $logger
	 */
	private $logger;

	/**
	 * @var ImgurService $imgurService
	 */
	private $imgurService;

	/**
	 * @var GigadriveService $gigadriveService
	 */
	private $gigadriveService;

	/**
	 * @var FirebaseService $firebaseService
	 */
	private $firebaseService;

	public function __construct(LoggerInterface $logger, ImgurService $imgurService, GigadriveService $gigadriveService, FirebaseService $firebaseService) {
		$this->logger = $logger;
		$this->imgurService = $imgurService;
		$this->gigadriveService = $gigadriveService;
		$this->firebaseService = $firebaseService;
	}

	/**
	 * Uploads an image file to a remote CDN.
	 *
	 * @param string $data The binary file data
	 * @return string|null The final URL, null if it could not be uploaded.
	 */
	public function uploadImage(string $data): ?string {
		$imgurURL = $this->imgurService->uploadImage($data);
		if ($imgurURL) return $imgurURL;

		$firebaseURL = $this->firebaseService->uploadImage($data);
		if ($firebaseURL) return $firebaseURL;

		$gigadriveURL = $this->gigadriveService->storeFileOnCDN($data);
		if ($gigadriveURL) return $gigadriveURL;

		return null;
	}

	/**
	 * @return LoggerInterface
	 */
	public function getLogger(): LoggerInterface {
		return $this->logger;
	}

	/**
	 * @return GigadriveService
	 */
	public function getGigadriveService(): GigadriveService {
		return $this->gigadriveService;
	}

	/**
	 * @return ImgurService
	 */
	public function getImgurService(): ImgurService {
		return $this->imgurService;
	}

	/**
	 * @return FirebaseService
	 */
	public function getFirebaseService(): FirebaseService {
		return $this->firebaseService;
	}
}
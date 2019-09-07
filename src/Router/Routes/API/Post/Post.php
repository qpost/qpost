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

namespace qpost\Router\API\Post;

use DateTime;
use qpost\Database\EntityManager;
use qpost\Feed\FeedEntry;
use qpost\Feed\FeedEntryType;
use qpost\Media\MediaFile;
use qpost\Util\Method;
use qpost\Util\Util;
use function base64_decode;
use function file_exists;
use function file_put_contents;
use function getimagesize;
use function getrandmax;
use function hash;
use function is_array;
use function is_string;
use function json_encode;
use function qpost\Router\API\api_auth_check;
use function qpost\Router\API\api_create_route;
use function qpost\Router\API\api_get_token;
use function qpost\Router\API\api_prepare_object;
use function qpost\Router\API\api_request_data;
use function rand;
use function strlen;
use const POST_CHARACTER_LIMIT;
use const VERIFIED_POST_CHARACTER_LIMIT;

api_create_route(Method::POST, "/post", function () {
	if (api_auth_check($this)) {
		$requestData = api_request_data($this);
		$token = api_get_token();
		$currentUser = $token->getUser();

		if (isset($requestData["message"])) {
			if (is_string($requestData["message"])) {
				$message = trim($requestData["message"]);

				/**
				 * @var int $characterLimit
				 */
				$characterLimit = $currentUser->isVerified() ? VERIFIED_POST_CHARACTER_LIMIT : POST_CHARACTER_LIMIT;

				if (strlen($message) >= 0 && strlen($message) <= $characterLimit) {
					$attachments = [];
					if (isset($requestData["attachments"])) {
						if (is_array($requestData["attachments"])) {
							foreach ($requestData["attachments"] as $attachment) {
								if (is_string($attachment)) {
									if (@base64_decode($attachment)) {
										$attachments[] = $attachment;
									} else {
										$this->response->status = "400";
										return json_encode(["error" => "'attachments' has to be an array of base64 strings."]);
									}
								} else {
									$this->response->status = "400";
									return json_encode(["error" => "'attachments' has to be an array of base64 strings."]);
								}
							}
						} else {
							$this->response->status = "400";
							return json_encode(["error" => "'attachments' has to be an array of base64 strings."]);
						}
					}

					if (strlen($message) === 0 && count($attachments) === 0) {
						$this->response->status = "400";
						return json_encode(["error" => "Post is empty."]);
					}

					$entityManager = EntityManager::instance();

					$feedEntry = new FeedEntry();
					$feedEntry->setUser($currentUser)
						->setText($message)
						->setSessionId($token->getId())
						->setType(FeedEntryType::POST)
						->setNSFW(false)// TODO
						->setTime(new DateTime("now"));

					$entityManager->persist($feedEntry);

					foreach ($attachments as $base64) {
						$file = base64_decode($base64);

						$path = null;
						while (is_null($path) || file_exists($path)) $path = __DIR__ . "/../../../../../tmp/" . rand(0, getrandmax()) . ".png";
						file_put_contents($path, $file);

						if (!(@getimagesize($path))) {
							continue;
						}

						$sha256 = hash("sha256", $file);

						/**
						 * @var MediaFile $mediaFile
						 */
						$mediaFile = $entityManager->getRepository(MediaFile::class)->findOneBy([
							"sha256" => $sha256
						]);

						if (!$mediaFile) {
							$cdnResult = Util::storeFileOnCDN($path);
							if (!is_null($cdnResult)) {
								if (isset($cdnResult["url"])) {
									$url = $cdnResult["url"];

									$mediaFile = new MediaFile();
									$mediaFile->setSHA256($sha256)
										->setURL($url)
										->setOriginalUploader($currentUser)
										->setType("IMAGE")
										->setTime(new DateTime("now"));
								}
							}
						}

						if ($mediaFile) {
							$feedEntry->addAttachment($mediaFile);
							$mediaFile->addPost($feedEntry);

							$entityManager->persist($mediaFile);
						}
					}

					$entityManager->flush();

					return json_encode(["post" => api_prepare_object($feedEntry)]);
				} else {
					$this->response->status = "400";
					return json_encode(["error" => "The message must be between 0 and " . $characterLimit . " characters long."]);
				}
			} else {
				$this->response->status = "400";
				return json_encode(["error" => "'message' has to be a string."]);
			}
		} else {
			$this->response->status = "400";
			return json_encode(["error" => "'message' is required."]);
		}
	} else {
		return "";
	}
});
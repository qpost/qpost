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

use DateTime;
use Exception;
use qpost\Constants\FeedEntryType;
use qpost\Constants\MediaFileType;
use qpost\Entity\FeedEntry;
use qpost\Entity\MediaFile;
use qpost\Service\APIService;
use qpost\Service\GigadriveService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function base64_decode;
use function file_exists;
use function file_put_contents;
use function filesize;
use function getimagesize;
use function getrandmax;
use function hash;
use function is_array;
use function is_null;
use function is_string;
use function rand;
use function sys_get_temp_dir;

class PostController extends AbstractController {
	/**
	 * @Route("/api/post", methods={"POST"})
	 *
	 * @param APIService $apiService
	 * @param GigadriveService $gigadriveService
	 * @return Response
	 * @throws Exception
	 */
	public function post(APIService $apiService, GigadriveService $gigadriveService): Response {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$parameters = $apiService->parameters();
		$token = $apiService->getToken();
		$user = $apiService->getUser();

		if ($parameters->has("message")) {
			$message = $parameters->get("message");

			if (is_string($message)) {
				$message = trim($message);

				/**
				 * @var int $characterLimit
				 */
				$characterLimit = $user->isVerified() ? $_ENV["VERIFIED_POST_CHARACTER_LIMIT"] : $_ENV["POST_CHARACTER_LIMIT"];

				if (strlen($message) >= 0 && strlen($message) <= $characterLimit) {
					$attachments = [];
					if ($parameters->has("attachments")) {
						$passedAttachments = $parameters->get("attachments");

						if (is_array($passedAttachments)) {
							if (count($passedAttachments) <= 4) {
								foreach ($passedAttachments as $attachment) {
									if (is_string($attachment)) {
										if (@base64_decode($attachment)) {
											$attachments[] = $attachment;
										} else {
											return $apiService->json(["error" => "'attachments' has to be an array of base64 strings."], 400);
										}
									} else {
										return $apiService->json(["error" => "'attachments' has to be an array of base64 strings."], 400);
									}
								}
							} else {
								return $apiService->json(["error" => "You may not upload more than 4 attachments at once."], 400);
							}
						} else {
							return $apiService->json(["error" => "'attachments' has to be an array of base64 strings."], 400);
						}
					}

					if (strlen($message) === 0 && count($attachments) === 0) {
						return $apiService->json(["error" => "Post is empty."], 400);
					}

					$entityManager = $apiService->getEntityManager();
					$feedEntry = (new FeedEntry())
						->setUser($user)
						->setText($message)
						->setToken($token)
						->setType(FeedEntryType::POST)
						->setNSFW(false)// TODO
						->setTime(new DateTime("now"));

					$entityManager->persist($feedEntry);

					foreach ($attachments as $base64) {
						$file = @base64_decode($base64);

						if ($file) {
							$path = null;
							while (is_null($path) || file_exists($path)) $path = sys_get_temp_dir() . "/qpost/attachments/" . rand(0, getrandmax()) . ".png";
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
								// Check if file is smaller than 2MB
								$fileSize = @filesize($path);
								if (!($fileSize) || !(($fileSize / 1024 / 1024) < 2)) {
									continue;
								}

								$url = $gigadriveService->storeFileOnCDN($file);
								if (!is_null($url)) {
									$mediaFile = (new MediaFile())
										->setSHA256($sha256)
										->setURL($url)
										->setOriginalUploader($user)
										->setType(MediaFileType::IMAGE)
										->setTime(new DateTime("now"));
								}
							}

							if ($mediaFile) {
								$feedEntry->addAttachment($mediaFile);
								$mediaFile->addFeedEntry($feedEntry);

								$entityManager->persist($mediaFile);
							}
						}
					}

					$entityManager->flush();

					return $apiService->json(["post" => $apiService->serialize($feedEntry)]);
				} else {
					return $apiService->json(["error" => "The message must be between 0 and " . $characterLimit . " characters long."], 400);
				}
			} else {
				return $apiService->json(["error" => "'message' has to be a string."], 400);
			}
		} else {
			return $apiService->json(["error" => "'message' is required."], 400);
		}
	}
}
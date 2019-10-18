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
use MediaEmbed\MediaEmbed;
use qpost\Constants\FeedEntryType;
use qpost\Constants\MediaFileType;
use qpost\Entity\FeedEntry;
use qpost\Entity\MediaFile;
use qpost\Service\APIService;
use qpost\Service\DataDeletionService;
use qpost\Service\GigadriveService;
use qpost\Util\Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function base64_decode;
use function count;
use function dirname;
use function file_exists;
use function file_put_contents;
use function filesize;
use function getimagesize;
use function getrandmax;
use function hash;
use function is_array;
use function is_bool;
use function is_null;
use function is_numeric;
use function is_string;
use function mkdir;
use function rand;
use function str_replace;
use function strlen;
use function sys_get_temp_dir;
use function trim;

class StatusController extends AbstractController {
	/**
	 * @Route("/api/status", methods={"GET"})
	 *
	 * @param APIService $apiService
	 * @return Response|null
	 */
	public function info(APIService $apiService) {
		$response = $apiService->validate(false);
		if (!is_null($response)) return $response;

		$parameters = $apiService->parameters();

		if ($parameters->has("id")) {
			$id = $parameters->get("id");

			if (!Util::isEmpty($id)) {
				if (is_numeric($id)) {
					/**
					 * @var FeedEntry $feedEntry
					 */
					$feedEntry = $apiService->getEntityManager()->getRepository(FeedEntry::class)->findOneBy([
						"id" => $id
					]);

					if (!is_null($feedEntry) && $apiService->mayView($feedEntry)) {
						return $apiService->json(["result" => $apiService->serialize($feedEntry)]);
					} else {
						return $apiService->json(["error" => "The requested resource could not be found."], 404);
					}
				} else {
					return $apiService->json(["error" => "'id' has to be an integer."], 400);
				}
			} else {
				return $apiService->json(["error" => "'id' is required."], 400);
			}
		} else {
			return $apiService->json(["error" => "'id' is required."], 400);
		}
	}

	/**
	 * @Route("/api/status", methods={"DELETE"})
	 *
	 * @param APIService $apiService
	 * @param DataDeletionService $dataDeletionService
	 * @return Response
	 */
	public function delete(APIService $apiService, DataDeletionService $dataDeletionService): Response {
		$response = $apiService->validate(true);
		if (!is_null($response)) return $response;

		$parameters = $apiService->parameters();
		$user = $apiService->getUser();

		if ($parameters->has("id")) {
			$id = $parameters->get("id");

			if (!Util::isEmpty($id)) {
				if (is_numeric($id)) {
					/**
					 * @var FeedEntry $feedEntry
					 */
					$feedEntry = $apiService->getEntityManager()->getRepository(FeedEntry::class)->findOneBy([
						"id" => $id
					]);

					if (!is_null($feedEntry)) {
						$entryOwner = $feedEntry->getUser();

						if (!is_null($entryOwner) && $entryOwner->getId() === $user->getId()) {
							$dataDeletionService->deleteFeedEntry($feedEntry);

							return $apiService->noContent();
						} else {
							return $apiService->json(["error" => "You are not allowed to delete this status."], 403);
						}
					} else {
						return $apiService->json(["error" => "The requested resource could not be found."], 404);
					}
				} else {
					return $apiService->json(["error" => "'id' has to be an integer."], 400);
				}
			} else {
				return $apiService->json(["error" => "'id' is required."], 400);
			}
		} else {
			return $apiService->json(["error" => "'id' is required."], 400);
		}
	}

	/**
	 * @Route("/api/status", methods={"POST"})
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
				$message = trim(Util::fixString($message));

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

					// check if empty
					if (strlen($message) === 0 && count($attachments) === 0) {
						return $apiService->json(["error" => "Post is empty."], 400);
					}

					// handle NSFW
					$nsfw = false;
					if ($parameters->has("nsfw")) {
						$nsfw = $parameters->get("nsfw");

						if (!is_bool($nsfw)) {
							return $apiService->json(["error" => "'nsfw' has to be a boolean."]);
						}
					}

					$entityManager = $apiService->getEntityManager();
					$feedEntry = (new FeedEntry())
						->setUser($user)
						->setText(Util::isEmpty($message) ? null : $message)
						->setToken($token)
						->setType(FeedEntryType::POST)
						->setNSFW($nsfw)
						->setTime(new DateTime("now"));

					$entityManager->persist($feedEntry);

					$mediaFileRepository = $entityManager->getRepository(MediaFile::class);

					foreach ($attachments as $base64) {
						$file = @base64_decode($base64);

						if ($file) {
							$path = null;
							while (is_null($path) || file_exists($path)) $path = sys_get_temp_dir() . "/qpost/attachments/" . rand(0, getrandmax()) . ".png";

							$directoryPath = dirname($path);
							if (!file_exists($directoryPath)) {
								mkdir($directoryPath, 0777, true);
							}

							file_put_contents($path, $file);

							if (!(@getimagesize($path))) {
								continue;
							}

							$sha256 = hash("sha256", $file);

							/**
							 * @var MediaFile $mediaFile
							 */
							$mediaFile = $mediaFileRepository->findOneBy([
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

					// handle video embeds
					if (!Util::isEmpty($message) && count($attachments) === 0) {
						$urls = Util::getURLsInString($message);

						if ($urls && count($urls) > 0) {
							$videoURL = null;
							$mediaEmbed = new MediaEmbed();

							foreach ($urls as $url) {
								$mediaObject = $mediaEmbed->parseUrl($url);

								if ($mediaObject) {
									$mediaObject->setParam("autoplay", "false");

									$videoURL = str_replace("&amp;", "&", $mediaObject->getEmbedSrc());
									break;
								}
							}

							if ($videoURL) {
								$sha256 = hash("sha256", $videoURL);

								$mediaFile = $mediaFileRepository->findOneBy([
									"url" => $videoURL,
									"sha256" => $sha256
								]);

								if (!$mediaFile) {
									$mediaFile = (new MediaFile())
										->setSHA256($sha256)
										->setURL($videoURL)
										->setOriginalUploader($user)
										->setType(MediaFileType::VIDEO)
										->setTime(new DateTime("now"));
								}

								if ($mediaFile) {
									$feedEntry->addAttachment($mediaFile);
									$mediaFile->addFeedEntry($feedEntry);

									$entityManager->persist($mediaFile);
								}
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

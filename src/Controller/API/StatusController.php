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

namespace qpost\Controller\API;

use DateTime;
use Doctrine\DBAL\Types\Type;
use Exception;
use MediaEmbed\MediaEmbed;
use qpost\Constants\Feature;
use qpost\Constants\FeedEntryType;
use qpost\Constants\MediaFileType;
use qpost\Constants\NotificationType;
use qpost\Entity\FeedEntry;
use qpost\Entity\Hashtag;
use qpost\Entity\MediaFile;
use qpost\Entity\Notification;
use qpost\Entity\User;
use qpost\Service\APIService;
use qpost\Service\DataDeletionService;
use qpost\Service\StorageService;
use qpost\Util\Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function array_key_exists;
use function base64_decode;
use function count;
use function dirname;
use function explode;
use function file_exists;
use function file_put_contents;
use function filesize;
use function getimagesize;
use function getrandmax;
use function hash;
use function is_array;
use function is_bool;
use function is_int;
use function is_null;
use function is_numeric;
use function is_string;
use function mkdir;
use function rand;
use function str_replace;
use function strlen;
use function substr;
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
	 * @param StorageService $storageService
	 * @return Response
	 * @throws Exception
	 */
	public function post(APIService $apiService, StorageService $storageService): Response {
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
				$characterLimit = $user->hasFeature(Feature::INCREASED_CHARACTER_LIMIT) ? $_ENV["INCREASED_POST_CHARACTER_LIMIT"] : $_ENV["POST_CHARACTER_LIMIT"];

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

					// handle reply status
					/**
					 * @var FeedEntry $parent
					 */
					$parent = null;
					$type = FeedEntryType::POST;
					if ($parameters->has("parent")) {
						$parentId = $parameters->get("parent");

						if (is_int($parentId)) {
							$parent = $entityManager->getRepository(FeedEntry::class)->createQueryBuilder("f")
								->where("f.id = :id")
								->setParameter("id", $parentId, Type::INTEGER)
								->andWhere("f.type = :post OR f.type = :reply")
								->setParameter("post", FeedEntryType::POST, Type::STRING)
								->setParameter("reply", FeedEntryType::REPLY, Type::STRING)
								->getQuery()
								->useQueryCache(true)
								->getOneOrNullResult();

							if ($parent) {
								if ($apiService->mayView($parent)) {
									$type = FeedEntryType::REPLY;
								} else {
									return $apiService->json(["error" => "The requested resource could not be found."], 404);
								}
							} else {
								return $apiService->json(["error" => "The requested resource could not be found."], 404);
							}
						} else {
							return $apiService->json(["error" => "'parent' has to be a boolean."]);
						}
					}

					$feedEntry = (new FeedEntry())
						->setUser($user)
						->setText(Util::isEmpty($message) ? null : $message)
						->setToken($token)
						->setType($type)
						->setParent($parent)
						->setNSFW($nsfw)
						->setTime(new DateTime("now"));

					$entityManager->persist($feedEntry);

					// handle hashtags
					if (!Util::isEmpty($message)) {
						$tags = Util::extractHashtags($message);

						if (count($tags) > 0) {
							foreach ($tags as $tag) {
								$hashtag = $entityManager->getRepository(Hashtag::class)->findHashtag($tag);
								if (!$hashtag) {
									$hashtag = (new Hashtag())
										->setId($tag)
										->setCreator($user)
										->setCreatingEntry($feedEntry)
										->setTime(new DateTime("now"));
								}

								$hashtag->addFeedEntry($feedEntry);
								$entityManager->persist($hashtag);
							}
						}
					}

					// handle reply notification
					if ($parent && $type === FeedEntryType::REPLY) {
						$parentUser = $parent->getUser();

						if ($parentUser->getId() != $user->getId() && $apiService->maySendNotifications($parentUser, $user)) { // don't send self notification
							$notification = (new Notification())
								->setUser($parentUser)
								->setReferencedFeedEntry($feedEntry)
								->setReferencedUser($user)
								->setType(NotificationType::REPLY)
								->setTime(new DateTime("now"));

							$entityManager->persist($notification);
						}
					}

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
								return $apiService->json(["error" => "One of the attachments is invalid."], 400);
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
								if (!($fileSize) || !(($fileSize / 1024 / 1024) < 10)) {
									return $apiService->json(["error" => "Attachments can not be bigger than 10MB."], 413);
								}

								$url = $storageService->uploadImage($file);
								if (!is_null($url)) {
									if (Util::endsWith($url, ".gif") && count($attachments) > 1) {
										return $apiService->json(["error" => "You may not upload more attachments, if you include a GIF."], 400);
									}

									$mediaFile = $mediaFileRepository->findOneBy([
										"url" => $url
									]);

									if (!$mediaFile) {
										$mediaFile = (new MediaFile())
											->setSHA256($sha256)
											->setURL($url)
											->setOriginalUploader($user)
											->setType(MediaFileType::IMAGE)
											->setTime(new DateTime("now"));
									}
								} else {
									return $apiService->json(["error" => "Failed to upload attachments."], 400);
								}
							}

							if ($mediaFile) {
								$feedEntry->addAttachment($mediaFile);
								$mediaFile->addFeedEntry($feedEntry);

								$entityManager->persist($mediaFile);
							}
						} else {
							return $apiService->json(["error" => "'attachments' has to be an array of base64 strings."], 400);
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

								if ($mediaObject && array_key_exists("iframe-player", $mediaObject->stub())) {
									$mediaObject->setParam("autoplay", "false");

									$videoURL = str_replace("&amp;", "&", $mediaObject->getEmbedSrc());
									break;
								}
							}

							if ($videoURL) {
								$mediaFile = $mediaFileRepository->findOneBy([
									"url" => $videoURL
								]);

								if (!$mediaFile) {
									$sha256 = hash("sha256", $videoURL);

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

					// handle mentions
					if (!Util::isEmpty($message)) {
						/**
						 * @var User[] $mentionedUsers
						 */
						$mentionedUsers = [];
						$userRepository = $entityManager->getRepository(User::class);

						foreach (explode(" ", $message) as $word) {
							if (!Util::startsWith($word, "@")) {
								continue;
							}

							if (strlen($word) >= 4 && strlen($word) <= 17) { // @ + minimum of 3 character usernames + maximum of 16 character usernames
								$username = substr($word, 1);
								$mentionedUser = $userRepository->getUserByUsername($username);

								if ($mentionedUser && $mentionedUser->getId() != $user->getId() && $apiService->mayView($mentionedUser)) {
									$mentionedUsers[] = $mentionedUser;
								}
							}
						}

						foreach ($mentionedUsers as $mentionedUser) {
							if ($apiService->maySendNotifications($mentionedUser, $user)) {
								$notification = (new Notification())
									->setUser($mentionedUser)
									->setReferencedFeedEntry($feedEntry)
									->setReferencedUser($user)
									->setType(NotificationType::MENTION)
									->setTime(new DateTime("now"));

								$entityManager->persist($notification);
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

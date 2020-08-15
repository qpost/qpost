<?php
/*
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

namespace qpost\Controller\API;

use DateTime;
use Gigadrive\Bundle\SymfonyExtensionsBundle\DependencyInjection\Util;
use MediaEmbed\MediaEmbed;
use qpost\Constants\APIParameterType;
use qpost\Constants\Feature;
use qpost\Constants\FeedEntryType;
use qpost\Constants\MediaFileType;
use qpost\Constants\NotificationType;
use qpost\Entity\FeedEntry;
use qpost\Entity\Hashtag;
use qpost\Entity\MediaAttachment;
use qpost\Entity\MediaFile;
use qpost\Entity\Notification;
use qpost\Entity\User;
use qpost\Exception\AccessNotAllowedException;
use qpost\Exception\InvalidParameterIntegerRangeException;
use qpost\Exception\InvalidParameterStringLengthException;
use qpost\Exception\InvalidParameterTypeException;
use qpost\Exception\InvalidTokenException;
use qpost\Exception\MissingParameterException;
use qpost\Exception\ResourceNotFoundException;
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
use function is_null;
use function mkdir;
use function rand;
use function str_replace;
use function strlen;
use function substr;
use function sys_get_temp_dir;
use function trim;

/**
 * @Route("/api")
 */
class StatusController extends APIController {
	/**
	 * @Route("/status", methods={"GET"})
	 *
	 * @return Response|null
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws MissingParameterException
	 * @throws ResourceNotFoundException
	 */
	public function info() {
		return $this->response(
			$this->feedEntry("id")
		);
	}

	/**
	 * @Route("/status", methods={"DELETE"})
	 *
	 * @return Response
	 * @throws AccessNotAllowedException
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws MissingParameterException
	 * @throws ResourceNotFoundException
	 * @throws InvalidTokenException
	 */
	public function delete(): Response {
		$this->validateAuth();
		$user = $this->getUser();
		$feedEntry = $this->feedEntry("id");

		$entryOwner = $feedEntry->getUser();

		if (is_null($entryOwner) || $entryOwner->getId() !== $user->getId()) {
			throw new AccessNotAllowedException();
		}

		$this->dataDeletionService->deleteFeedEntry($feedEntry);

		return $this->response();
	}

	/**
	 * @Route("/status", methods={"POST"})
	 *
	 * @return Response
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws InvalidTokenException
	 * @throws MissingParameterException
	 * @throws ResourceNotFoundException
	 * @throws InvalidParameterStringLengthException
	 */
	public function post(): Response {
		$this->validateAuth();
		$user = $this->getUser();

		$this->validateParameterType("message", APIParameterType::STRING);
		$this->validateParameterStringLength("message", 0, $user->hasFeature(Feature::INCREASED_CHARACTER_LIMIT) ? $_ENV["INCREASED_POST_CHARACTER_LIMIT"] : $_ENV["POST_CHARACTER_LIMIT"]);
		$this->validateParameterType("nsfw", APIParameterType::BOOLEAN);
		$this->validateParameterType("attachments", APIParameterType::BASE64_ARRAY, false);

		$parameters = $this->parameters();
		$token = $this->apiService->getToken();
		$message = trim(Util::fixString($parameters->get("message")));
		$attachments = $parameters->has("attachments") ? $parameters->get("attachments") : [];
		$nsfw = $parameters->get("nsfw");
		$type = FeedEntryType::POST;

		// check for too many attachments
		if (count($attachments) > 5) {
			return $this->error("You may not upload more than 4 attachments at once.", Response::HTTP_BAD_REQUEST);
		}

		// check if empty
		if (strlen($message) === 0 && count($attachments) === 0) {
			return $this->error("Post is empty.", Response::HTTP_BAD_REQUEST);
		}

		// handle reply status
		$parent = $this->feedEntry("parent", null, false);
		if ($parent) {
			if ($parent->getType() !== FeedEntryType::POST && $parent->getType() !== FeedEntryType::REPLY) {
				throw new ResourceNotFoundException();
			}

			$type = FeedEntryType::REPLY;
		}

		$feedEntry = (new FeedEntry())
			->setUser($user)
			->setText(Util::isEmpty($message) ? null : $message)
			->setToken($token)
			->setType($type)
			->setParent($parent)
			->setNSFW($nsfw)
			->setTime(new DateTime("now"));

		$this->entityManager->persist($feedEntry);

		// handle hashtags
		if (!Util::isEmpty($message)) {
			$tags = Util::extractHashtags($message);

			if (count($tags) > 0) {
				foreach ($tags as $tag) {
					$hashtag = $this->entityManager->getRepository(Hashtag::class)->findHashtag($tag);
					if (!$hashtag) {
						$hashtag = (new Hashtag())
							->setId($tag)
							->setCreator($user)
							->setCreatingEntry($feedEntry)
							->setTime(new DateTime("now"));
					}

					$hashtag->addFeedEntry($feedEntry);
					$this->entityManager->persist($hashtag);
				}
			}
		}

		// handle reply notification
		if ($parent && $type === FeedEntryType::REPLY) {
			$parentUser = $parent->getUser();

			if ($parentUser->getId() != $user->getId() && $this->apiService->maySendNotifications($parentUser, $user)) { // don't send self notification
				$notification = (new Notification())
					->setUser($parentUser)
					->setReferencedFeedEntry($feedEntry)
					->setReferencedUser($user)
					->setType(NotificationType::REPLY)
					->setTime(new DateTime("now"));

				$this->entityManager->persist($notification);
			}
		}

		$mediaFileRepository = $this->entityManager->getRepository(MediaFile::class);

		$position = 1;
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
					return $this->error("One of the attachments is invalid.", 400);
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
						return $this->error("Attachments can not be bigger than 10MB.", Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
					}

					$url = $this->storageService->uploadImage($file);
					if (!is_null($url)) {
						if (Util::endsWith($url, ".gif") && count($attachments) > 1) {
							return $this->error("You may not upload more attachments, if you include a GIF.", Response::HTTP_BAD_REQUEST);
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
						return $this->error("Failed to upload attachments.", Response::HTTP_BAD_REQUEST);
					}
				}

				if ($mediaFile) {
					$attachment = (new MediaAttachment())
						->setFeedEntry($feedEntry)
						->setMediaFile($mediaFile)
						->setPosition($position)
						->setTime(new DateTime("now"));

					$this->entityManager->persist($attachment);

					$position++;
				}
			} else {
				return $this->error("'attachments' has to be an array of base64 strings.", Response::HTTP_BAD_REQUEST);
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
						$attachment = (new MediaAttachment())
							->setFeedEntry($feedEntry)
							->setMediaFile($mediaFile)
							->setPosition($position)
							->setTime(new DateTime("now"));

						$this->entityManager->persist($attachment);
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

			$userRepository = $this->entityManager->getRepository(User::class);

			foreach (explode(" ", $message) as $word) {
				if (!Util::startsWith($word, "@")) {
					continue;
				}

				if (strlen($word) >= 4 && strlen($word) <= 17) { // @ + minimum of 3 character usernames + maximum of 16 character usernames
					$username = substr($word, 1);
					$mentionedUser = $userRepository->getUserByUsername($username);

					if ($mentionedUser && $mentionedUser->getId() != $user->getId() && $this->apiService->mayView($mentionedUser)) {
						$mentionedUsers[] = $mentionedUser;
					}
				}
			}

			foreach ($mentionedUsers as $mentionedUser) {
				if ($this->apiService->maySendNotifications($mentionedUser, $user)) {
					$notification = (new Notification())
						->setUser($mentionedUser)
						->setReferencedFeedEntry($feedEntry)
						->setReferencedUser($user)
						->setType(NotificationType::MENTION)
						->setTime(new DateTime("now"));

					$this->entityManager->persist($notification);
				}
			}
		}

		$this->entityManager->flush();

		return $this->response($feedEntry);
	}
}

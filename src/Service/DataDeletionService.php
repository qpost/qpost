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

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use qpost\Constants\FeedEntryType;
use qpost\Entity\FeedEntry;
use qpost\Entity\Hashtag;
use qpost\Entity\MediaFile;
use qpost\Entity\Notification;
use qpost\Entity\ResetPasswordToken;
use qpost\Entity\Suspension;
use qpost\Entity\User;
use qpost\Entity\UsernameHistoryEntry;

class DataDeletionService {
	/**
	 * @var EntityManagerInterface $entityManager
	 */
	private $entityManager;

	/**
	 * @var LoggerInterface $logger
	 */
	private $logger;

	public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger) {
		$this->entityManager = $entityManager;
		$this->logger = $logger;
	}

	public function deleteUser(User $user) {
		$entityManager = $this->entityManager;

		// Update media files
		foreach ($entityManager->getRepository(MediaFile::class)->findBy([
			"originalUploader" => $user
		]) as $mediaFile) {
			$mediaFile->setOriginalUploader(null);
			$entityManager->persist($mediaFile);
		}

		// Delete notifications
		foreach ($entityManager->getRepository(Notification::class)->findBy([
			"referencedUser" => $user
		]) as $notification) {
			$entityManager->remove($notification);
		}

		// Delete reset password tokens
		foreach ($entityManager->getRepository(ResetPasswordToken::class)->findBy([
			"user" => $user
		]) as $passwordToken) {
			$entityManager->remove($passwordToken);
		}

		// Update created suspensions
		foreach ($entityManager->getRepository(Suspension::class)->findBy([
			"staff" => $user
		]) as $suspension) {
			$suspension->setStaff(null);
			$entityManager->persist($suspension);
		}

		// Delete own suspensions
		foreach ($entityManager->getRepository(Suspension::class)->findBy([
			"target" => $user
		]) as $suspension) {
			$entityManager->remove($suspension);
		}

		// Update hashtags
		foreach ($entityManager->getRepository(Hashtag::class)->findBy([
			"creator" => $user
		]) as $hashtag) {
			$hashtag->setCreator(null);
			$entityManager->persist($hashtag);
		}

		// Delete feed entries
		foreach ($entityManager->getRepository(FeedEntry::class)->findBy([
			"user" => $user
		]) as $feedEntry) {
			$this->deleteFeedEntry($feedEntry);
		}

		// Delete name history
		foreach ($entityManager->getRepository(UsernameHistoryEntry::class)->getEntriesByUser($user) as $entry) {
			$entityManager->remove($entry);
		}

		$entityManager->remove($user);
		$entityManager->flush();
	}

	/**
	 * @param FeedEntry $feedEntry
	 */
	public function deleteFeedEntry(FeedEntry $feedEntry) {
		$entityManager = $this->entityManager;

		foreach ($entityManager->getRepository(Notification::class)->findBy([
			"referencedFeedEntry" => $feedEntry
		]) as $notification) {
			$entityManager->remove($notification);
		}

		foreach ($feedEntry->getChildren() as $child) {
			if ($child->getType() === FeedEntryType::POST || $child->getType() === FeedEntryType::REPLY) {
				$child->setParent(null);
				$entityManager->persist($child);
			} else {
				$this->deleteFeedEntry($child);
			}
		}

		foreach ($entityManager->getRepository(Hashtag::class)->findBy([
			"creatingEntry" => $feedEntry
		]) as $hashtag) {
			$hashtag->setCreatingEntry(null);
			$entityManager->persist($hashtag);
		}

		$entityManager->remove($feedEntry);
		$entityManager->flush();
	}

	/**
	 * @return EntityManagerInterface
	 */
	public function getEntityManager(): EntityManagerInterface {
		return $this->entityManager;
	}

	/**
	 * @return LoggerInterface
	 */
	public function getLogger(): LoggerInterface {
		return $this->logger;
	}
}
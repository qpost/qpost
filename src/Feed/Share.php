<?php

namespace qpost\Feed;

use DateTime;
use qpost\Account\User;
use qpost\Database\EntityManager;

class Share {
	/**
	 * @param User $user
	 * @param FeedEntry $post
	 * @return void
	 */
	public static function share(User $user, FeedEntry $post): void {
		if ($user && $post) {
			if ($post->getType() === FeedEntryType::POST) {
				if (!self::hasShared($user, $post)) {
					$entityManager = EntityManager::instance();

					$share = new FeedEntry();

					$share->setUser($user)
						->setPost($post)
						->setType(FeedEntryType::SHARE)
						->setSessionId("TODO")// TODO
						->setNSFW($post->isNSFW())
						->setTime(new DateTime("now"));

					$entityManager->persist($share);
					$entityManager->flush();
				}
			}
		}
	}

	/**
	 * @param User $user
	 * @param FeedEntry $post
	 * @return bool
	 */
	public static function hasShared(User $user, FeedEntry $post): bool {
		return !is_null(self::getShare($user, $post));
	}

	/**
	 * @param User $user
	 * @param FeedEntry $post
	 * @return FeedEntry|null
	 */
	public static function getShare(User $user, FeedEntry $post): ?FeedEntry {
		/**
		 * @var FeedEntry $entry
		 */
		$entry = EntityManager::instance()->getRepository(FeedEntry::class)->findOneBy([
			"user" => $user,
			"post" => $post,
			"type" => FeedEntryType::SHARE
		]);

		return $entry;
	}

	/**
	 * @param User $user
	 * @param FeedEntry $post
	 * @return void
	 */
	public static function unshare(User $user, FeedEntry $post): void {
		if ($user && $post) {
			if ($post->getType() === FeedEntryType::POST) {
				$share = self::getShare($user, $post);

				if (!is_null($share)) {
					$entityManager = EntityManager::instance();

					$entityManager->remove($share);
					$entityManager->flush();
				}
			}
		}
	}
}
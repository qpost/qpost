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

namespace qpost\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use qpost\Entity\FeedEntry;
use qpost\Entity\User;
use function is_null;

/**
 * @method FeedEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method FeedEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method FeedEntry[]    findAll()
 * @method FeedEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeedEntryRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, FeedEntry::class);
	}

	public function getEntryById(int $id): ?FeedEntry {
		return $this->findOneBy([
			"id" => $id
		]);
	}

	public function getFeed(User $from, User $target = null, int $min = null, int $max = null): array {
		$rsm = $this->createResultSetMappingBuilder("f");
		$rsm->addScalarResult("favoriteCount", "favoriteCount", "integer");
		$rsm->addScalarResult("replyCount", "replyCount", "integer");
		$rsm->addScalarResult("shareCount", "shareCount", "integer");

		$ownerWhere = is_null($target) ? "(EXISTS (SELECT 1 FROM follower AS ff WHERE ff.sender_id = ? AND ff.receiver_id = u.id) OR u.id = ?)" : "f.user_id = ?";

		$parameters = [$from->getId()];

		if (is_null($target)) {
			$parameters[] = $from->getId();
		}

		if (!is_null($min)) {
			$parameters[] = $min;
		}

		if (!is_null($max)) {
			$parameters[] = $max;
		}

		$query = $this->_em->createNativeQuery("SELECT " . $rsm->generateSelectClause([
				"f" => "f",
			]) . ",COALESCE(favoriteCount, 0) AS favoriteCount,
       COALESCE(replyCount, 0)    AS replyCount,
       COALESCE(shareCount, 0)    AS shareCount
FROM feed_entry AS f
         LEFT JOIN (
    SELECT feed_entry_id, COUNT(favorite.id) AS favoriteCount
    FROM favorite
    GROUP BY feed_entry_id
) favorite_counts ON favorite_counts.feed_entry_id = f.id
         LEFT JOIN (
    SELECT parent_id, SUM(feed_entry.type = 'REPLY') AS replyCount, SUM(feed_entry.type = 'SHARE') AS shareCount
    FROM feed_entry
    WHERE feed_entry.type = 'REPLY'
       OR feed_entry.type = 'SHARE'
    GROUP BY parent_id
) children ON children.parent_id = f.id
		INNER JOIN user AS u ON f.user_id = u.id
WHERE " . $ownerWhere . (is_null($target) ? " AND u.privacy_level != 'CLOSED'" : "") . " AND f.text NOT LIKE '@%'
" . (!is_null($min) ? " AND f.id > ?" : "") . "
" . (!is_null($max) ? " AND f.id < ?" : "") . "
AND ((f.type = 'POST' AND f.parent_id IS NULL) OR (f.type = 'SHARE' AND f.parent_id IS NOT NULL))
GROUP BY f.id
ORDER BY f.time DESC
LIMIT 30", $rsm);

		foreach ($parameters as $index => $parameter) {
			$query = $query->setParameter($index, $parameter);
		}

		$results = $query->getResult();

		$entries = [];

		foreach ($results as $result) {
			/**
			 * @var FeedEntry $feedEntry
			 */
			$feedEntry = $result[0];

			$feedEntry->setReplyCount($result["replyCount"])
				->setShareCount($result["shareCount"])
				->setFavoriteCount($result["favoriteCount"]);

			$entries[] = $feedEntry;
		}

		return $entries;
	}
}

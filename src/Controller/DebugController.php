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

namespace qpost\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Psr\Log\LoggerInterface;
use qpost\Entity\FeedEntry;
use qpost\Service\APIService;
use qpost\Twig\Twig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DebugController extends AbstractController {
	/**
	 * @Route("/debug", condition="'dev' === '%kernel.environment%'")
	 * @param EntityManagerInterface $entityManager
	 * @param LoggerInterface $logger
	 * @param APIService $apiService
	 * @return Response
	 */
	public function debugAction(EntityManagerInterface $entityManager, LoggerInterface $logger, APIService $apiService) {
		$rsm = new ResultSetMappingBuilder($entityManager);
		$rsm->addRootEntityFromClassMetadata(FeedEntry::class, "f");
		$rsm->addScalarResult("favoriteCount", "favoriteCount", "integer");
		$rsm->addScalarResult("replyCount", "replyCount", "integer");
		$rsm->addScalarResult("shareCount", "shareCount", "integer");

		$value = $entityManager->createNativeQuery("SELECT " . $rsm->generateSelectClause([
				"f" => "f",
			]) . ",
       COALESCE(favoriteCount, 0) AS favoriteCount,
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
WHERE f.user_id = ?
GROUP BY f.id
ORDER BY favoriteCount DESC;", $rsm)
			->setParameter(1, 1)
			->getResult();

		$logger->info("value", ["value" => $value]);

		return $this->render("debug.html.twig", Twig::param([
			"value" => $value
		]));
	}
}
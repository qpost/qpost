<?php

namespace qpost\Router;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\QueryBuilder;
use qpost\Account\PrivacyLevel;
use qpost\Account\User;
use qpost\Database\EntityManager;
use qpost\Feed\FeedEntry;
use qpost\Feed\FeedEntryType;
use qpost\Util\Util;

function create_posts_query($query): QueryBuilder {
	return EntityManager::instance()->getRepository(FeedEntry::class)->createQueryBuilder("p")
		->innerJoin("p.user", "u")
		->where("p.post IS NULL")
		->andWhere("p.text LIKE :q OR u.displayName LIKE :q OR u.username LIKE :q")
		->setParameter("q", "%" . $query . "%")
		->andWhere("p.type = :type")
		->setParameter("type", FeedEntryType::POST, Type::STRING)
		->andWhere("u.privacyLevel = :level")
		->setParameter("level", PrivacyLevel::PUBLIC, Type::STRING);
}

function create_users_query($query): QueryBuilder {
	return EntityManager::instance()->getRepository(User::class)->createQueryBuilder("u")
		->where("u.displayName LIKE :q OR u.username LIKE :q OR u.bio LIKE :q")
		->setParameter("q", "%" . $query . "%")
		->andWhere("u.privacyLevel = :level")
		->setParameter("level", PrivacyLevel::PUBLIC, Type::STRING);
}

create_route("/search", function () {
	$query = isset($_GET["query"]) && !Util::isEmpty(trim($_GET["query"])) ? trim($_GET["query"]) : null;
	$type = isset($_GET["type"]) && !Util::isEmpty(trim($_GET["type"])) ? trim($_GET["type"]) : "posts";
	$page = isset($_GET["page"]) && !Util::isEmpty(trim($_GET["page"])) && is_numeric($_GET["page"]) && (int)$_GET["page"] > 0 ? (int)$_GET["page"] : 1;
	$results = [];
	$num = 0;

	if ($query) {
		# Fetch total number of results
		$num = ($type == "posts" ? create_posts_query($query) : create_users_query($query))
			->select("count(u.id)")
			->getQuery()
			->getResult()[0][1];

		# Fetch actual results
		if ($num > 0) {
			$itemsPerPage = 10;

			if ($type == "posts") {
				/**
				 * @var FeedEntry[] $result
				 */
				$result = create_posts_query($query)
					->setFirstResult(($page - 1) * $itemsPerPage)
					->setMaxResults($itemsPerPage)
					->getQuery()
					->getResult();

				foreach ($result as $feedEntry) {
					$user = $feedEntry->getUser();

					if (!$feedEntry->mayView() || !$user->mayView()) continue;

					array_push($results, [
						"post" => $feedEntry,
						"user" => $user
					]);
				}
			} else if ($type == "users") {
				/**
				 * @var User[] $result
				 */
				$result = create_users_query($query)
					->setFirstResult(($page - 1) * $itemsPerPage)
					->setMaxResults($itemsPerPage)
					->getQuery()
					->getResult();

				foreach ($result as $user) {
					if (!$user->mayView()) continue;

					array_push($results, $user);
				}
			}
		}
	}

	return twig_render("pages/search/" . $type . ".html.twig", [
		"title" => "Search" . (!is_null($query) ? ": \"" . Util::sanatizeString($_GET["query"]) . "\"" : ""),
		"page" => $page,
		"type" => $type,
		"query" => $query,
		"results" => $results,
		"num" => $num
	]);
});
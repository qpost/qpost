<?php

use qpost\Account\User;
use qpost\Cache\CacheHandler;
use qpost\Database\Database;
use qpost\Feed\FeedEntry;
use qpost\Util\Util;

$app->get("/search",function(){
	$query = isset($_GET["query"]) && !Util::isEmpty(trim($_GET["query"])) ? trim($_GET["query"]) : null;
	$type = isset($_GET["type"]) && !Util::isEmpty(trim($_GET["type"])) ? trim($_GET["type"]) : "posts";
	$page = isset($_GET["page"]) && !Util::isEmpty(trim($_GET["page"])) && is_numeric($_GET["page"]) && (int)$_GET["page"] > 0 ? (int)$_GET["page"] : 1;
	$results = [];

	if ($query) {
		$num = 0;
		$mysqli = Database::Instance()->get();

		$q = "%" . $query . "%";

		# Fetch total number of results
		if ($type == "posts") {
			$n = "searchnum_posts_" . $query;

			if (CacheHandler::existsInCache($n)) {
				$num = CacheHandler::getFromCache($n);
			} else {
				$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `feed` AS p INNER JOIN `users` AS u ON p.user = u.id WHERE p.`post` IS NULL AND (p.`text` LIKE ? OR u.`displayName` LIKE ? OR u.`username` LIKE ?) AND p.`type` = 'POST' AND u.`privacy.level` = 'PUBLIC'");
				$stmt->bind_param("sss", $q, $q, $q);
				if ($stmt->execute()) {
					$result = $stmt->get_result();

					if ($result->num_rows) {
						$row = $result->fetch_assoc();

						$num = $row["count"];

						CacheHandler::setToCache($n, $num, 2 * 60);
					}
				}
				$stmt->close();
			}
		} else if ($type == "users") {
			$n = "searchnum_users_" . $query;

			if (CacheHandler::existsInCache($n)) {
				$num = CacheHandler::getFromCache($n);
			} else {
				$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `users` AS u WHERE (u.`displayName` LIKE ? OR u.`username` LIKE ? OR u.`bio` LIKE ?) AND u.`privacy.level` != 'CLOSED'");
				$stmt->bind_param("sss", $q, $q, $q);
				if ($stmt->execute()) {
					$result = $stmt->get_result();

					if ($result->num_rows) {
						$row = $result->fetch_assoc();

						$num = $row["count"];

						CacheHandler::setToCache($n, $num, 2 * 60);
					}
				}
				$stmt->close();
			}
		}

		# Fetch actual results
		if ($num > 0) {
			$itemsPerPage = 10;

			$mysqli = Database::Instance()->get();

			if ($type == "posts") {
				$stmt = $mysqli->prepare("SELECT p.`id` AS `postID`,u.`id` AS `userID` FROM `feed` AS p INNER JOIN `users` AS u ON p.user = u.id WHERE p.`post` IS NULL AND (p.`text` LIKE ? OR u.`displayName` LIKE ? OR u.`username` LIKE ?) AND p.`type` = 'POST' AND u.`privacy.level` = 'PUBLIC' ORDER BY p.`time` DESC LIMIT " . (($page - 1) * $itemsPerPage) . " , " . $itemsPerPage);
				$stmt->bind_param("sss", $q, $q, $q);
				if ($stmt->execute()) {
					$result = $stmt->get_result();

					if ($result->num_rows) {
						while ($row = $result->fetch_assoc()) {
							$f = FeedEntry::getEntryById($row["postID"]);
							$u = User::getUserById($row["userID"]);

							if (!$f->mayView() || !$u->mayView()) continue;

							array_push($results, [
								"post" => $f,
								"user" => $u
							]);
						}

						CacheHandler::setToCache($n, $num, 2 * 60);
					}
				}
				$stmt->close();
			} else if ($type == "users") {
				$stmt = $mysqli->prepare("SELECT u.`id` FROM `users` AS u WHERE (u.`displayName` LIKE ? OR u.`username` LIKE ? OR u.`bio` LIKE ?) AND u.`privacy.level` != 'CLOSED' LIMIT " . (($page - 1) * $itemsPerPage) . " , " . $itemsPerPage);
				$stmt->bind_param("sss", $q, $q, $q);
				if ($stmt->execute()) {
					$result = $stmt->get_result();

					if ($result->num_rows) {
						while ($row = $result->fetch_assoc()) {
							$u = User::getUserById($row["id"]);

							if (!$u->mayView()) continue;

							array_push($results, $u);
						}
					}
				}
				$stmt->close();
			}
		}
	}

	return twig_render("pages/search/" . $type . ".html.twig", [
		"title" => "Search" . (!is_null($query) ? ": \"" . Util::sanatizeString($_GET["query"]) . "\"" : ""),
		"page" => $page,
		"type" => $type,
		"query" => $query,
		"results" => $results
	]);
});
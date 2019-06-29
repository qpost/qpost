<?php

use qpost\Cache\CacheHandler;
use qpost\Database\Database;
use qpost\Util\Util;

$app->bind("/notifications/:page",function($params){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$user = Util::getCurrentUser();
	if (is_null($user)) return $this->reroute("/login");

	$page = is_numeric($params["page"]) && (int)$params["page"] > 0 ? (int)$params["page"] : 1;
	$num = 0;
	$n = "totalNotifications_" . $user->getId();
	$mysqli = Database::Instance()->get();
	$uID = $user->getId();
	$itemsPerPage = 30;

	if (CacheHandler::existsInCache($n)) {
		$num = CacheHandler::getFromCache($n);
	} else {
		$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `notifications` WHERE `user` = ?");
		$stmt->bind_param("i", $uID);
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

	$notifications = [];

	if ($num > 0) {
		$markAsRead = [];

		$stmt = $mysqli->prepare("SELECT * FROM `notifications` WHERE `user` = ? ORDER BY `time` DESC LIMIT " . (($page - 1) * $itemsPerPage) . " , " . $itemsPerPage);
		$stmt->bind_param("i", $uID);
		if ($stmt->execute()) {
			$result = $stmt->get_result();

			if ($result->num_rows) {
				while ($row = $result->fetch_assoc()) {
					if ($row["seen"] == false) {
						array_push($markAsRead, $row["id"]);
					}

					array_push($notifications, $row);
				}
			}
		}
		$stmt->close();

		if (count($markAsRead) > 0) {
			$user->markNotificationsAsRead($markAsRead);
		}
	}

	if(!is_null($user)){
		$notifs = $user->getUnreadNotifications();

		return twig_render("pages/notifications/page.html.twig", [
			"title" => "Notifications (" . $notifs . ")",
			"nav" => NAV_NOTIFICATIONS,
			"page" => $page,
			"notifications" => $notifications,
			"currentPage" => $page,
			"itemsPerPage" => $itemsPerPage,
			"num" => $num
		]);
	}

	return $this->reroute("/");
});

$app->bind("/notifications",function(){
	return $this->reroute("/notifications/1");
});
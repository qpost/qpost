<?php

use qpost\Account\User;
use qpost\Database\Database;
use qpost\Util\Util;

$app->bind("/requests",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$mysqli = Database::Instance()->get();

	if (isset($_POST["action"]) && isset($_POST["user"]) && is_numeric($_POST["user"])) {
		$user = User::getUserById($_POST["user"]);

		if (!is_null($user)) {
			if (!$user->isFollowing(Util::getCurrentUser()) && $user->hasSentFollowRequest(Util::getCurrentUser())) {
				if ($_POST["action"] == "accept") {
					$user->follow(Util::getCurrentUser());
				} else if ($_POST["action"] == "deny") {
					$u1 = Util::getCurrentUser()->getId();
					$u2 = $user->getId();

					$stmt = $mysqli->prepare("DELETE FROM `follow_requests` WHERE `follower` = ? AND `following` = ?");
					$stmt->bind_param("ii", $u2, $u1);
					$stmt->execute();
					$stmt->close();

					Util::getCurrentUser()->reloadOpenFollowRequests();
				}
			}
		}
	}

	$openRequests = [];

	$user = Util::getCurrentUser();
	$uid = $user->getId();
	if ($user->getOpenFollowRequests() > 0) {
		$stmt = $mysqli->prepare("SELECT u.`id` FROM `follow_requests` AS f INNER JOIN `users` AS u ON f.`follower` = u.`id` WHERE f.`following` = ? ORDER BY f.`time` DESC");
		$stmt->bind_param("i", $uid);
		if ($stmt->execute()) {
			$result = $stmt->get_result();

			if ($result->num_rows) {
				while ($row = $result->fetch_assoc()) {
					$u = User::getUserById($row["id"]);

					array_push($openRequests, $u);
				}
			}
		}
	}

	return twig_render("pages/requests.html.twig", [
		"title" => "Follow requests (" . $user->getOpenFollowRequests() . ")",
		"openRequests" => $openRequests
	]);
});
<?php

use Lime\App;
use qpost\Account\PrivacyLevel;
use qpost\Account\ProfileViewStatus;
use qpost\Account\User;
use qpost\Cache\CacheHandler;
use qpost\Database\Database;
use qpost\Feed\FeedEntry;
use qpost\Util\Util;

function find_user($query): ?User {
	if (!Util::isEmpty($query)) {
		$user = User::getUserByUsername($query);
		if (is_null($user) && is_numeric($query)) $user = @User::getUserById((int)$query);

		return $user;
	}

	return null;
}

function profile_view_status(User $user): int {
	$currentUser = Util::getCurrentUser();

	if ($user->isSuspended()) {
		return ProfileViewStatus::SUSPENDED;
	}

	if (!$user->isEmailActivated()) {
		return ProfileViewStatus::EMAIL_NOT_ACTIVATED;
	}

	if (Util::isLoggedIn() && $currentUser->isBlocked($user)) {
		return ProfileViewStatus::BLOCKED;
	}

	if ($user->getPrivacyLevel() == PrivacyLevel::CLOSED && (!Util::isLoggedIn() || $currentUser->getId() != $user->getId())) {
		return ProfileViewStatus::CLOSED;
	}

	if ($user->getPrivacyLevel() == PrivacyLevel::PRIVATE && (!Util::isLoggedIn() || ($currentUser->getId() != $user->getId() && !$currentUser->isFollowing($user)))) {
		return ProfileViewStatus::PRIVATE;
	}

	return ProfileViewStatus::OK;
}

function profile_handle_redirect(App $app, User $user): bool {
	if (profile_view_status($user) != ProfileViewStatus::OK) {
		$app->reroute("/" . $user->getUsername());
		return false;
	}

	return true;
}

function profile_fetch_feed_num(User $user): int {
	$mysqli = Database::Instance()->get();
	$num = 0;
	$uID = $user->getId();
	$n = "profile_feed_num_" . $uID;

	if (CacheHandler::existsInCache($n)) {
		$num = CacheHandler::getFromCache($n);
	} else {
		$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `feed` WHERE ((`post` IS NULL AND `type` = 'POST') OR (`type` != 'POST')) AND `user` = ?");
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

	return $num;
}

/**
 * @param User $user
 * @param int $currentPage
 * @return FeedEntry[]
 */
function profile_fetch_feed(User $user, int $currentPage) {
	$mysqli = Database::Instance()->get();
	$itemsPerPage = 40;

	$uID = $user->getId();
	$num = profile_fetch_feed_num($user);

	$feedEntries = [];

	if ($num > 0) {
		$stmt = $mysqli->prepare("SELECT `id` FROM `feed` WHERE ((`post` IS NULL AND `type` = 'POST') OR (`type` != 'POST')) AND `user` = ? ORDER BY `time` DESC LIMIT " . (($currentPage - 1) * $itemsPerPage) . " , " . $itemsPerPage);
		$stmt->bind_param("i", $uID);
		if ($stmt->execute()) {
			$result = $stmt->get_result();

			if ($result->num_rows) {
				while ($row = $result->fetch_assoc()) {
					array_push($feedEntries, FeedEntry::getEntryById($row["id"]));
				}
			}
		}
		$stmt->close();
	}

	return $feedEntries;
}

/**
 * @param User $user
 * @param int $currentPage
 * @return User[]
 */
function profile_fetch_followers(User $user, int $currentPage) {
	$mysqli = Database::Instance()->get();
	$itemsPerPage = 40;

	$num = $user->getFollowers();
	$uID = $user->getId();

	$users = [];

	if ($num > 0) {
		$users = [];

		$stmt = $mysqli->prepare("SELECT u.`id` FROM `follows` AS f INNER JOIN `users` AS u ON f.`follower` = u.`id` WHERE f.`following` = ? ORDER BY f.`time` DESC LIMIT " . (($currentPage - 1) * $itemsPerPage) . " , " . $itemsPerPage);
		$stmt->bind_param("i", $uID);
		if ($stmt->execute()) {
			$result = $stmt->get_result();

			if ($result->num_rows) {
				while ($row = $result->fetch_assoc()) {
					$u = User::getUserById($row["id"]);

					$user->cacheFollower($u->getId());

					if (!$u->mayView()) continue;

					array_push($users, $u);
				}
			}
		}
		$stmt->close();
	}

	return $users;
}

/**
 * @param User $user
 * @param int $currentPage
 * @return User[]
 */
function profile_fetch_following(User $user, int $currentPage) {
	$mysqli = Database::Instance()->get();
	$itemsPerPage = 40;

	$num = $user->getFollowers();
	$uID = $user->getId();

	$users = [];

	if ($num > 0) {
		$users = [];

		$stmt = $mysqli->prepare("SELECT u.`id` FROM `follows` AS f INNER JOIN `users` AS u ON f.`following` = u.`id` WHERE f.`follower` = ? ORDER BY f.`time` DESC LIMIT " . (($currentPage - 1) * $itemsPerPage) . " , " . $itemsPerPage);
		$stmt->bind_param("i", $uID);
		if ($stmt->execute()) {
			$result = $stmt->get_result();

			if ($result->num_rows) {
				while ($row = $result->fetch_assoc()) {
					$u = User::getUserById($row["id"]);

					$u->cacheFollower($uID);

					if (!$u->mayView()) continue;

					array_push($users, $u);
				}
			}
		}
		$stmt->close();
	}

	return $users;
}

$app->bind("/:query/following",function($params){
	$query = $params["query"];
	$page = 1;
	$user = find_user($query);

	if (!is_null($user)) {
		if ($query !== $user->getUsername()) {
			return $this->reroute("/" . $user->getUsername() . "/following");
		}

		if (profile_handle_redirect($this, $user)) {
			return twig_render("pages/profile/following.html.twig", [
				"title" => "People followed by " . $user->getUsername(),
				"nav" => Util::isLoggedIn() && $user->getId() == Util::getCurrentUser()->getId() ? NAV_PROFILE : null,
				"user" => $user,
				"socialImage" => $user->getAvatarURL(),
				"showProfile" => true,
				"profileTab" => "FOLLOWING",
				"currentPage" => $page,
				"description" => $user->getBio(),
				"users" => profile_fetch_following($user, $page)
			]);
		}
	}
});

$app->bind("/:query/following/:page",function($params){
	$query = $params["query"];
	$page = is_numeric($params["page"]) && (int)$params["page"] > 0 ? (int)$params["page"] : 1;
	$user = find_user($query);

	if (!is_null($user)) {
		if ($query !== $user->getUsername()) {
			return $this->reroute("/" . $user->getUsername() . "/following/" . $page);
		}

		if (profile_handle_redirect($this, $user)) {
			return twig_render("pages/profile/following.html.twig", [
				"title" => "People followed by " . $user->getUsername(),
				"nav" => Util::isLoggedIn() && $user->getId() == Util::getCurrentUser()->getId() ? NAV_PROFILE : null,
				"user" => $user,
				"socialImage" => $user->getAvatarURL(),
				"showProfile" => true,
				"profileTab" => "FOLLOWING",
				"currentPage" => $page,
				"description" => $user->getBio(),
				"users" => profile_fetch_following($user, $page)
			]);
		}
	}
});

$app->bind("/:query/followers",function($params){
	$query = $params["query"];
	$page = 1;
	$user = find_user($query);

	if (!is_null($user)) {
		if ($query !== $user->getUsername()) {
			return $this->reroute("/" . $user->getUsername() . "/followers");
		}

		if (profile_handle_redirect($this, $user)) {
			return twig_render("pages/profile/followers.html.twig", [
				"title" => "People following " . $user->getUsername(),
				"nav" => Util::isLoggedIn() && $user->getId() == Util::getCurrentUser()->getId() ? NAV_PROFILE : null,
				"user" => $user,
				"socialImage" => $user->getAvatarURL(),
				"showProfile" => true,
				"profileTab" => "FOLLOWERS",
				"currentPage" => $page,
				"description" => $user->getBio(),
				"users" => profile_fetch_followers($user, $page)
			]);
		}
	}
});

$app->bind("/:query/followers/:page",function($params){
	$query = $params["query"];
	$page = is_numeric($params["page"]) && (int)$params["page"] > 0 ? (int)$params["page"] : 1;
	$user = find_user($query);

	if (!is_null($user)) {
		if ($query !== $user->getUsername()) {
			return $this->reroute("/" . $user->getUsername() . "/followers/" . $page);
		}

		if (profile_handle_redirect($this, $user)) {
			return twig_render("pages/profile/followers.html.twig", [
				"title" => "People following " . $user->getUsername(),
				"nav" => Util::isLoggedIn() && $user->getId() == Util::getCurrentUser()->getId() ? NAV_PROFILE : null,
				"user" => $user,
				"socialImage" => $user->getAvatarURL(),
				"showProfile" => true,
				"profileTab" => "FOLLOWERS",
				"currentPage" => $page,
				"description" => $user->getBio(),
				"users" => profile_fetch_followers($user, $page)
			]);
		}
	}
});

$app->bind("/:query",function($params){
	$query = $params["query"];
	$page = 1;
	$user = find_user($query);

	if (!is_null($user)) {
		$viewStatus = profile_view_status($user);

		if ($viewStatus != ProfileViewStatus::EMAIL_NOT_ACTIVATED) {
			if ($query !== $user->getUsername()) {
				return $this->reroute("/" . $user->getUsername());
			}

			switch ($viewStatus) {
				case ProfileViewStatus::BLOCKED:
					return twig_render("pages/profile/blocked.html.twig", [
						"user" => $user,
						"socialImage" => $user->getAvatarURL(),
						"preventStatusModal" => true
					]);
				case ProfileViewStatus::PRIVATE:
					return twig_render("pages/profile/private.html.twig", [
						"user" => $user,
						"socialImage" => $user->getAvatarURL(),
						"preventStatusModal" => true
					]);
				case ProfileViewStatus::CLOSED:
					return twig_render("pages/profile/closed.html.twig", [
						"user" => $user,
						"socialImage" => $user->getAvatarURL(),
						"preventStatusModal" => true
					]);
				case ProfileViewStatus::SUSPENDED:
					return twig_render("pages/profile/suspended.html.twig", [
						"title" => "Account suspended",
						"user" => $user,
						"preventStatusModal" => true
					]);
				default:
					return twig_render("pages/profile/feed.html.twig", [
						"title" => $user->getDisplayName() . " (@" . $user->getUsername() . ")",
						"nav" => Util::isLoggedIn() && $user->getId() == Util::getCurrentUser()->getId() ? NAV_PROFILE : null,
						"user" => $user,
						"socialImage" => $user->getAvatarURL(),
						"showProfile" => true,
						"profileTab" => "FEED",
						"currentPage" => $page,
						"description" => $user->getBio(),
						"posts" => profile_fetch_feed($user, $page),
						"num" => profile_fetch_feed_num($user)
					]);
			}
		}
	}
});

$app->bind("/:query/:page",function($params){
	$query = $params["query"];
	$page = is_numeric($params["page"]) && (int)$params["page"] > 0 ? (int)$params["page"] : 1;
	$user = find_user($query);

	if (!is_null($user)) {
		if ($query !== $user->getUsername()) {
			return $this->reroute("/" . $user->getUsername() . "/" . $page);
		}

		if (profile_handle_redirect($this, $user)) {
			return twig_render("pages/profile/feed.html.twig", [
				"title" => $user->getDisplayName() . " (@" . $user->getUsername() . ")",
				"nav" => Util::isLoggedIn() && $user->getId() == Util::getCurrentUser()->getId() ? NAV_PROFILE : null,
				"user" => $user,
				"socialImage" => $user->getAvatarURL(),
				"showProfile" => true,
				"profileTab" => "FEED",
				"currentPage" => $page,
				"description" => $user->getBio(),
				"posts" => profile_fetch_feed($user, $page),
				"num" => profile_fetch_feed_num($user)
			]);
		}
	}
});
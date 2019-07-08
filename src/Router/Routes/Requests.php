<?php

namespace qpost\Router;

use qpost\Account\Follower;
use qpost\Account\FollowRequest;
use qpost\Account\User;
use qpost\Database\EntityManager;
use qpost\Util\Util;

create_route("/requests", function () {
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$entityManager = EntityManager::instance();
	$currentUser = Util::getCurrentUser();

	if (isset($_POST["action"]) && isset($_POST["user"]) && is_numeric($_POST["user"])) {
		$user = User::getUser($_POST["user"]);

		if (!is_null($user)) {
			if (!Follower::isFollowing($user, $currentUser) && FollowRequest::hasSentFollowRequest($user, $currentUser)) {
				if ($_POST["action"] == "accept") {
					Follower::follow($user, $currentUser);
				} else if ($_POST["action"] == "deny") {
					$followRequest = $entityManager->getRepository(FollowRequest::class)->findOneBy([
						"from" => $user,
						"to" => $currentUser
					]);

					$entityManager->remove($followRequest);
					$entityManager->flush();
				}
			}
		}
	}

	/**
	 * @var FollowRequest[] $openRequests
	 */
	$openRequests = [];

	if ($currentUser->getOpenRequestsCount() > 0) {
		$openRequests = $entityManager->getRepository(FollowRequest::class)->findBy([
			"to" => $currentUser
		], [
			"time" => "DESC"
		]);
	}

	return twig_render("pages/requests.html.twig", [
		"title" => "Follow requests (" . $currentUser->getOpenRequestsCount() . ")",
		"openRequests" => $openRequests
	]);
});
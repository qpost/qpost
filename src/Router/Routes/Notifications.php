<?php

namespace qpost\Router;

use Doctrine\Common\Collections\Criteria;
use qpost\Database\EntityManager;
use qpost\Feed\Notification;
use qpost\Navigation\NavPoint;
use qpost\Util\Util;

create_route("/notifications/:page", function ($params) {
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$user = Util::getCurrentUser();
	if (is_null($user)) return $this->reroute("/login");

	$page = is_numeric($params["page"]) && (int)$params["page"] > 0 ? (int)$params["page"] : 1;
	$entityManager = EntityManager::instance();
	$uID = $user->getId();
	$itemsPerPage = 30;

	$num = $entityManager->getRepository(Notification::class)->count([
		"user" => $user
	]);

	/**
	 * @var Notification[] $notifications
	 */
	$notifications = [];

	if ($num > 0) {
		$criteria = Criteria::create();
		$expr = Criteria::expr();

		$notifications = $entityManager->getRepository(Notification::class)->matching(
			$criteria->where($expr->eq("user", $user))
				->setFirstResult(($page - 1) * $itemsPerPage)
				->setMaxResults($itemsPerPage)
		);
	}

	$notifs = $user->getUnreadNotifications();

	$output = twig_render("pages/notifications/page.html.twig", [
		"title" => "Notifications (" . $notifs . ")",
		"nav" => NavPoint::NOTIFICATIONS,
		"page" => $page,
		"notifications" => $notifications,
		"currentPage" => $page,
		"itemsPerPage" => $itemsPerPage,
		"num" => $num
	]);

	foreach ($notifications as $notification) {
		if (!$notification->isSeen()) {
			$notification->setSeen(true);
			$entityManager->persist($notification);
		}
	}

	$entityManager->flush();

	return $output;
});

create_route("/notifications", function () {
	return $this->reroute("/notifications/1");
});
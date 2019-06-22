<?php

use qpost\Util\Util;

$app->bind("/notifications/:page",function($params){
	$user = Util::getCurrentUser();
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$page = is_numeric($params["page"]) && (int)$params["page"] > 0 ? (int)$params["page"] : 1;

	if(!is_null($user)){
		$notifs = $user->getUnreadNotifications();

		return $this->render("views:Notifications.php with views:Layout.php",[
			"title" => "Notifications (" . $notifs . ")",
			"nav" => NAV_NOTIFICATIONS,
			"currentPage" => $page
		]);
	}
});

$app->bind("/notifications",function(){
	$user = Util::getCurrentUser();
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$page = 1;

	if(!is_null($user)){
		$notifs = $user->getUnreadNotifications();

		return $this->render("views:Notifications.php with views:Layout.php",[
			"title" => "Notifications (" . $notifs . ")",
			"nav" => NAV_NOTIFICATIONS,
			"currentPage" => $page
		]);
	}
});
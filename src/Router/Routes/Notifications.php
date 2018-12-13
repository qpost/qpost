<?php

$app->bind("/notifications/:page",function($params){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$page = is_numeric($params["page"]) && (int)$params["page"] > 0 ? (int)$params["page"] : 1;

	return $this->render("views:Notifications.php with views:Layout.php",[
		"title" => "Notifications (" . Util::getCurrentUser()->getUnreadNotifications() . ")",
		"nav" => NAV_NOTIFICATIONS,
		"currentPage" => $page
	]);
});

$app->bind("/notifications",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$page = 1;

	return $this->render("views:Notifications.php with views:Layout.php",[
		"title" => "Notifications (" . Util::getCurrentUser()->getUnreadNotifications() . ")",
		"nav" => NAV_NOTIFICATIONS,
		"currentPage" => $page
	]);
});
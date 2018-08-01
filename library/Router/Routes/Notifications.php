<?php

$app->bind("/notifications/:page",function($params){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$page = is_numeric($params["page"]) && (int)$params["page"] > 0 ? (int)$params["page"] : 1;

	$data = array(
		"title" => "Notifications (" . Util::getCurrentUser()->getUnreadNotifications() . ")",
		"nav" => NAV_NOTIFICATIONS,
		"subtitle" => "Notifications (" . Util::getCurrentUser()->getUnreadNotifications() . ")",
		"currentPage" => $page
	);

	return $this->render("views:Notifications.php with views:Layout.php",$data);
});

$app->bind("/notifications",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$page = 1;

	$data = array(
		"title" => "Notifications (" . Util::getCurrentUser()->getUnreadNotifications() . ")",
		"nav" => NAV_NOTIFICATIONS,
		"subtitle" => "Notifications (" . Util::getCurrentUser()->getUnreadNotifications() . ")",
		"currentPage" => $page
	);

	return $this->render("views:Notifications.php with views:Layout.php",$data);
});
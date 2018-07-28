<?php

$app->bind("/notifications",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$data = array(
		"title" => "Notifications",
		"nav" => NAV_NOTIFICATIONS,
		"subtitle" => "Notifications"
	);

	return $this->render("views:Notifications.php with views:Layout.php",$data);
});
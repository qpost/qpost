<?php

$app->bind("/messages",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	return $this->render("views:Messages.php with views:Layout.php",[
		"title" => "Messages (" . Util::getCurrentUser()->getUnreadMessages() . ")",
		"nav" => NAV_MESSAGES
	]);
});
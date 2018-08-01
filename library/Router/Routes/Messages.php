<?php

$app->bind("/messages",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$data = array(
		"title" => "Messages (" . Util::getCurrentUser()->getUnreadMessages() . ")",
		"nav" => NAV_MESSAGES,
		"subtitle" => "Messages (" . Util::getCurrentUser()->getUnreadMessages() . ")"
	);

	return $this->render("views:Messages.php with views:Layout.php",$data);
});
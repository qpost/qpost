<?php

$app->bind("/messages",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$data = array(
		"title" => "Messages",
		"nav" => NAV_MESSAGES,
		"subtitle" => "Messages"
	);

	return $this->render("views:Messages.php with views:Layout.php",$data);
});
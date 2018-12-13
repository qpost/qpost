<?php

$app->bind("/requests",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	return $this->render("views:Requests.php with views:Layout.php",[
		"title" => "Follow requests (" . Util::getCurrentUser()->getOpenFollowRequests() . ")"
	]);
});
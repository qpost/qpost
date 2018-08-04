<?php

$app->bind("/requests",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$data = array(
		"title" => "Follow requests (" . Util::getCurrentUser()->getOpenFollowRequests() . ")",
		"subtitle" => "Follow requests (" . Util::getCurrentUser()->getOpenFollowRequests() . ")",
	);

	return $this->render("views:Requests.php with views:Layout.php",$data);
});
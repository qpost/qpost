<?php

$app->bind("/socialnet",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$page = 1;

	$data = array(
		"title" => "Social Net",
		"subtitle" => "Social Net"
	);

	return $this->render("views:SocialNet.php with views:Layout.php",$data);
});
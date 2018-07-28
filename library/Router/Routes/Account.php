<?php

$app->bind("/account",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$data = array(
		"title" => "Account",
		"nav" => NAV_ACCOUNT,
		"subtitle" => "Account"
	);

	return $this->render("views:Account.php with views:Layout.php",$data);
});
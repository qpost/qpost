<?php

$app->bind("/account",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$data = array(
		"title" => "Account",
		"nav" => NAV_ACCOUNT,
		"subtitle" => "My account",
		"showAccountNav" => true,
		"accountNav" => ACCOUNT_NAV_HOME
	);

	return $this->render("views:Account/Home.php with views:Layout.php",$data);
});

$app->bind("/account/privacy",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$data = array(
		"title" => "Privacy",
		"nav" => NAV_ACCOUNT,
		"subtitle" => "Privacy settings",
		"showAccountNav" => true,
		"accountNav" => ACCOUNT_NAV_PRIVACY
	);

	return $this->render("views:Account/Privacy.php with views:Layout.php",$data);
});
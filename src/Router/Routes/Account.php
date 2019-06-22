<?php

use qpost\Util\Util;

$app->bind("/account",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	return $this->render("views:Account/Home.php with views:Layout.php",[
		"title" => "Account",
		"nav" => NAV_ACCOUNT,
		"showAccountNav" => true,
		"accountNav" => ACCOUNT_NAV_HOME
	]);
});

$app->bind("/account/privacy",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	return $this->render("views:Account/Privacy.php with views:Layout.php",[
		"title" => "Privacy",
		"nav" => NAV_ACCOUNT,
		"showAccountNav" => true,
		"accountNav" => ACCOUNT_NAV_PRIVACY
	]);
});

$app->bind("/account/sessions",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	return $this->render("views:Account/Sessions.php with views:Layout.php",[
		"title" => "Active sessions",
		"nav" => NAV_ACCOUNT,
		"showAccountNav" => true,
		"accountNav" => ACCOUNT_NAV_SESSIONS
	]);
});

$app->bind("/account/change-password",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");
	if(Util::getCurrentUser()->isGigadriveLinked()) return $this->reroute("/account");

	return $this->render("views:Account/ChangePassword.php with views:Layout.php",[
		"title" => "Change your password",
		"nav" => NAV_ACCOUNT,
		"showAccountNav" => true,
		"accountNav" => ACCOUNT_NAV_CHANGE_PASSWORD
	]);
});

$app->bind("/account/verify-email",function(){
    return $this->render("views:Account/VerifyEmail.php with views:Layout.php",[
		"title" => "Verify your Email address"
	]);
});
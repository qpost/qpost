<?php

$app->bind("/account",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$data = array(
		"title" => "Account",
		"nav" => NAV_ACCOUNT,
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
		"showAccountNav" => true,
		"accountNav" => ACCOUNT_NAV_PRIVACY
	);

	return $this->render("views:Account/Privacy.php with views:Layout.php",$data);
});

$app->bind("/account/sessions",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$data = array(
		"title" => "Active sessions",
		"nav" => NAV_ACCOUNT,
		"showAccountNav" => true,
		"accountNav" => ACCOUNT_NAV_SESSIONS
	);

	return $this->render("views:Account/Sessions.php with views:Layout.php",$data);
});

$app->bind("/account/change-password",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");
	if(Util::getCurrentUser()->isGigadriveLinked()) return $this->reroute("/account");

	$data = array(
		"title" => "Change your password",
		"nav" => NAV_ACCOUNT,
		"showAccountNav" => true,
		"accountNav" => ACCOUNT_NAV_CHANGE_PASSWORD
	);

	return $this->render("views:Account/ChangePassword.php with views:Layout.php",$data);
});

$app->bind("/account/verify-email",function(){
    $data = array(
        "title" => "Verify your Email address"
    );

    return $this->render("views:Account/VerifyEmail.php with views:Layout.php",$data);
});
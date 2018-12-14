<?php

$app->bind("/",function(){
	if(!Util::isLoggedIn()){
		return $this->render("views:Home.php with views:HomeLayout.php");
	} else {
		return $this->render("views:HomeFeed.php with views:Layout.php",[
			"title" => "Home",
			"nav" => NAV_HOME,
			"hideFooter" => true
		]);
	}
});
<?php

$app->bind("/edit",function($params){
	if(Util::isLoggedIn()){
		return $this->render("views:Edit.php with views:Layout.php",[
			"title" => "Edit your profile",
			"nav" => NAV_PROFILE
		]);
	} else {
		return $this->reroute("/");
	}
});
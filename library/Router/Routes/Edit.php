<?php

$app->bind("/edit",function($params){
	if(Util::isLoggedIn()){
		$data = array(
			"title" => "Edit your profile",
			"nav" => NAV_PROFILE,
			"subtitle" => "Edit your profile"
		);
	
		return $this->render("views:Edit.php with views:Layout.php",$data);
	} else {
		return $this->reroute("/");
	}
});
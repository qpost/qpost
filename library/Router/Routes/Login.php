<?php

$app->bind("/login",function(){
	if(!Util::isLoggedIn()){
		$data = array(
			"title" => "Login"
		);
	
		return $this->render("views:Login.php with views:Layout.php",$data);
	} else {
		return $this->reroute("/");
	}
});
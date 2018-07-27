<?php

$app->bind("/",function(){
	if(!Util::isLoggedIn()){
		$data = array(
			"title" => "Home"
		);
	
		return $this->render("views:Home.php with views:Layout.php",$data);
	} else {
		$data = array(
			"title" => "Home"
		);
	
		return $this->render("views:HomeFeed.php with views:Layout.php",$data);
	}
});
<?php

$app->bind("/logout",function(){
	unset($_SESSION["id"]);

	if(Util::isLoggedIn()){
		$token = Token::getTokenById($_COOKIE["sesstoken"]);
	
		if(!is_null($token)){
			$token->expire();
			Util::unsetCookie("sesstoken");
		}
	}

	return $this->reroute("/");
});
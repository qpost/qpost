<?php

namespace qpost\Router;

use qpost\Util\Util;

create_route("/logout", function () {
	unset($_SESSION["id"]);

	if(Util::isLoggedIn()){
		$token = Util::getCurrentToken();
	
		if(!is_null($token)){
			$token->expire();
			Util::unsetCookie("sesstoken");
		}
	}

	return $this->reroute("/");
});
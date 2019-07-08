<?php

namespace qpost\Router;

use qpost\Navigation\NavPoint;
use qpost\Util\Util;

create_route("/delete", function () {
    if(Util::isLoggedIn()){
        $user = Util::getCurrentUser();
		$token = Util::getCurrentToken();

        if(isset($_POST["confirmation"]) && $_POST["confirmation"] == "true" && !is_null($user) && !is_null($token)){
            $token->expire();
            Util::unsetCookie("sesstoken");
            
            $user->deleteAccount();

            return $this->reroute("/?msg=accountDeleted");
        } else {
			return twig_render("pages/account/delete.html.twig", [
				"title" => "Delete your account",
				"nav" => NavPoint::ACCOUNT
			]);
        }
	} else {
		return $this->reroute("/");
	}
});
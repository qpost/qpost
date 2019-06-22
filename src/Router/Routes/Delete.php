<?php

use qpost\Account\Token;
use qpost\Util\Util;

$app->bind("/delete",function(){
    if(Util::isLoggedIn()){
        $user = Util::getCurrentUser();
        $token = Token::getTokenById($_COOKIE["sesstoken"]);

        if(isset($_POST["confirmation"]) && $_POST["confirmation"] == "true" && !is_null($user) && !is_null($token)){
            $token->expire();
            Util::unsetCookie("sesstoken");
            
            $user->deleteAccount();

            return $this->reroute("/?msg=accountDeleted");
        } else {
            return $this->render("views:Account/Delete.php with views:Layout.php",[
                "title" => "Delete your account",
                "nav" => NAV_ACCOUNT
            ]);
        }
	} else {
		return $this->reroute("/");
	}
});
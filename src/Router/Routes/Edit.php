<?php

use qpost\Util\Util;

$app->bind("/edit", function () {
	if(Util::isLoggedIn()){
		return $this->render("views:Edit.php with views:Layout.php",[
			"title" => "Edit your profile",
			"nav" => NAV_PROFILE
		]);
	} else {
		return $this->reroute("/");
	}
});
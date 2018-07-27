<?php

$app->bind("/logout",function(){
	unset($_SESSION["id"]);

	return $this->reroute("/");
});
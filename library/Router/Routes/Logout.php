<?php

$app->bind("/logout",function(){
	unlink($_SESSION["id"]);

	return $this->reroute("/");
});
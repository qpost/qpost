<?php

$app->bind("/login",function(){
	if(isset($_GET["id"])){
		// DEBUG ROUTE FOR FORCING LOGIN

		$_SESSION["id"] = (int)$_GET["id"];
		return $this->reroute("/");
	} else {
		// TODO
	}
});
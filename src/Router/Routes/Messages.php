<?php

use qpost\Util\Util;

$app->bind("/messages",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	return twig_render("pages/messages.html.twig", [
		"title" => "Messages (" . Util::getCurrentUser()->getUnreadMessages() . ")",
		"nav" => NAV_MESSAGES
	]);
});
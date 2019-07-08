<?php

namespace qpost\Router;

use qpost\Navigation\NavPoint;
use qpost\Util\Util;

create_route("/messages", function () {
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	return twig_render("pages/messages.html.twig", [
		"title" => "Messages (" . Util::getCurrentUser()->getUnreadMessages() . ")",
		"nav" => NavPoint::MESSAGES
	]);
});
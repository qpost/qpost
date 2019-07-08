<?php

namespace qpost\Router;

create_route("/discover", function () {
	return twig_render("pages/landing/discover.html.twig", [
		"title" => "Discover"
	]);
});
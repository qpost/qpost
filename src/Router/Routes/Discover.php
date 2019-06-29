<?php

$app->bind("/discover",function(){
	return twig_render("pages/landing/discover.html.twig", [
		"title" => "Discover"
	]);
});
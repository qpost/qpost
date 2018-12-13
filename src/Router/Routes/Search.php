<?php

$app->get("/search",function(){
	$data = array(
		"title" => "Search" . (isset($_GET["query"]) && !Util::isEmpty(trim($_GET["query"])) ? ": \"" . Util::sanatizeString($_GET["query"]) . "\"" : ""),
		"page" => isset($_GET["page"]) && !Util::isEmpty(trim($_GET["page"])) && is_numeric($_GET["page"]) && (int)$_GET["page"] > 0 ? (int)$_GET["page"] : 1,
		"type" => isset($_GET["type"]) && !Util::isEmpty(trim($_GET["type"])) ? $_GET["type"] : "posts",
		"query" => isset($_GET["query"]) && !Util::isEmpty(trim($_GET["query"])) ? $_GET["query"] : null
	);

	return $this->render("views:Search.php with views:Layout.php",$data);
});
<?php

$app->get("/search",function(){
	$data = array(
		"title" => "Search" . (isset($_GET["query"]) && !empty($_GET["query"]) ? ": &quot;" . Util::sanatizeString($_GET["query"]) . "&quot;" : ""),
		"subtitle" => "Search" . (isset($_GET["query"]) && !empty($_GET["query"]) ? ": &quot;" . Util::sanatizeString($_GET["query"]) . "&quot;" : ""),
		"page" => isset($_GET["page"]) && !empty($_GET["page"]) && is_numeric($_GET["page"]) && (int)$_GET["page"] > 0 ? (int)$_GET["page"] : 1,
		"type" => isset($_GET["type"]) && !empty($_GET["type"]) ? $_GET["type"] : "posts",
		"query" => isset($_GET["query"]) && !empty($_GET["query"]) ? $_GET["query"] : null
	);

	return $this->render("views:Search.php with views:Layout.php",$data);
});
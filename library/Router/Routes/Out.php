<?php

$app->bind("/out",function(){
	if(isset($_GET["link"])){
		if(filter_var($_GET["link"],FILTER_VALIDATE_URL)){
			$link = $_GET["link"];

			$host = !is_null(parse_url($link,PHP_URL_HOST)) && !Util::isEmpty(parse_url($link,PHP_URL_HOST)) ? parse_url($link,PHP_URL_HOST) : $link;
			
			if($host != $_SERVER["HTTP_HOST"]){
				$host = Util::sanatizeString($host);
				$link = Util::sanatizeString($link);

				$data = array(
					"title" => "You are now headed to a different website",
					"link" => $link,
					"host" => $host
				);
			
				return $this->render("views:Out.php with views:Layout.php",$data);
			} else {
				return $this->reroute($link);
			}
		} else {
			return $this->reroute("/");
		}
	} else {
		return $this->reroute("/");
	}
});
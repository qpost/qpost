<?php

$app->bind("/discover",function(){
	return $this->render("views:Discover.php with views:HomeLayout.php",[
        "title" => "Discover"
    ]);
});
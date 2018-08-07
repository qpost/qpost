<?php

$app->bind("/discover",function(){
	$data = array(
        "title" => "Discover"
    );

    return $this->render("views:Discover.php with views:HomeLayout.php",$data);
});
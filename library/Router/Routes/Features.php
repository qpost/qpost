<?php

$app->bind("/features",function(){
	$data = array(
        "title" => "Features"
    );

    return $this->render("views:Features.php with views:HomeLayout.php",$data);
});
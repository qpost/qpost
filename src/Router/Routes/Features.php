<?php

$app->bind("/features",function(){
	return $this->render("views:Features.php with views:HomeLayout.php",[
        "title" => "Features"
    ]);
});
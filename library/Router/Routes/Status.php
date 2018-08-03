<?php

$app->bind("/status/:id",function($params){
    $id = $params["id"];

    if(!empty($id) && is_numeric($id)){
        $post = FeedEntry::getEntryById($id);

        if(!is_null($post) && !is_null($post->getUser())){
            if($post->getType() == "POST"){
                $_SESSION["profileLoadPost"] = $post->getId();

                return $this->reroute("/" . $post->getUser()->getUsername());
            }
        }
    }
});
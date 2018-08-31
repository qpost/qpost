<?php

$app->bind("/status/:id",function($params){
    $id = $params["id"];

    if(!empty($id) && is_numeric($id)){
        $post = FeedEntry::getEntryById($id);

        if(!is_null($post) && !is_null($post->getUser())){
            if($post->getType() == "POST"){
				$user = $post->getUser();
				$_SESSION["profileLoadPost"] = $post->getId();

				if(!is_null($post->getAttachments()) && is_array($post->getAttachments()) && count($post->getAttachments()) > 0){
					foreach($post->getAttachmentObjects() as $mediaFile){
						if($mediaFile->getType() == "IMAGE"){
							$bigSocialImage = $mediaFile->getURL();
							break;
						}
					}
				}
				
				$data = array(
					"title" => $post->getUser()->getDisplayName() . " on qpost: \"" . Util::limitString($post->getText(),34,true) . "\"",
					"originalTitle" => $user->getDisplayName() . " (@" . $user->getUsername() . ")",
					"nav" => Util::isLoggedIn() && $user->getId() == $_SESSION["id"] ? NAV_PROFILE : null,
					"user" => $user,
					"socialImage" => $user->getAvatarURL(),
					"showProfile" => true,
					"profileTab" => PROFILE_TAB_FEED,
					"currentPage" => 1,
					"description" => Util::limitString($post->getText(),150,true),
					"bigSocialImage" => isset($bigSocialImage) ? $bigSocialImage : null
				);
			
				return $this->render("views:Profile/Feed.php with views:Layout.php",$data);

                //return $this->reroute("/" . $post->getUser()->getUsername());
            }
        }
    }
});
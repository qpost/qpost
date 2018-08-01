<?php

$app->post("/scripts/toggleFollow",function(){
	$this->response->mime ="json";
	
	if(isset($_POST["user"])){
		if(Util::isLoggedIn()){
			$user = Util::getCurrentUser();
			$toFollow = User::getUserById($_POST["user"]);

			if($user->getFollowers() < FOLLOW_LIMIT){
				if(!is_null($user) && !is_null($toFollow)){
					if($user->getId() != $toFollow->getId()){
						$followStatus = -1;

						if($user->isFollowing($toFollow)){
							$user->unfollow($toFollow);
							$followStatus = 0;
						} else {
							$user->follow($toFollow);
							$followStatus = 1;
						}
		
						return json_encode(["followStatus" => $followStatus]);
					} else {
						return json_encode(["error" => "Can't follow self"]);
					}
				} else {
					return json_encode(["error" => "Invalid user"]);
				}
			} else {
				return json_encode(["error" => "Reached follow limit"]);
			}
		} else {
			return json_encode(["error" => "Not logged in"]);
		}
	} else {
		return json_encode(["error" => "Bad request"]);
	}
});
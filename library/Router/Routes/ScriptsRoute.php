<?php

$app->post("/scripts/toggleFollow",function(){
	$this->response->mime ="json";
	
	if(isset($_POST["user"])){
		if(Util::isLoggedIn()){
			$user = Util::getCurrentUser();
			$toFollow = User::getUserById($_POST["user"]);

			if(!is_null($user) && !is_null($toFollow)){
				if($user->isFollowing($toFollow)){
					$user->unfollow($toFollow);
				} else {
					$user->follow($toFollow);
				}

				return json_decode(["following" => $user->isFollowing($user)]);
			} else {
				return json_encode(["error" => "Invalid user"]);
			}
		} else {
			return json_encode(["error" => "Not logged in"]);
		}
	} else {
		return json_encode(["error" => "Bad request"]);
	}
});
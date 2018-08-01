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

$app->post("/scripts/createPost",function(){
	$this->response->mime ="json";
	
	if(isset($_POST["text"])){
		if(Util::isLoggedIn()){
			$user = Util::getCurrentUser();
			$text = $_POST["text"];

			if($text <= POST_CHARACTER_LIMIT){
				$text = Util::sanatizeString($text);

				$userId = $user->getId();
				$sessionId = session_id();
				$type = FEED_ENTRY_TYPE_POST;

				$postId = null;

				$mysqli = Database::Instance()->get();
				$stmt = $mysqli->prepare("INSERT INTO `feed` (`user`,`text`,`following`,`sessionId`,`type`) VALUES(?,?,NULL,?,?);");
				$stmt->bind_param("isss", $userId,$text,$sessionId,$type);
				if($stmt->execute()){
					$postId = $stmt->insert_id;
				}
				$stmt->close();

				if(!is_null($postId)){
					$post = [];

					$stmt = $mysqli->prepare("SELECT `text`,`time` FROM `feed` WHERE `id` = ? LIMIT 1");
					$stmt->bind_param("i",$postId);
					if($stmt->execute()){
						$result = $stmt->get_result();

						if($result->num_rows){
							$row = $result->fetch_assoc();

							$post["id"] = $postId;
							$post["text"] = Util::convertPost($row["text"]);
							$post["time"] = Util::timeago($row["time"]);
							$post["userName"] = $user->getUsername();
							$post["userDisplayName"] = $user->getDisplayName();
							$post["userAvatar"] = $user->getAvatarURL();
						}
					}
					$stmt->close();

					$user->reloadFeedEntriesCount();
					$user->reloadPostsCount();

					return json_encode(["post" => $post]);
				} else {
					return json_encode(["error" => "Empty post id"]);
				}
			} else {
				return json_encode(["error" => "Exceeded character limit"]);
			}

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
<?php

$app->post("/scripts/toggleFollow",function(){
	$this->response->mime ="json";
	
	if(isset($_POST["user"])){
		if(Util::isLoggedIn()){
			$user = Util::getCurrentUser();
			$toFollow = User::getUserById($_POST["user"]);
			$mysqli = Database::Instance()->get();

			if($user->getFollowers() < FOLLOW_LIMIT){
				if(!is_null($user) && !is_null($toFollow)){
					if($user->getId() != $toFollow->getId()){
						$followStatus = -1;

						if($toFollow->getPrivacyLevel() == "PUBLIC"){
							if($user->isFollowing($toFollow)){
								$user->unfollow($toFollow);
								$followStatus = 0;
							} else {
								$user->follow($toFollow);
								$followStatus = 1;
							}
						} else if($toFollow->getPrivacyLevel() == "PRIVATE"){
							$u1 = $user->getId();
							$u2 = $toFollow->getId();

							if($user->isFollowing($toFollow)){
								$user->unfollow($toFollow);
								$followStatus = 0;
							} else {
								if(!$user->hasSentFollowRequest($toFollow)){
									$stmt = $mysqli->prepare("INSERT INTO `follow_requests` (`follower`,`following`) VALUES(?,?);");
									$stmt->bind_param("ii",$u1,$u2);
									$stmt->execute();
									$stmt->close();

									$toFollow->reloadOpenFollowRequests();
	
									$followStatus = 2;
								} else {
									$stmt = $mysqli->prepare("DELETE FROM `follow_requests` WHERE `follower` = ? AND `following` = ?");
									$stmt->bind_param("ii",$u1,$u2);
									$stmt->execute();
									$stmt->close();

									$toFollow->reloadOpenFollowRequests();
	
									$followStatus = 0;
								}
							}
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

$app->post("/scripts/deletePost",function(){
	$this->response->mime = "json";

	if(isset($_POST["post"])){
		if(Util::isLoggedIn()){
			$user = Util::getCurrentUser();

			$post = FeedEntry::getEntryById($_POST["post"]);

			if(is_null($post))
				return json_encode(["error" => "Unknown post"]);

			if($post->getUserId() == $user->getId() && ($post->getType() == "POST" || $post->getType() == "NEW_FOLLOWING")){
				$post->delete();

				return json_encode(["status" => "done"]);
			} else {
				return json_encode(["error" => "Unknown post"]);
			}
		} else {
			return json_encode(["error" => "Not logged in"]);
		}
	} else {
		return json_encode(["error" => "Bad request"]);
	}
});

$app->post("/scripts/toggleFavorite",function(){
	$this->response->mime = "json";
	
	if(isset($_POST["post"])){
		if(Util::isLoggedIn()){
			$user = Util::getCurrentUser();
			$post = FeedEntry::getEntryById($_POST["post"]);

			if(is_null($post))
				return json_encode(["error" => "Unknown post"]);

			if($user->hasFavorited($post->getId())){
				$user->unfavorite($post->getId());
				return json_encode(["status" => "Favorite removed"]);
			} else {
				$user->favorite($post->getId());
				return json_encode(["status" => "Favorite added"]);
			}
		} else {
			return json_encode(["error" => "Not logged in"]);
		}
	} else {
		return json_encode(["error" => "Bad request"]);
	}
});

$app->post("/scripts/toggleShare",function(){
	$this->response->mime ="json";
	
	if(isset($_POST["post"])){
		if(Util::isLoggedIn()){
			$user = Util::getCurrentUser();
			$post = FeedEntry::getEntryById($_POST["post"]);

			if(is_null($post))
				return json_encode(["error" => "Unknown post"]);

			if($post->getUserId() == $user->getId())
				return json_encode(["error" => "Cant share own post"]);

			if($user->hasShared($post->getId())){
				$user->unshare($post->getId());
				return json_encode(["status" => "Share removed"]);
			} else {
				$user->share($post->getId());
				return json_encode(["status" => "Share added"]);
			}
		} else {
			return json_encode(["error" => "Not logged in"]);
		}
	} else {
		return json_encode(["error" => "Bad request"]);
	}
});

$app->get("/scripts/desktopNotifications",function(){
	$this->response->mime = "json";

	if(Util::isLoggedIn()){
		$currentUser = Util::getCurrentUser();
		$mysqli = Database::Instance()->get();
		$uid = $currentUser->getId();

		$notifications = [];

		$stmt = $mysqli->prepare("SELECT * FROM `notifications` WHERE `user` = ? AND `notified` = 0");
		$stmt->bind_param("i",$uid);
		if($stmt->execute()){
			$result = $stmt->get_result();
			if($result->num_rows){
				while($row = $result->fetch_assoc()){
					array_push($notifications,[
						"id" => $row["id"],
						"user" => Util::userJsonData($row["user"]),
						"type" => $row["type"],
						"follower" => Util::userJsonData($row["follower"]),
						"post" => Util::postJsonData($row["post"]),
						"time" => $row["time"]
					]);
				}
			}
		}
		$stmt->close();

		$stmt = $mysqli->prepare("UPDATE `notifications` SET `notified` = 1 WHERE `user` = ? AND `notified` = 0");
		$stmt->bind_param("i",$uid);
		$stmt->execute();
		$stmt->close();

		return json_encode(["notifications" => $notifications,"unreadCount" => Util::getCurrentUser()->getUnreadNotifications()]);
	} else {
		return json_encode(["error" => "Not logged in"]);
	}
});

$app->post("/scripts/extendHomeFeed",function(){
	$this->response->mime = "json";

	if(Util::isLoggedIn()){
		$currentUser = Util::getCurrentUser();
		$mysqli = Database::Instance()->get();

		$a = $currentUser->getFollowingAsArray();
		array_push($a,$currentUser->getId());

		$i = $mysqli->real_escape_string(implode(",",$a));

		if(isset($_POST["mode"])){
			if($_POST["mode"] == "loadOld"){
				if(isset($_POST["firstPost"])){
					$posts = [];
					$firstPost = (int)$_POST["firstPost"];

					$stmt = $mysqli->prepare("SELECT f.`id` AS `postID`,f.`text` AS `postText`,f.`time` AS `postTime`,f.`sessionId`,f.`post` AS `sharedPost`,f.`count.replies`,f.`count.shares`,f.`count.favorites`,u.* FROM `feed` AS f INNER JOIN `users` AS u ON f.`user` = u.`id` WHERE f.`post` IS NULL AND (f.`type` = 'POST' OR f.`type` = 'SHARE') AND f.`user` IN ($i) AND u.`privacy.level` != 'CLOSED' AND f.`id` < ? ORDER BY f.`time` DESC LIMIT 30");
					$stmt->bind_param("i",$firstPost);
					if($stmt->execute()){
						$result = $stmt->get_result();

						if($result->num_rows){
							while($row = $result->fetch_assoc()){
								$entry = FeedEntry::getEntryFromData($row["postID"],$row["id"],$row["postText"],null,$row["sharedPost"],$row["sessionId"],"POST",$row["count.replies"],$row["count.shares"],$row["count.favorites"],$row["postTime"]);

								$sharedPost = is_null($row["sharedPost"]) ? $entry : FeedEntry::getEntryById($row["sharedPost"]);

								$postActionButtons = Util::getPostActionButtons($sharedPost);

								if(!is_null($row["sharedPost"])){
									$sharedPost = FeedEntry::getEntryById($row["sharedPost"]);

									if(!is_null($sharedPost)){
										$sharedUser = $sharedPost->getUser();

										if(!is_null($sharedUser)){
											array_push($posts,[
												"id" => $entry->getId(),
												"time" => Util::timeago($entry->getTime()),
												"userName" => $entry->getUser()->getUsername(),
												"userDisplayName" => $entry->getUser()->getDisplayName(),
												"userAvatar" => $entry->getUser()->getAvatarURL(),
												
												"shared" => [
													"id" => $sharedPost->getId(),
													"text" => Util::convertPost($sharedPost->getText()),
													"time" => Util::timeago($sharedPost->getTime()),
													"userName" => $sharedPost->getUser()->getUsername(),
													"userDisplayName" => $sharedPost->getUser()->getDisplayName(),
													"userAvatar" => $sharedPost->getUser()->getAvatarURL()
												],

												"postActionButtons" => $postActionButtons
											]);
										} else {
											continue;
										}
									} else {
										continue;
									}
								} else {
									array_push($posts,[
										"id" => $entry->getId(),
										"text" => Util::convertPost($entry->getText()),
										"time" => Util::timeago($entry->getTime()),
										"userName" => $entry->getUser()->getUsername(),
										"userDisplayName" => $entry->getUser()->getDisplayName(),
										"userAvatar" => $entry->getUser()->getAvatarURL(),

										"postActionButtons" => $postActionButtons
									]);
								}
							}
						}
					}
					$stmt->close();

					return json_encode(["result" => $posts]);
				} else {
					return json_encode(["error" => "Bad request"]);
				}
			} else if($_POST["mode"] == "loadNew"){
				if(isset($_POST["lastPost"])){
					$posts = [];
					$lastPost = (int)$_POST["lastPost"];

					$stmt = $mysqli->prepare("SELECT f.`id` AS `postID`,f.`text` AS `postText`,f.`time` AS `postTime`,f.`sessionId`,f.`post` AS `sharedPost`,f.`count.replies`,f.`count.shares`,f.`count.favorites`,u.* FROM `feed` AS f INNER JOIN `users` AS u ON f.`user` = u.`id` WHERE f.`post` IS NULL AND (f.`type` = 'POST' OR f.`type` = 'SHARE') AND f.`user` IN ($i) AND u.`privacy.level` != 'CLOSED' AND f.`id` > ? ORDER BY f.`time` DESC LIMIT 30");
					$stmt->bind_param("i",$lastPost);
					if($stmt->execute()){
						$result = $stmt->get_result();

						if($result->num_rows){
							while($row = $result->fetch_assoc()){
								$entry = FeedEntry::getEntryFromData($row["postID"],$row["id"],$row["postText"],null,$row["sharedPost"],$row["sessionId"],"POST",$row["count.replies"],$row["count.shares"],$row["count.favorites"],$row["postTime"]);

								$sharedPost = is_null($row["sharedPost"]) ? $entry : FeedEntry::getEntryById($row["sharedPost"]);

								$postActionButtons = Util::getPostActionButtons($sharedPost);

								if(!is_null($row["sharedPost"])){
									$sharedPost = FeedEntry::getEntryById($row["sharedPost"]);

									if(!is_null($sharedPost)){
										$sharedUser = $sharedPost->getUser();

										if(!is_null($sharedUser)){
											array_push($posts,[
												"id" => $entry->getId(),
												"time" => Util::timeago($entry->getTime()),
												"userName" => $entry->getUser()->getUsername(),
												"userDisplayName" => $entry->getUser()->getDisplayName(),
												"userAvatar" => $entry->getUser()->getAvatarURL(),
												
												"shared" => [
													"id" => $sharedPost->getId(),
													"text" => Util::convertPost($sharedPost->getText()),
													"time" => Util::timeago($sharedPost->getTime()),
													"userId" => $sharedPost->getUser()->getId(),
													"userName" => $sharedPost->getUser()->getUsername(),
													"userDisplayName" => $sharedPost->getUser()->getDisplayName(),
													"userAvatar" => $sharedPost->getUser()->getAvatarURL()
												],

												"postActionButtons" => $postActionButtons
											]);
										} else {
											continue;
										}
									} else {
										continue;
									}
								} else {
									array_push($posts,[
										"id" => $entry->getId(),
										"text" => Util::convertPost($entry->getText()),
										"time" => Util::timeago($entry->getTime()),
										"userName" => $entry->getUser()->getUsername(),
										"userDisplayName" => $entry->getUser()->getDisplayName(),
										"userAvatar" => $entry->getUser()->getAvatarURL(),

										"postActionButtons" => $postActionButtons
									]);
								}
							}
						}
					}
					$stmt->close();

					return json_encode(["result" => $posts]);
				} else {
					return json_encode(["error" => "Bad request"]);
				}
			} else {
				return json_encode(["error" => "Bad request"]);
			}
		} else {
			return json_encode(["error" => "Bad request"]);
		}
	} else {
		return json_encode(["error" => "Not logged in"]);
	}
});

$app->post("/scripts/postInfo",function(){
	$this->response->mime = "json";

	if(isset($_POST["postId"])){
		$postId = $_POST["postId"];
		$post = FeedEntry::getEntryById($postId);

		if(!is_null($post)){
			$user = $post->getUser();

			if(!is_null($user)){
				$followButton = Util::followButton($user,false,["float-right"]);

				if(is_null($followButton))
					$followButton = "";

				$jsonData = Util::postJsonData($postId);

				$replies = [];
				if($post->getReplies() > 0){
					$mysqli = Database::Instance()->get();
						
					$postId = $post->getId();
					$uid = Util::isLoggedIn() ? Util::getCurrentUser()->getId() : -1;

					$stmt = $mysqli->prepare("SELECT f.*,u.`id` AS `userId`,u.`displayName`,u.`username`,u.`email`,u.`avatar`,u.`bio`,u.`token`,u.`birthday`,u.`privacy.level`,u.`featuredBox.title`,u.`featuredBox.content`,u.`lastGigadriveUpdate`,u.`gigadriveJoinDate`,u.`time` AS `userTime` FROM `feed` AS f INNER JOIN `users` AS u ON f.user = u.id WHERE f.`post` = ? AND f.`type` = 'POST' ORDER BY u.`id` = ?,f.`time` DESC");
					$stmt->bind_param("ii",$postId,$uid);
					if($stmt->execute()){
						$result = $stmt->get_result();

						if($result->num_rows){
							while($row = $result->fetch_assoc()){
								$f = FeedEntry::getEntryFromData($row["id"],$row["user"],$row["text"],$row["following"],$row["post"],$row["sessionId"],$row["type"],$row["count.replies"],$row["count.shares"],$row["count.favorites"],$row["time"]);
								$u = User::getUserByData($row["userId"],$row["displayName"],$row["username"],$row["email"],$row["avatar"],$row["bio"],$row["token"],$row["birthday"],$row["privacy.level"],$row["featuredBox.title"],$row["featuredBox.content"],$row["lastGigadriveUpdate"],$row["gigadriveJoinDate"],$row["userTime"]);

								array_push($replies,[
									"id" => $f->getId(),
									"user" => [
										"id" => $u->getId(),
										"displayName" => $u->getDisplayName(),
										"username" => $u->getUsername(),
										"avatar" => $u->getAvatarURL()
									],
									"text" => Util::convertPost($f->getText()),
									"textUnfiltered" => Util::sanatizeString($f->getText()),
									"time" => Util::timeago($f->getTime()),
									"postActionButtons" => Util::getPostActionButtons($f)
								]);
							}
						}
					}
					$stmt->close();
				}

				$jsonData["followButton"] = $followButton;
				$jsonData["replies"] = $replies;
				$jsonData["postForm"] = Util::renderCreatePostForm(["replyForm"],false);

				return json_encode($jsonData);
			} else {
				return json_encode(["error" => "User not found"]);
			}
		} else {
			return json_encode(["error" => "Invalid ID"]);
		}
	} else {
		return json_encode(["error" => "Bad request"]);
	}
});

$app->post("/scripts/createPost",function(){
	$this->response->mime = "json";
	
	if(isset($_POST["text"])){
		if(Util::isLoggedIn()){
			$user = Util::getCurrentUser();
			$text = trim($_POST["text"]);

			$mentioned = Util::getUsersMentioned($text);

			if(strlen($text) <= POST_CHARACTER_LIMIT){
				if(count($mentioned) < 15){
					$text = Util::sanatizeString($text);

					$userId = $user->getId();
					$sessionId = session_id();
					$type = FEED_ENTRY_TYPE_POST;

					$postId = null;

					$parent = isset($_POST["replyTo"]) ? $_POST["replyTo"] : null;

					if(!is_null($parent) && !is_null(FeedEntry::getEntryById($parent))){
						$parentCreator = FeedEntry::getEntryById($parent)->getUser();
						if($parentCreator->getPrivacyLevel() == "CLOSED" && $parentCreator->getId() != $userId) return json_encode(["error" => "Parent owner is closed"]);
						if($parentCreator->getPrivacyLevel() == "PRIVATE" && !$parentCreator->isFollower($userId)) return json_encode(["error" => "Parent owner is private and not followed"]);
						if($parentCreator->hasBlocked($userId)) return json_encode(["error" => "Parent has blocked"]);
					}

					if($parent == 0) $parent = null;

					$mysqli = Database::Instance()->get();
					$stmt = $mysqli->prepare("INSERT INTO `feed` (`user`,`text`,`following`,`sessionId`,`type`,`post`) VALUES(?,?,NULL,?,?,?);");
					$stmt->bind_param("isssi", $userId,$text,$sessionId,$type,$parent);
					if($stmt->execute()){
						$postId = $stmt->insert_id;
					}
					$stmt->close();

					if(!is_null($postId)){
						$post = [];

						$postData = FeedEntry::getEntryById($postId);
						$post["id"] = $postData->getId();
						$post["text"] = Util::convertPost($postData->getText());
						$post["time"] = Util::timeago($postData->getTime());
						$post["userName"] = $user->getUsername();
						$post["userDisplayName"] = $user->getDisplayName();
						$post["userAvatar"] = $user->getAvatarURL();

						if(!is_null($parent)){
							$parentData = FeedEntry::getEntryById($parent);

							if(!is_null($parentData)){
								$parentData->reloadReplies();

								if($parentData->getUserId() != $userId){
									if($parentData->getUser()->canPostNotification(NOTIFICATION_TYPE_REPLY,null,$postId)){
										$uid = $parentData->getUserId();

										$stmt = $mysqli->prepare("INSERT INTO `notifications` (`user`,`type`,`post`) VALUES(?,'REPLY',?);");
										$stmt->bind_param("ii",$uid,$postId);
										$stmt->execute();
										$stmt->close();

										$parentData->getUser()->reloadUnreadNotifications();
									}
								}
							}
						}

						if(count($mentioned) > 0){
							foreach($mentioned as $u){
								$uid = $u->getId();
								if($uid == $userId) continue;

								if(!$user->isBlocked($u)){
									if($u->canPostNotification(NOTIFICATION_TYPE_MENTION,null,$postId)){
										$stmt = $mysqli->prepare("INSERT INTO `notifications` (`user`,`type`,`post`) VALUES(?,'MENTION',?);");
										$stmt->bind_param("ii",$uid,$postId);
										$stmt->execute();
										$stmt->close();

										$u->reloadUnreadNotifications();
									}
								}
							}
						}

						$postActionButtons = Util::getPostActionButtons($postData);

						$user->reloadFeedEntriesCount();
						$user->reloadPostsCount();

						return json_encode(["post" => $post,"postActionButtons" => $postActionButtons]);
					} else {
						return json_encode(["error" => "Empty post id"]);
					}
				} else {
					return json_encode(["error" => "Too many mentions"]);
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
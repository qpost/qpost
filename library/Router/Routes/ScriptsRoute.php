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

$app->post("/scripts/toggleFavorite",function(){
	$this->response->mime ="json";
	
	if(isset($_POST["post"])){
		if(Util::isLoggedIn()){
			$user = Util::getCurrentUser();
			$post = FeedEntry::getEntryById($_POST["post"]);

			if(is_null($post))
				return json_encode(["error" => "Unknown post"]);

			if($user->hasFavorited($post->getId())){
				$user->unfavorite($post->getId());
			} else {
				$user->favorite($post->getId());
			}

			if($user->hasFavorited($post->getId())){
				return json_encode(["status" => "Favorite added"]);
			} else {
				return json_encode(["status" => "Favorite removed"]);
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
			} else {
				$user->share($post->getId());
			}

			if($user->hasShared($post->getId())){
				return json_encode(["status" => "Share added"]);
			} else {
				return json_encode(["status" => "Share removed"]);
			}
		} else {
			return json_encode(["error" => "Not logged in"]);
		}
	} else {
		return json_encode(["error" => "Bad request"]);
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

					$stmt = $mysqli->prepare("SELECT f.`id` AS `postID`,f.`text` AS `postText`,f.`time` AS `postTime`,f.`sessionId`,f.`post` AS `sharedPost`,u.* FROM `feed` AS f INNER JOIN `users` AS u ON f.`user` = u.`id` WHERE (f.`type` = 'POST' OR f.`type` = 'SHARE') AND f.`user` IN ($i) AND f.`id` < ? ORDER BY f.`time` DESC LIMIT 30");
					$stmt->bind_param("i",$firstPost);
					if($stmt->execute()){
						$result = $stmt->get_result();

						if($result->num_rows){
							while($row = $result->fetch_assoc()){
								$entry = FeedEntry::getEntryFromData($row["postID"],$row["id"],$row["postText"],null,$row["sharedPost"],$row["sessionId"],"POST",$row["postTime"]);

								$sharedPost = is_null($row["sharedPost"]) ? $entry : FeedEntry::getEntryById($row["sharedPost"]);

								$postActionButtons = "";

								if(Util::isLoggedIn()){
									$postActionButtons .= '<div class="mt-1 postActionButtons ignoreParentClick float-left">';
										$postActionButtons .= '<span' . (Util::getCurrentUser()->getId() != $entry->getUser()->getId() ? ' class="shareButton" data-toggle="tooltip" title="Share"' : ' data-toggle="tooltip" title="You can not share this post"') . ' data-post-id="' . $entry->getId() . '">';
											$postActionButtons .= '<i class="fas fa-share-alt' . (Util::getCurrentUser()->hasShared($sharedPost->getId()) ? ' text-primary' : "")  . '"' . (Util::getCurrentUser()->hasShared($sharedPost->getId()) ? "" : ' style="color: gray"') . '></i>';
										$postActionButtons .= '</span>';
		
										$postActionButtons .= '<span class="shareCount small text-primary ml-1 mr-1">';
											$postActionButtons .= $entry->getShares();
										$postActionButtons .= '</span>';
		
										$postActionButtons .= '<span class="favoriteButton" data-post-id="<?= $sharedPost->getId() ?>">';
											$postActionButtons .= '<i class="fas fa-star"' . (Util::getCurrentUser()->hasFavorited($sharedPost->getId()) ? ' style="color: gold"' : ' style="color: gray"') . '></i>';
										$postActionButtons .= '</span>';
		
										$postActionButtons .= '<span class="favoriteCount small ml-1 mr-1" style="color: #ff960c">';
											$postActionButtons .= $entry->getFavorites();
										$postActionButtons .= '</span>';
									$postActionButtons .= '</div>';
								}

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

					$stmt = $mysqli->prepare("SELECT f.`id` AS `postID`,f.`text` AS `postText`,f.`time` AS `postTime`,f.`sessionId`,f.`post` AS `sharedPost`,u.* FROM `feed` AS f INNER JOIN `users` AS u ON f.`user` = u.`id` WHERE (f.`type` = 'POST' OR f.`type` = 'SHARE') AND f.`user` IN ($i) AND f.`id` > ? ORDER BY f.`time` DESC LIMIT 30");
					$stmt->bind_param("i",$lastPost);
					if($stmt->execute()){
						$result = $stmt->get_result();

						if($result->num_rows){
							while($row = $result->fetch_assoc()){
								$entry = FeedEntry::getEntryFromData($row["postID"],$row["id"],$row["postText"],null,$row["sharedPost"],$row["sessionId"],"POST",$row["postTime"]);

								$sharedPost = is_null($row["sharedPost"]) ? $entry : FeedEntry::getEntryById($row["sharedPost"]);

								$postActionButtons = "";

								if(Util::isLoggedIn()){
									$postActionButtons .= '<div class="mt-1 postActionButtons ignoreParentClick float-left">';
										$postActionButtons .= '<span' . (Util::getCurrentUser()->getId() != $sharedPost->getUser()->getId() ? ' class="shareButton" data-toggle="tooltip" title="Share"' : ' data-toggle="tooltip" title="You can not share this post"') . ' data-post-id="' . $sharedPost->getId() . '">';
											$postActionButtons .= '<i class="fas fa-share-alt' . (Util::getCurrentUser()->hasShared($sharedPost->getId()) ? ' text-primary' : "")  . '"' . (Util::getCurrentUser()->hasShared($sharedPost->getId()) ? "" : ' style="color: gray"') . '></i>';
										$postActionButtons .= '</span>';
		
										$postActionButtons .= '<span class="shareCount small text-primary ml-1 mr-1">';
											$postActionButtons .= $sharedPost->getShares();
										$postActionButtons .= '</span>';
		
										$postActionButtons .= '<span class="favoriteButton" data-post-id="<?= $sharedPost->getId() ?>">';
											$postActionButtons .= '<i class="fas fa-star"' . (Util::getCurrentUser()->hasFavorited($sharedPost->getId()) ? ' style="color: gold"' : ' style="color: gray"') . '></i>';
										$postActionButtons .= '</span>';
		
										$postActionButtons .= '<span class="favoriteCount small ml-1 mr-1" style="color: #ff960c">';
											$postActionButtons .= $sharedPost->getFavorites();
										$postActionButtons .= '</span>';
									$postActionButtons .= '</div>';
								}

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
			if($post->getType() == "POST"){
				$user = $post->getUser();

				if(!is_null($user)){
					$followButton = Util::followButton($user,false,["float-right"]);

					if(is_null($followButton))
						$followButton = "";

					$postActionButtons = "";

					if(Util::isLoggedIn()){
						$postActionButtons .= '<div class="mt-1 postActionButtons ignoreParentClick float-left">';
							$postActionButtons .= '<span' . (Util::getCurrentUser()->getId() != $post->getUser()->getId() ? ' class="shareButton" data-toggle="tooltip" title="Share"' : ' data-toggle="tooltip" title="You can not share this post"') . ' data-post-id="' . $post->getId() . '">';
								$postActionButtons .= '<i class="fas fa-share-alt' . (Util::getCurrentUser()->hasShared($post->getId()) ? ' text-primary' : "")  . '"' . (Util::getCurrentUser()->hasShared($post->getId()) ? "" : ' style="color: gray"') . '></i>';
							$postActionButtons .= '</span>';

							$postActionButtons .= '<span class="shareCount small text-primary ml-1 mr-1">';
								$postActionButtons .= $post->getShares();
							$postActionButtons .= '</span>';

							$postActionButtons .= '<span class="favoriteButton" data-post-id="<?= $sharedPost->getId() ?>">';
								$postActionButtons .= '<i class="fas fa-star"' . (Util::getCurrentUser()->hasFavorited($post->getId()) ? ' style="color: gold"' : ' style="color: gray"') . '></i>';
							$postActionButtons .= '</span>';

							$postActionButtons .= '<span class="favoriteCount small ml-1 mr-1" style="color: #ff960c">';
								$postActionButtons .= $post->getFavorites();
							$postActionButtons .= '</span>';
						$postActionButtons .= '</div>';
					}
					
					return json_encode([
						"id" => $post->getId(),
						"user" => [
							"id" => $user->getId(),
							"displayName" => $user->getDisplayName(),
							"username" => $user->getUsername(),
							"avatar" => $user->getAvatarURL()
						],
						"text" => Util::convertPost($post->getText()),
						"textUnfiltered" => Util::sanatizeString($post->getText()),
						"time" => Util::timeago($post->getTime()),
						"shares" => $post->getShares(),
						"favorites" => $post->getFavorites(),
						"followButton" => $followButton,
						"postActionButtons" => $postActionButtons
					]);
				} else {
					return json_encode(["error" => "User not found"]);
				}
			} else {
				return json_encode(["error" => "Not a post"]);
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
			$text = $_POST["text"];

			$mentioned = Util::getUsersMentioned($text);

			if($text <= POST_CHARACTER_LIMIT){
				if(count($mentioned) < 15){
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
									}
								}
							}
						}

						$user->reloadFeedEntriesCount();
						$user->reloadPostsCount();

						return json_encode(["post" => $post]);
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
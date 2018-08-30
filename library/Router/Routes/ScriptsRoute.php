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

$app->post("/scripts/validateVideoURL",function(){
	$this->response->mime = "json";

	if(Util::isLoggedIn()){
		$currentUser = Util::getCurrentUser();

		if(isset($_POST["videoURL"])){
			return json_encode(["status" => Util::isValidVideoURL($_POST["videoURL"]) ? "valid" : "invalid"]);
		} else {
			return json_encode(["error" => "Bad request"]);
		}
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

					$stmt = $mysqli->prepare("SELECT f.`id` AS `postID`,f.`text` AS `postText`,f.`time` AS `postTime`,f.`sessionId`,f.`post` AS `sharedPost`,f.`count.replies`,f.`count.shares`,f.`count.favorites`,f.`attachments`,u.* FROM `feed` AS f INNER JOIN `users` AS u ON f.`user` = u.`id` WHERE f.`post` IS NULL AND (f.`type` = 'POST' OR f.`type` = 'SHARE') AND f.`user` IN ($i) AND u.`privacy.level` != 'CLOSED' AND f.`id` < ? ORDER BY f.`time` DESC LIMIT 30");
					$stmt->bind_param("i",$firstPost);
					if($stmt->execute()){
						$result = $stmt->get_result();

						if($result->num_rows){
							while($row = $result->fetch_assoc()){
								$entry = FeedEntry::getEntryFromData($row["postID"],$row["id"],$row["postText"],null,$row["sharedPost"],$row["sessionId"],"POST",$row["count.replies"],$row["count.shares"],$row["count.favorites"],$row["attachments"],$row["postTime"]);

								array_push($posts,Util::postJsonData($entry));
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

					$stmt = $mysqli->prepare("SELECT f.`id` AS `postID`,f.`text` AS `postText`,f.`time` AS `postTime`,f.`sessionId`,f.`post` AS `sharedPost`,f.`count.replies`,f.`count.shares`,f.`count.favorites`,f.`attachments`,u.* FROM `feed` AS f INNER JOIN `users` AS u ON f.`user` = u.`id` WHERE f.`post` IS NULL AND (f.`type` = 'POST' OR f.`type` = 'SHARE') AND f.`user` IN ($i) AND u.`privacy.level` != 'CLOSED' AND f.`id` > ? ORDER BY f.`time` DESC LIMIT 30");
					$stmt->bind_param("i",$lastPost);
					if($stmt->execute()){
						$result = $stmt->get_result();

						if($result->num_rows){
							while($row = $result->fetch_assoc()){
								$entry = FeedEntry::getEntryFromData($row["postID"],$row["id"],$row["postText"],null,$row["sharedPost"],$row["sessionId"],"POST",$row["count.replies"],$row["count.shares"],$row["count.favorites"],$row["attachments"],$row["postTime"]);

								array_push($posts,Util::postJsonData($entry));
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

$app->post("/scripts/mediaUpload",function(){
	$this->response->mime = "json";

	if(Util::isLoggedIn()){
		$user = Util::getCurrentUser();

		if(!empty($_FILES)){
			if(is_array($_FILES) && count($_FILES) > 0){
				$mysqli = Database::Instance()->get();
				$mediaIDs = [];

				foreach($_FILES as $file){
					$tmpName = $file["tmp_name"];
					$fileName = $file["name"];
	
					if(is_uploaded_file($tmpName)){
						$ext = strtolower(pathinfo($fileName,PATHINFO_EXTENSION));
	
						if($ext == "jpg" || $ext == "jpeg" || $ext == "png" || $ext == "gif"){
							if(@getimagesize($tmpName) === false){
								continue;
							}
						} else {
							continue;
						}

						$sha256 = hash("sha256",file_get_contents($tmpName));
						$mediaFile = MediaFile::getMediaFileFromSHA($sha256);
						if(!is_null($mediaFile)){
							array_push($mediaIDs,$mediaFile->getId());
							continue;
						}

						$mediaID = MediaFile::generateNewID();

						$cdnResult = Util::storeFileOnCDN("serv/qpost/media/" . $mediaID . "/",$tmpName);
						if(!is_null($cdnResult)){
							if(isset($cdnResult["url"])){
								$url = $cdnResult["url"];

								$originalUploader = $user->getId();

								$stmt = $mysqli->prepare("INSERT INTO `media` (`id`,`sha256`,`url`,`originalUploader`) VALUES(?,?,?,?);");
								$stmt->bind_param("sssi",$mediaID,$sha256,$url,$originalUploader);
								if($stmt->execute()){
									$mediaFile = MediaFile::getMediaFileFromID($mediaID);

									array_push($mediaIDs,$mediaID);
								} else {
									$stmt->close();
									return json_encode(["error" => "Database error: " . $stmt->error]);
								}

								$stmt->close();
							} else {
								return json_encode(["error" => $cdnResult["error"]]);
							}
						} else {
							return json_encode(["error" => "Failed to upload to CDN"]);
						}
					} else {
						return json_encode(["error" => "Invalid file"]);
					}
				}

				return json_encode(["ids" => $mediaIDs]);
			} else {
				return json_encode(["error" => "No filed passed"]);
			}
		} else {
			return json_encode(["error" => "No filed passed"]);
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

				$jsonData = Util::postJsonData($postId,0,658,394);

				$replies = [];
				if($post->getReplies() > 0){
					$mysqli = Database::Instance()->get();
						
					$postId = $post->getId();
					$uid = Util::isLoggedIn() ? Util::getCurrentUser()->getId() : -1;

					$stmt = $mysqli->prepare("SELECT f.*,u.`id` AS `userId`,u.`displayName`,u.`username`,u.`email`,u.`avatar`,u.`bio`,u.`token`,u.`birthday`,u.`privacy.level`,u.`featuredBox.title`,u.`featuredBox.content`,u.`lastGigadriveUpdate`,u.`gigadriveJoinDate`,u.`time` AS `userTime`,u.`password`,u.`emailActivated`,u.`emailActivationToken`,u.`gigadriveId`,u.`lastUsernameChange` FROM `feed` AS f INNER JOIN `users` AS u ON f.user = u.id WHERE f.`post` = ? AND f.`type` = 'POST' ORDER BY u.`id` = ?,f.`time` DESC");
					$stmt->bind_param("ii",$postId,$uid);
					if($stmt->execute()){
						$result = $stmt->get_result();

						if($result->num_rows){
							while($row = $result->fetch_assoc()){
								$f = FeedEntry::getEntryFromData($row["id"],$row["user"],$row["text"],$row["following"],$row["post"],$row["sessionId"],$row["type"],$row["count.replies"],$row["count.shares"],$row["count.favorites"],$row["attachments"],$row["time"]);
								$u = User::getUserByData($row["userId"],$row["gigadriveId"],$row["displayName"],$row["username"],$row["password"],$row["email"],$row["avatar"],$row["bio"],$row["token"],$row["birthday"],$row["privacy.level"],$row["featuredBox.title"],$row["featuredBox.content"],$row["lastGigadriveUpdate"],$row["gigadriveJoinDate"],$row["userTime"],$row["emailActivated"],$row["emailActivationToken"],$row["lastUsernameChange"]);

								array_push($replies,Util::postJsonData($f,0,394));
							}
						}
					}
					$stmt->close();
				}

				$jsonData["followButton"] = $followButton;
				$jsonData["replies"] = $replies;
				$jsonData["postForm"] = Util::renderCreatePostForm(["replyForm","my-2"],false);

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

$app->post("/scripts/loadBirthdays",function(){
	$this->response->mime = "json";

	if(isset($_POST["dateString"])){
		$dateString = trim($_POST["dateString"]);

		if(strlen($dateString) == strlen("2018-01-01")){
			if(Util::isLoggedIn()){
				$user = Util::getCurrentUser();
	
				if(!is_null($user)){
					$birthdayUsers = [];
					$n = "birthdayUsers_" . $user->getId();
	
					if(CacheHandler::existsInCache($n)){
						$birthdayUsers = CacheHandler::getFromCache($n);
					} else {
						$followingArray = $user->getFollowingAsArray();
	
						if(count($followingArray) > 0){
							$mysqli = Database::Instance()->get();
	
							$i = $mysqli->real_escape_string(implode(",",$user->getFollowingAsArray()));
	
							$stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `id` IN ($i) AND `birthday` IS NOT NULL AND DATE_FORMAT(`birthday`,'%m-%d') = DATE_FORMAT(?,'%m-%d')");
							$stmt->bind_param("s",$dateString);
							if($stmt->execute()){
								$result = $stmt->get_result();

								if($result->num_rows){
									while($row = $result->fetch_assoc()){
										array_push($birthdayUsers,User::getUserByData($row["id"],$row["gigadriveId"],$row["displayName"],$row["username"],$row["password"],$row["email"],$row["avatar"],$row["bio"],$row["token"],$row["birthday"],$row["privacy.level"],$row["featuredBox.title"],$row["featuredBox.content"],$row["lastGigadriveUpdate"],$row["gigadriveJoinDate"],$row["time"],$row["emailActivated"],$row["emailActivationToken"],$row["lastUsernameChange"]));
									}
								}
							}
							$stmt->close();

							CacheHandler::setToCache($n,$birthdayUsers,5*60);
						}
					}
	
					$html = "";
	
					if(count($birthdayUsers) > 0){
						$html .= '<div class="card mb-3">';

						$html .= '<div class="card-header">Today\'s birthdays</div>';

						foreach($birthdayUsers as $birthdayUser){
							$html .= '<div class="px-2 py-1 my-1" style="height: 70px">';
							$html .= '<a href="/' . $birthdayUser->getUsername() . '" class="clearUnderline float-left">';
							$html .= '<img src="' . $birthdayUser->getAvatarURL() . '" width="64" height="64" class="rounded"/>';
							$html .= '</a>';

							$html .= '<div class="ml-2 float-left">';
							$html .= '<a href="/' . $birthdayUser->getUsername() . '" class="clearUnderline">';
							$html .= '<div class="font-weight-bold float-left small mt-1" style="max-width: 100px; overflow: hidden !important; text-overflow: ellipsis !important; white-space: nowrap !important; word-wrap: normal !important;">' . $birthdayUser->getDisplayName() . '</div>';
							$html .= '<div class="text-muted small float-right mt-1 ml-1" style="max-width: 80px; overflow: hidden !important; text-overflow: ellipsis !important; white-space: nowrap !important; word-wrap: normal !important;">@' . $birthdayUser->getUsername() . '</div><br/>';
							$html .= '</a>';

							$html .= Util::followButton($birthdayUser->getId(),true,["mt-0","btn-sm","ignoreParentClick"]);
							$html .= '</div>';
							$html .= '</div>';
						}

						$html .= '</div>';
					}
	
					return json_encode(["results" => count($birthdayUsers),"html" => $html]);
				} else {
					return json_encode(["error" => "Not logged in"]);
				}
			} else {
				return json_encode(["error" => "Not logged in"]);
			}
		} else {
			return json_encode(["error" => "Bad request"]);
		}
	} else {
		return json_encode(["error" => "Bad request"]);
	}
});

$app->post("/scripts/mediaInfo",function(){
	$this->response->mime = "json";

	if(isset($_POST["postId"]) && isset($_POST["mediaId"])){
		$postId = $_POST["postId"];
		$post = FeedEntry::getEntryById($postId);

		if(!is_null($post)){
			$user = $post->getUser();

			if(!is_null($user)){
				$mediaFile = MediaFile::getMediaFileFromID($_POST["mediaId"]);

				if(!is_null($mediaFile)){
					$followButton = Util::followButton($user,false,["float-right"]);

					if(is_null($followButton))
						$followButton = "";

					$postJsonData = Util::postJsonData($postId);
					$postJsonData["limitedHtml"] = $post->toListHTML(658,true);
					$mediaJsonData = Util::mediaJsonData($_POST["mediaId"],$_POST["postId"]);

					return json_encode(["post" => $postJsonData,"attachment" => $mediaJsonData]);
				} else {
					return json_encode(["error" => "File not found"]);
				}
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

$app->bind("/mediaThumbnail", function($params){
	$id = isset($_GET["id"]) ? $_GET["id"] : null;
	if(!is_null($id) && !empty($id)){
		$mediaFile = MediaFile::getMediaFileFromID($id);

		if($mediaFile != null){
			$url = $mediaFile->getURL();

			$n = "thumbnail_" . $mediaFile->getId();

			$this->response->mime = "jpg";

			if(\CacheHandler::existsInCache($n)){
				return imagejpeg(imagecreatefromstring(base64_decode(\CacheHandler::getFromCache($n))));
			} else {
				$imageString = file_get_contents($url);
				$size = getimagesizefromstring($imageString);

				if($size !== false){
					$width = -1;
					$height = -1;
					list($width,$height) = $size;

					if($width > -1 && $height > -1){
						$source = imagecreatefromstring($imageString);

						$virtualImage = imagecreatetruecolor(100,100);
						imagecopyresampled($virtualImage,$source,0,0,0,0,100,100,$width,$height);

						ob_start();
						imagejpeg($virtualImage);
						$imgSource = ob_get_clean();

						\CacheHandler::setToCache($n,base64_encode($imgSource),20*60);

						return imagejpeg($virtualImage);
					} else {
						return $this->reroute("/");
					}
				} else {
					return $this->reroute("/");
				}
			}
		} else {
			return $this->reroute("/");
		}
	} else {
		return $this->reroute("/");
	}
});

$app->post("/scripts/createPost",function(){
	$this->response->mime = "json";
	
	if(isset($_POST["text"])){
		if(Util::isLoggedIn()){
			$user = Util::getCurrentUser();
			$text = trim($_POST["text"]);

			$mentioned = Util::getUsersMentioned($text);

			$mysqli = Database::Instance()->get();

			if(strlen($text) <= POST_CHARACTER_LIMIT){
				if(count($mentioned) < 15){
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

					$furtherProccess = true;

					$attachments = null;
					if(isset($_POST["attachments"]) && !empty($_POST["attachments"])){
						if(Util::isValidJSON($_POST["attachments"]) && is_array(json_decode($_POST["attachments"],true))){
							$attachments = json_decode($_POST["attachments"],true);

							foreach($attachments as $attachment){
								if(is_string($attachment)){
									$mediaFile = MediaFile::getMediaFileFromID($attachment);

									if(is_null($mediaFile)){
										return json_encode(["error" => "Invalid attachment ID " . $_POST["attachments"]]);
									}
								} else {
									return json_encode(["error" => "Invalid attachment ID " . $_POST["attachments"]]);
								}

								$furtherProccess = false;
							}
						}
					}
					
					if($furtherProccess){
						if(isset($_POST["videoURL"]) && !is_null($_POST["videoURL"])){
							$videoURL = trim($_POST["videoURL"]);
	
							if(!empty($videoURL)){
								if(Util::isValidVideoURL($videoURL)){
									$sha = hash("sha256",$videoURL);
	
									$mediaFile = MediaFile::getMediaFileFromSHA($sha);
									if(is_null($mediaFile)){
										$mediaID = MediaFile::generateNewID();
	
										$stmt = $mysqli->prepare("INSERT INTO `media` (`id`,`sha256`,`url`,`originalUploader`,`type`) VALUES(?,?,?,?,'VIDEO');");
										$stmt->bind_param("sssi",$mediaID,$sha,$videoURL,$userId);
										if($stmt->execute()){
											$mediaFile = MediaFile::getMediaFileFromID($mediaID);
										} else {
											$a = json_encode(["error" => "Database error: " . $stmt->error]);
											$stmt->close();
											return $a;
										}
	
										$stmt->close();
									}
	
									if(!is_null($mediaFile)){
										if(is_null($attachments)) $attachments = [];
	
										array_push($attachments,$mediaFile->getId());
									}
								}
							}
						} else if(isset($_POST["linkURL"]) && !is_null($_POST["linkURL"])){
							$linkURL = trim($_POST["linkURL"]);
	
							if(!empty($linkURL)){
								if(filter_var($linkURL,FILTER_VALIDATE_URL)){
									$sha = hash("sha256",$linkURL);
	
									$mediaFile = MediaFile::getMediaFileFromSHA($sha);
									if(is_null($mediaFile)){
										$mediaID = MediaFile::generateNewID();
	
										$type = Util::isValidVideoURL($linkURL) ? "VIDEO" : "LINK";
	
										$stmt = $mysqli->prepare("INSERT INTO `media` (`id`,`sha256`,`url`,`originalUploader`,`type`) VALUES(?,?,?,?,?);");
										$stmt->bind_param("sssis",$mediaID,$sha,$linkURL,$userId,$type);
										if($stmt->execute()){
											$mediaFile = MediaFile::getMediaFileFromID($mediaID);
										} else {
											$a = json_encode(["error" => "Database error: " . $stmt->error]);
											$stmt->close();
											return $a;
										}
	
										$stmt->close();
									}
	
									if(!is_null($mediaFile)){
										if(is_null($attachments)) $attachments = [];
	
										array_push($attachments,$mediaFile->getId());
									}
								}
							}
						}
					}

					$attachmentsString = is_null($attachments) ? "[]" : json_encode($attachments);

					$mysqli = Database::Instance()->get();
					$stmt = $mysqli->prepare("INSERT INTO `feed` (`user`,`text`,`following`,`sessionId`,`type`,`post`,`attachments`) VALUES(?,?,NULL,?,?,?,?);");
					$stmt->bind_param("isssis", $userId,$text,$sessionId,$type,$parent,$attachmentsString);
					if($stmt->execute()){
						$postId = $stmt->insert_id;
					}
					$stmt->close();

					if(!is_null($postId)){
						$postData = FeedEntry::getEntryById($postId);
						$post = Util::postJsonData($postData);

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
<?php

namespace qpost\Router;

use DateTime;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\QueryBuilder;
use Gumlet\ImageResize;
use qpost\Account\Follower;
use qpost\Account\FollowRequest;
use qpost\Account\PrivacyLevel;
use qpost\Account\User;
use qpost\Block\Block;
use qpost\Cache\CacheHandler;
use qpost\Database\Database;
use qpost\Database\EntityManager;
use qpost\Feed\Favorite;
use qpost\Feed\FeedEntry;
use qpost\Feed\FeedEntryType;
use qpost\Feed\Notification;
use qpost\Feed\NotificationType;
use qpost\Feed\Share;
use qpost\Media\Attachment;
use qpost\Media\MediaFile;
use qpost\Util\Util;

create_route_post("/scripts/toggleFollow", function () {
	$this->response->mime ="json";

	if(isset($_POST["user"])){
		if(Util::isLoggedIn()){
			$entityManager = EntityManager::instance();

			$user = Util::getCurrentUser();
			/**
			 * @var User $toFollow
			 */
			$toFollow = $entityManager->getRepository(User::class)->findOneBy(["id" => $_POST["user"]]);

			if ($user->getFollowingCount() < FOLLOW_LIMIT) {
				if(!is_null($user) && !is_null($toFollow)){
					if($user->getId() != $toFollow->getId()){
						$followStatus = -1;

						if($toFollow->getPrivacyLevel() == PrivacyLevel::PUBLIC){
							if (Follower::isFollowing($user, $toFollow)) {
								Follower::unfollow($user, $toFollow);
								$followStatus = 0;
							} else {
								Follower::follow($user, $toFollow);
								$followStatus = 1;
							}
						} else if($toFollow->getPrivacyLevel() == PrivacyLevel::PRIVATE){
							if (Follower::isFollowing($user, $toFollow)) {
								Follower::unfollow($user, $toFollow);
								$followStatus = 0;
							} else {
								if (!!FollowRequest::hasSentFollowRequest($user, $toFollow)) {
									$request = new FollowRequest();
									$entityManager->persist($request->setFrom($user)->setTo($toFollow)->setTime(new DateTime("now")));

									$followStatus = 2;
								} else {
									$request = $entityManager->getRepository(FollowRequest::class)->findOneBy([
										"from" => $user,
										"to" => $toFollow
									]);

									if ($request) {
										$entityManager->remove($request);
									}

									$followStatus = 0;
								}
							}
						}

						$entityManager->flush();

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

create_route_post("/scripts/deletePost", function () {
	$this->response->mime = "json";

	if(isset($_POST["post"])){
		if(Util::isLoggedIn()){
			$user = Util::getCurrentUser();

			/**
			 * @var FeedEntry $post
			 */
			$post = EntityManager::instance()->getRepository(FeedEntry::class)->findBy(["id" => $_POST["post"]]);

			if(is_null($post))
				return json_encode(["error" => "Unknown post"]);

			if ($post->getUser()->getId() == $user->getId() && ($post->getType() == FeedEntryType::POST || $post->getType() == FeedEntryType::NEW_FOLLOWING)) {
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

create_route_post("/scripts/toggleFavorite", function () {
	$this->response->mime = "json";

	if(isset($_POST["post"])){
		if(Util::isLoggedIn()){
			$user = Util::getCurrentUser();

			/**
			 * @var FeedEntry $post
			 */
			$post = EntityManager::instance()->getRepository(FeedEntry::class)->findOneBy(["id" => $_POST["post"]]);

			if(is_null($post))
				return json_encode(["error" => "Unknown post"]);

			if (Favorite::hasFavorited($user, $post)) {
				if (Favorite::unfavorite($user, $post)) {
					return json_encode([
						"status" => "Favorite removed",
						"replies" => $post->getReplyCount(),
						"shares" => $post->getShareCount(),
						"favorites" => $post->getFavoriteCount()
					]);
				} else {
					return json_encode(["error" => "An error occurred"]);
				}
			} else {
				if (Favorite::favorite($user, $post)) {
					return json_encode([
						"status" => "Favorite added",
						"replies" => $post->getReplyCount(),
						"shares" => $post->getShareCount(),
						"favorites" => $post->getFavoriteCount()
					]);
				} else {
					return json_encode(["error" => "An error occurred"]);
				}
			}
		} else {
			return json_encode(["error" => "Not logged in"]);
		}
	} else {
		return json_encode(["error" => "Bad request"]);
	}
});

create_route_post("/scripts/toggleShare", function () {
	$this->response->mime ="json";

	if(isset($_POST["post"])){
		if(Util::isLoggedIn()){
			$user = Util::getCurrentUser();

			/**
			 * @var FeedEntry $post
			 */
			$post = EntityManager::instance()->getRepository(FeedEntry::class)->findOneBy(["id" => $_POST["post"]]);

			if(is_null($post))
				return json_encode(["error" => "Unknown post"]);

			if ($post->getUser()->getId() == $user->getId())
				return json_encode(["error" => "Cant share own post"]);

			if (Share::hasShared($user, $post)) {
				if($post->getUser()->getPrivacyLevel() == PrivacyLevel::PUBLIC){
					Share::unshare($user, $post);

					return json_encode([
						"status" => "Share removed",
						"replies" => $post->getReplyCount(),
						"shares" => $post->getShareCount(),
						"favorites" => $post->getFavoriteCount()
					]);
				} else {
					return json_encode(["error" => "Invalid privacy level"]);
				}
			} else {
				Share::share($user, $post);

				return json_encode([
					"status" => "Share added",
					"replies" => $post->getReplyCount(),
					"shares" => $post->getShareCount(),
					"favorites" => $post->getFavoriteCount()
				]);
			}
		} else {
			return json_encode(["error" => "Not logged in"]);
		}
	} else {
		return json_encode(["error" => "Bad request"]);
	}
});

create_route_get("/scripts/desktopNotifications", function () {
	$this->response->mime = "json";

	if(Util::isLoggedIn()){
		$currentUser = Util::getCurrentUser();
		$entityManager = EntityManager::instance();

		$notifications = [];

		/**
		 * @var Notification[] $foundNotifications
		 */
		$foundNotifications = $entityManager->getRepository(Notification::class)->findBy([
			"user" => $currentUser,
			"notified" => false
		]);

		foreach ($foundNotifications as $notification) {
			array_push($notifications, [
				"id" => $notification->getId(),
				"user" => Util::userJsonData($notification->getUser()),
				"type" => $notification->getType(),
				"follower" => Util::userJsonData($notification->getFollower()),
				"post" => Util::postJsonData($notification->getPost()),
				"time" => $notification->getTime()
			]);

			$notification->setNotified(true);
			$entityManager->persist($notification);
		}

		$entityManager->flush();

		return json_encode([
			"notifications" => $notifications,
			"unreadCount" => Util::getCurrentUser()->getUnreadNotifications()
		]);
	} else {
		return json_encode(["error" => "Not logged in"]);
	}
});

create_route_post("/scripts/validateVideoURL", function () {
	$this->response->mime = "json";

	if(Util::isLoggedIn()){
		if(isset($_POST["videoURL"])){
			return json_encode(["status" => Util::isValidVideoURL($_POST["videoURL"]) ? "valid" : "invalid"]);
		} else {
			return json_encode(["error" => "Bad request"]);
		}
	} else {
		return json_encode(["error" => "Not logged in"]);
	}
});

function home_feed_query(User $currentUser): QueryBuilder {
	return EntityManager::instance()->getRepository(FeedEntry::class)->createQueryBuilder("f")
		->innerJoin("f.user", "u")
		->where("u.privacyLevel != :closed")
		->setParameter("closed", PrivacyLevel::CLOSED, Type::STRING)
		->andWhere("f.post is null")
		->andWhere("f.type = :post or f.type = :share")
		->setParameter("post", FeedEntryType::POST, Type::STRING)
		->setParameter("share", FeedEntryType::SHARE, Type::STRING)
		->andWhere("exists (select 1 from qpost\Account\Follower ff where ff.to = :to) or f.user = :to")
		->setParameter("to", $currentUser)
		->orderBy("f.time", "DESC")
		->setMaxResults(30);
}

create_route_post("/scripts/extendHomeFeed", function () {
	$this->response->mime = "json";

	if(Util::isLoggedIn()){
		$currentUser = Util::getCurrentUser();

		if(isset($_POST["mode"])){
			if($_POST["mode"] == "loadOld"){
				if(isset($_POST["firstPost"])){
					$posts = [];
					$firstPost = (int)$_POST["firstPost"];

					/**
					 * @var FeedEntry[] $feedEntries
					 */
					$feedEntries = home_feed_query($currentUser)
						->andWhere("f.id < :id")
						->setParameter("id", $firstPost, Type::INTEGER)
						->getQuery()
						->getResult();

					foreach ($feedEntries as $feedEntry) {
						array_push($posts, Util::postJsonData($feedEntry));
					}

					return json_encode(["result" => $posts]);
				} else {
					return json_encode(["error" => "Bad request"]);
				}
			} else if($_POST["mode"] == "loadNew"){
				if(isset($_POST["lastPost"])){
					$posts = [];
					$lastPost = (int)$_POST["lastPost"];

					/**
					 * @var FeedEntry[] $feedEntries
					 */
					$feedEntries = home_feed_query($currentUser)
						->andWhere("f.id > :id")
						->setParameter("id", $lastPost, Type::INTEGER)
						->getQuery()
						->getResult();

					foreach ($feedEntries as $feedEntry) {
						array_push($posts, Util::postJsonData($feedEntry));
					}

					return json_encode(["result" => $posts]);
				} else {
					return json_encode(["error" => "Bad request"]);
				}
			} else if($_POST["mode"] == "loadFirst"){
				$posts = [];

				/**
				 * @var FeedEntry[] $feedEntries
				 */
				$feedEntries = home_feed_query($currentUser)
					->getQuery()
					->getResult();

				foreach ($feedEntries as $feedEntry) {
					array_push($posts, Util::postJsonData($feedEntry));
				}

				return json_encode(["result" => $posts]);
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

create_route_post("/scripts/mediaUpload", function () {
	$this->response->mime = "json";

	if(Util::isLoggedIn()){
		$user = Util::getCurrentUser();

		if(!Util::isEmpty($_FILES)){
			if(is_array($_FILES) && count($_FILES) > 0){
				$entityManager = EntityManager::instance();
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

						/**
						 * @var MediaFile $mediaFile
						 */
						$mediaFile = $entityManager->getRepository(MediaFile::class)->findOneBy(["sha256" => $sha256]);

						if(!is_null($mediaFile)){
							array_push($mediaIDs,$mediaFile->getId());
							continue;
						}

						$cdnResult = Util::storeFileOnCDN($tmpName);
						if(!is_null($cdnResult)){
							if(isset($cdnResult["url"])){
								$url = $cdnResult["url"];

								$mediaFile = new MediaFile();

								$mediaFile->setSHA256($sha256)
									->setURL($url)
									->setOriginalUploader($user);

								$entityManager->persist($mediaFile);

								array_push($mediaIDs, $mediaFile->getId());
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

create_route_post("/scripts/postInfo", function () {
	$this->response->mime = "json";

	if(isset($_POST["postId"])){
		$entityManager = EntityManager::instance();

		$postId = $_POST["postId"];

		/**
		 * @var FeedEntry $post
		 */
		$post = $entityManager->getRepository(FeedEntry::class)->findOneBy(["id" => $postId]);

		if(!is_null($post)){
			$user = $post->getUser();

			if(!is_null($user)){
				if($post->mayView()){
					$followButton = Util::followButton($user,false,["float-right","mt-2"]);

					if(is_null($followButton))
						$followButton = "";

					$jsonData = Util::postJsonData($post, 0);

					$replyData = [];

					/**
					 * @var FeedEntry[] $replies
					 */
					$replies = $entityManager->getRepository(FeedEntry::class)->findBy([
						"post" => $post,
						"type" => FeedEntryType::POST
					]);

					if(!is_null($replies)){
						foreach ($replies as $reply){
							array_push($replyData,Util::postJsonData($reply,0));
						}
					}

					$jsonData["followButton"] = $followButton;
					$jsonData["replies"] = $replyData;
					$jsonData["postForm"] = '<div class="pt-1">' . Util::renderCreatePostForm(["replyForm","my-2"],false) . '</div>';

					return json_encode($jsonData);
				} else {
					return json_encode(["error" => "You may not view this post."]);
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

create_route_post("/scripts/loadBirthdays", function () {
	$this->response->mime = "json";

	if(isset($_POST["dateString"])){
		$dateString = trim($_POST["dateString"]);

		if(strlen($dateString) == strlen("2018-01-01")){
			if(Util::isLoggedIn()){
				$user = Util::getCurrentUser();

				if(!is_null($user)){
					$birthdayUsers = [];
					$entityManager = EntityManager::instance();

					/**
					 * @var User[] $users
					 */
					$users = $entityManager->getRepository(User::class)->createQueryBuilder("u")
						->where("exists (select 1 from qpost\Account\Follower f where f.to = :to)")
						->setParameter("to", $user)
						->andWhere("u.birthday is not null")
						->andWhere("DATE_FORMAT(u.birthday,'%m-%d') = DATE_FORMAT(:birthday,'%m-%d')")
						->setParameter("birthday", $user->getBirthday(), Type::STRING)
						->getQuery()
						->getResult();

					foreach ($users as $u) {
						array_push($birthdayUsers, $u);
					}

					$html = "";

					if(count($birthdayUsers) > 0){
						$html .= '<div class="card my-3">';

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

create_route_post("/scripts/mediaInfo", function () {
	$this->response->mime = "json";

	if(isset($_POST["postId"]) && isset($_POST["mediaId"])){
		$entityManager = EntityManager::instance();

		$postId = $_POST["postId"];

		/**
		 * @var FeedEntry $post
		 */
		$post = $entityManager->getRepository(FeedEntry::class)->findOneBy(["id" => $postId]);

		if(!is_null($post)){
			$user = $post->getUser();

			if(!is_null($user)){
				/**
				 * @var MediaFile $mediaFile
				 */
				$mediaFile = $entityManager->getRepository(MediaFile::class)->findOneBy(["id" => $_POST["mediaId"]]);

				if(!is_null($mediaFile)){
					$followButton = Util::followButton($user,false,["float-right"]);

					if(is_null($followButton))
						$followButton = "";

					$postJsonData = Util::postJsonData($postId);
					$postJsonData["limitedHtml"] = $post->toListHTML(true,true);
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

create_route("/mediaThumbnail", function () {
	$id = isset($_GET["id"]) ? $_GET["id"] : null;
	if(!is_null($id) && !Util::isEmpty($id)){
		$entityManager = EntityManager::instance();

		/**
		 * @var MediaFile $mediaFile
		 */
		$mediaFile = $entityManager->getRepository(MediaFile::class)->findOneBy(["id" => $id]);

		if($mediaFile != null){
			$url = $mediaFile->getURL();

			$n = "thumbnail_" . $mediaFile->getId();

			$this->response->mime = "jpg";

			if (CacheHandler::existsInCache($n)) {
				$imageString = base64_decode(CacheHandler::getFromCache($n));

				$image = ImageResize::createFromString($imageString);

				if(!is_null($image)){
					$image->output(IMAGETYPE_JPEG, 100);
					return "";
				} else {
					return $this->reroute("/");
				}
			} else {
				if($mediaFile->getType() == "IMAGE"){
					$imageString = file_get_contents($url);
					$size = getimagesizefromstring($imageString);

					if($size !== false){
						$width = -1;
						$height = -1;
						list($width,$height) = $size;

						if($width > -1 && $height > -1){
							$image = ImageResize::createFromString($imageString);

							if(!is_null($image)){
								/*$source = imagecreatefromstring($imageString);

								$virtualImage = imagecreatetruecolor(100,100);
								imagecopyresampled($virtualImage,$source,0,0,0,0,100,100,$width,$height);*/

								$image->resizeToBestFit(800,450,true);

								$imageString = $image->getImageAsString();

								CacheHandler::setToCache($n, base64_encode($imageString), 20 * 60);

								$image = ImageResize::createFromString(base64_decode(CacheHandler::getFromCache($n)));

								$image->output(IMAGETYPE_JPEG, 100);
								return "";
							} else {
								return $this->reroute("/");
							}
						} else {
							return $this->reroute("/");
						}
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

create_route_post("/scripts/favoriteSample", function () {
	$this->response->mime = "json";

	if(isset($_POST["post"])){
		$entityManager = EntityManager::instance();
		$postId = $_POST["post"];

		/**
		 * @var FeedEntry $post
		 */
		$post = $entityManager->getRepository(FeedEntry::class)->findOneBy(["id" => $postId]);
		if(!is_null($post)){
			if ($post->getType() == FeedEntryType::POST) {
				if($post->mayView()){
					$favoriteSample = $post->getFavoriteSample();

					if(!is_null($favoriteSample) && !is_null($favoriteSample->getUsers())){
						$users = [];

						foreach($favoriteSample->getUsers() as $user){
							array_push($users,[
								"username" => Util::sanatizeString($user->getUsername()),
								"displayName" => Util::sanatizeString($user->getDisplayName())
							]);
						}

						return json_encode([
							"users" => $users,
							"showMore" => $favoriteSample->showsMore(),
							"showMoreCount" => $favoriteSample->getShowMoreCount()
						]);
					} else {
						return json_encode(["error" => "Failed to load"]);
					}
				} else {
					return json_encode(["error" => "Unknown post"]);
				}
			} else {
				return json_encode(["error" => "Unknown post"]);
			}
		} else {
			return json_encode(["error" => "Unknown post"]);
		}
	} else {
		return json_encode(["error" => "Bad request"]);
	}
});

create_route_post("/scripts/shareSample", function () {
	$this->response->mime = "json";

	if(isset($_POST["post"])){
		$entityManager = EntityManager::instance();
		$postId = $_POST["post"];

		/**
		 * @var FeedEntry $post
		 */
		$post = $entityManager->getRepository(FeedEntry::class)->findOneBy(["id" => $postId]);

		if(!is_null($post)){
			if ($post->getType() == FeedEntryType::POST) {
				if($post->mayView()){
					$shareSample = $post->getShareSample();

					if(!is_null($shareSample) && !is_null($shareSample->getUsers())){
						$users = [];

						foreach($shareSample->getUsers() as $user){
							array_push($users,$user->toAPIJson(Util::getCurrentUser(),false,false));
						}

						return json_encode([
							"users" => $users,
							"showMore" => $shareSample->showsMore(),
							"showMoreCount" => $shareSample->getShowMoreCount()
						]);
					} else {
						return json_encode(["error" => "Failed to load"]);
					}
				} else {
					return json_encode(["error" => "Unknown post"]);
				}
			} else {
				return json_encode(["error" => "Unknown post"]);
			}
		} else {
			return json_encode(["error" => "Unknown post"]);
		}
	} else {
		return json_encode(["error" => "Bad request"]);
	}
});

create_route_post("/scripts/createPost", function () {
	$this->response->mime = "json";

	if(isset($_POST["text"])){
		if(Util::isLoggedIn()){
			$user = Util::getCurrentUser();

			if(!$user->isSuspended()){
				$text = trim(Util::fixString($_POST["text"]));

				$mentioned = Util::getUsersMentioned($text);

				$mysqli = Database::Instance()->get();

				if(strlen($text) <= Util::getCharacterLimit()){
					if(Util::isEmpty($text)) $text = null;

					if(count($mentioned) < 15){
						$entityManager = EntityManager::instance();

						$sessionId = Util::getCurrentToken()->getId();
						$type = FeedEntryType::POST;

						$postId = null;

						/**
						 * @var FeedEntry $parent
						 */
						$parent = isset($_POST["replyTo"]) ? $entityManager->getRepository(FeedEntry::class)->findOneBy(["id" => $_POST["replyTo"]]) : null;

						if (!is_null($parent)) {
							$parentCreator = $parent->getUser();
							if ($parentCreator->getPrivacyLevel() == PrivacyLevel::CLOSED && $parentCreator->getId() != $user->getId()) return json_encode(["error" => "Parent owner is closed"]);
							if ($parentCreator->getPrivacyLevel() == PrivacyLevel::PRIVATE && !Follower::isFollowing($user, $parentCreator)) return json_encode(["error" => "Parent owner is private and not followed"]);
							if (Block::hasBlocked($parentCreator, $user)) return json_encode(["error" => "Parent has blocked"]);
						}

						/**
						 * @var bool $nsfw
						 */
						$nsfw = isset($_POST["nsfw"]) && $_POST["nsfw"] == "true";

						$furtherProccess = true;

						/**
						 * @var MediaFile[] $mediaFiles
						 */
						$mediaFiles = [];

						if(isset($_POST["attachments"]) && !Util::isEmpty($_POST["attachments"])){
							if (Util::isValidJSON($_POST["attachments"])) {
								$attachments = json_decode($_POST["attachments"],true);

								if (is_array($attachments)) {
									foreach ($attachments as $attachment) {
										if (is_string($attachment)) {
											/**
											 * @var MediaFile $mediaFile
											 */
											$mediaFile = $entityManager->getRepository(MediaFile::class)->findOneBy(["id" => $attachment]);

											if (is_null($mediaFile)) {
												return json_encode(["error" => "Invalid attachment ID " . $attachment]);
											} else {
												array_push($mediaFiles, $mediaFile);
											}
										} else {
											return json_encode(["error" => "Invalid attachment ID " . $_POST["attachments"]]);
										}

										$furtherProccess = false;
									}
								}
							}
						}

						if($furtherProccess){
							if(isset($_POST["videoURL"]) && !is_null($_POST["videoURL"])){
								$videoURL = trim($_POST["videoURL"]);

								if(!Util::isEmpty($videoURL)){
									if(Util::isValidVideoURL($videoURL)){
										$videoURL = Util::stripUnneededInfoFromVideoURL($videoURL);
										$sha = hash("sha256",$videoURL);

										/**
										 * @var MediaFile $mediaFile
										 */
										$mediaFile = $entityManager->getRepository(MediaFile::class)->findOneBy(["sha256" => $sha]);

										if(is_null($mediaFile)){
											$mediaFile = new MediaFile();

											$mediaFile->setType("VIDEO")
												->setOriginalUploader($user)
												->setURL($videoURL)
												->setSHA256($sha);

											$entityManager->persist($mediaFile);
										}

										if(!is_null($mediaFile)){
											array_push($mediaFiles, $mediaFile);
										}
									}
								}
							} else if(isset($_POST["linkURL"]) && !is_null($_POST["linkURL"])){
								$linkURL = trim($_POST["linkURL"]);

								if(!Util::isEmpty($linkURL)){
									if(filter_var($linkURL,FILTER_VALIDATE_URL)){
										$sha = hash("sha256",$linkURL);

										/**
										 * @var MediaFile $mediaFile
										 */
										$mediaFile = $entityManager->getRepository(MediaFile::class)->findOneBy(["sha256" => $sha]);

										if(is_null($mediaFile)){
											$type = Util::isValidVideoURL($linkURL) ? "VIDEO" : "LINK";

											$mediaFile = new MediaFile();

											$mediaFile->setType($type)
												->setOriginalUploader($user)
												->setURL($linkURL)
												->setSHA256($sha);

											$entityManager->persist($mediaFile);
										}

										if(!is_null($mediaFile)){
											array_push($mediaFiles, $mediaFile);
										}
									}
								}
							}
						}

						$feedEntry = new FeedEntry();

						$feedEntry->setUser($user)
							->setText($text)
							->setSessionId($sessionId)
							->setType($type)
							->setPost($parent)
							->setNSFW($nsfw)
							->setTime(new DateTime("now"));

						foreach ($mediaFiles as $mediaFile) {
							$attachment = new Attachment();

							$attachment->setPost($feedEntry)
								->setMediaFile($mediaFile);

							$entityManager->persist($attachment);
							$feedEntry->addAttachment($attachment);
						}

						$entityManager->persist($feedEntry);

						$entityManager->flush();

						$post = Util::postJsonData($feedEntry, 0);

						if (!is_null($parent)) {
							if ($parent->getUser()->getId() != $user->getId()) {
								$notification = new Notification();

								$notification->setUser($parent->getUser())
									->setType(NotificationType::REPLY)
									->setPost($feedEntry)
									->setTime(new DateTime("now"));

								$entityManager->persist($notification);
							}
						}

						if (count($mentioned) > 0) {
							foreach ($mentioned as $u) {
								if ($u->getId() === $user->getId()) continue;

								if (!Block::hasBlocked($u, $user)) {
									$notification = new Notification();

									$notification->setUser($u)
										->setType(NotificationType::MENTION)
										->setPost($feedEntry)
										->setTime(new DateTime("now"));

									$entityManager->persist($notification);
								}
							}
						}

						$postActionButtons = Util::getPostActionButtons($feedEntry);

						$entityManager->flush();

						return json_encode(["post" => $post, "postActionButtons" => $postActionButtons]);
					} else {
						return json_encode(["error" => "Too many mentions"]);
					}
				} else {
					return json_encode(["error" => "Exceeded character limit"]);
				}
			} else {
				return json_encode(["error" => "User suspended"]);
			}
		} else {
			return json_encode(["error" => "Not logged in"]);
		}
	} else {
		return json_encode(["error" => "Bad request"]);
	}
});
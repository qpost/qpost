<?php

$user = Util::getCurrentUser();
$num = 0;
$n = "totalNotifications_" . $user->getId();
$mysqli = Database::Instance()->get();
$uID = $user->getId();
$itemsPerPage = 30;

if(CacheHandler::existsInCache($n)){
	$num = CacheHandler::getFromCache($n);
} else {
	$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `notifications` WHERE `user` = ?");
	$stmt->bind_param("i",$uID);
	if($stmt->execute()){
		$result = $stmt->get_result();

		if($result->num_rows){
			$row = $result->fetch_assoc();

			$num = $row["count"];

			CacheHandler::setToCache($n,$num,2*60);
		}
	}
	$stmt->close();
}

?>
<div class="card-body">
	<div class="row">
		<div class="col-lg-8 offset-lg-2 border rounded px-3 py-3">
	<?php

	$notifications = [];

	if($num > 0){
		$stmt = $mysqli->prepare("SELECT * FROM `notifications` WHERE `user` = ? ORDER BY `time` DESC LIMIT " . (($currentPage-1)*$itemsPerPage) . " , " . $itemsPerPage);
		$stmt->bind_param("i",$uID);
		if($stmt->execute()){
			$result = $stmt->get_result();

			if($result->num_rows){
				while($row = $result->fetch_assoc()){
					array_push($notifications,$row);
				}
			}
		}
		$stmt->close();

		if(count($notifications) > 0){
			$l = false;

			foreach($notifications as $notification){
				if($notification["type"] == NOTIFICATION_TYPE_MENTION){
					$l = false;

					$post = FeedEntry::getEntryById($notification["post"]);

					if(!is_null($post)){
						$u = $post->getUser();
						?>
			<div class="card feedEntry<?= !$last ? " mb-2" : "" ?> statusTrigger" data-status-render="<?= $post->getId() ?>" data-entry-id="<?= $post->getId() ?>">
				<div class="card-body">
					<div class="row">
						<div class="col-1">
							<a href="/<?= $u->getUsername(); ?>" class="clearUnderline">
								<img class="rounded mx-1 my-1" src="<?= $u->getAvatarURL(); ?>" width="40" height="40"/>
							</a>
						</div>

						<div class="col-11">
							<p class="mb-0">
								<a href="/<?= $u->getUsername(); ?>" class="clearUnderline">
									<span class="font-weight-bold"><?= $u->getDisplayName(); ?></span>
								</a>

								<span class="text-muted font-weight-normal">@<?= $u->getUsername(); ?></span>

								&bull;

								<?= Util::timeago($post->getTime()); ?>
							</p>

							<p class="mb-0 convertEmoji">
								<?= Util::convertPost($post->getText()); ?>
							</p>

							<?php if(Util::isLoggedIn()){ ?>
							<div class="mt-1 postActionButtons ignoreParentClick float-left">
								<span class="replyButton" data-toggle="tooltip" title="Reply">
									<i class="fas fa-share"></i>
								</span><span class="replyCount small text-primary mr-1">
									<?= $post->getReplies(); ?>
								</span><span<?= Util::getCurrentUser()->getId() != $u->getId() ? ' class="shareButton" data-toggle="tooltip" title="Share"' : ' data-toggle="tooltip" title="You can not share this post"'; ?> data-post-id="<?= $post->getId() ?>">
									<i class="fas fa-share-alt<?= Util::getCurrentUser()->hasShared($post->getId()) ? ' text-primary' : "" ?>"<?= Util::getCurrentUser()->hasShared($post->getId()) ? "" : ' style="color: gray"' ?>></i>
								</span><span class="shareCount small text-primary ml-1 mr-1">
									<?= $post->getShares(); ?>
								</span><span class="favoriteButton" data-post-id="<?= $post->getId() ?>">
									<i class="fas fa-star"<?= Util::getCurrentUser()->hasFavorited($post->getId()) ? ' style="color: gold"' : ' style="color: gray"' ?>></i>
								</span><span class="favoriteCount small ml-1 mr-1" style="color: #ff960c">
									<?= $post->getFavorites(); ?>
								</span>
							</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
						<?php
					}
				} else if($notification["type"] == NOTIFICATION_TYPE_NEW_FOLLOWER){
					$u2 = User::getUserById($notification["follower"]);

					if(!is_null($u2)){
						?>
			<div class="my-1 px-2 py-2 border-top<?= $l ? " border-bottom" : ""; ?>" style="border-color: #CCC;<?= $notification["seen"] == false ? " background: #9FCCFC" : ""; ?>">
				<div class="row">
					<div class="col-lg-1">
						<a href="/<?= $u2->getUsername(); ?>" class="clearUnderline">
							<img class="rounded mx-1 my-1" src="<?= $u2->getAvatarURL(); ?>" width="40" height="40"/>
						</a>
					</div>

					<div class="col-lg-11">
						<div class="text-muted small">New follower - @<?= $u2->getUsername(); ?></div>
						<b><a href="/<?= $u2->getUsername(); ?>" class="clearUnderline"><?= $u2->getDisplayName(); ?></a></b> is now following you. &bull; <span class="text-muted"><?= Util::timeago($notification["time"]); ?></span>
					</div>
				</div>
			</div>
						<?php

						$l = true;
					}
				} else if($notification["type"] == NOTIFICATION_TYPE_FAVORITE){
					$post = FeedEntry::getEntryById($notification["post"]);

					if(!is_null($post)){
						$u = $post->getUser();
						$u2 = User::getUserById($notification["follower"]);
						?>
			<div class="card feedEntry<?= !$last ? " mb-2" : "" ?> statusTrigger" data-status-render="<?= $post->getId() ?>" data-entry-id="<?= $post->getId() ?>">
				<div class="card-body">
					<div class="small text-muted">
						<i class="fas fa-star" style="color:gold"></i> <a href="/<?= $u2->getUsername(); ?>" class="clearUnderline"><img src="<?= $u2->getAvatarURL(); ?>" width="16" height="16"/> <?= $u2->getDisplayName(); ?></a> favorited your post &bull; <?= Util::timeago($notification["time"]); ?>
					</div>
					<hr/>
					<div class="row">
						<div class="col-1">
							<a href="/<?= $u->getUsername(); ?>" class="clearUnderline">
								<img class="rounded mx-1 my-1" src="<?= $u->getAvatarURL(); ?>" width="40" height="40"/>
							</a>
						</div>

						<div class="col-11">
							<p class="mb-0">
								<a href="/<?= $u->getUsername(); ?>" class="clearUnderline">
									<span class="font-weight-bold"><?= $u->getDisplayName(); ?></span>
								</a>

								<span class="text-muted font-weight-normal">@<?= $u->getUsername(); ?></span>

								&bull;

								<?= Util::timeago($post->getTime()); ?>
							</p>

							<p class="mb-0 convertEmoji">
								<?= Util::convertPost($post->getText()); ?>
							</p>

							<?php if(Util::isLoggedIn()){ ?>
							<div class="mt-1 postActionButtons ignoreParentClick float-left">
								<span class="replyButton" data-toggle="tooltip" title="Reply">
									<i class="fas fa-share"></i>
								</span><span class="replyCount small text-primary mr-1">
									<?= $post->getReplies(); ?>
								</span><span<?= Util::getCurrentUser()->getId() != $u->getId() ? ' class="shareButton" data-toggle="tooltip" title="Share"' : ' data-toggle="tooltip" title="You can not share this post"'; ?> data-post-id="<?= $post->getId() ?>">
									<i class="fas fa-share-alt<?= Util::getCurrentUser()->hasShared($post->getId()) ? ' text-primary' : "" ?>"<?= Util::getCurrentUser()->hasShared($post->getId()) ? "" : ' style="color: gray"' ?>></i>
								</span><span class="shareCount small text-primary ml-1 mr-1">
									<?= $post->getShares(); ?>
								</span><span class="favoriteButton" data-post-id="<?= $post->getId() ?>">
									<i class="fas fa-star"<?= Util::getCurrentUser()->hasFavorited($post->getId()) ? ' style="color: gold"' : ' style="color: gray"' ?>></i>
								</span><span class="favoriteCount small ml-1 mr-1" style="color: #ff960c">
									<?= $post->getFavorites(); ?>
								</span>
							</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
						<?php
					}
				} else if($notification["type"] == NOTIFICATION_TYPE_SHARE){
					$post = FeedEntry::getEntryById($notification["post"]);

					if(!is_null($post)){
						$u = $post->getUser();
						$u2 = User::getUserById($notification["follower"]);
						?>
			<div class="card feedEntry<?= !$last ? " mb-2" : "" ?> statusTrigger" data-status-render="<?= $post->getId() ?>" data-entry-id="<?= $post->getId() ?>">
				<div class="card-body">
					<div class="small text-muted">
						<i class="fas fa-share-alt text-primary"></i> <a href="/<?= $u2->getUsername(); ?>" class="clearUnderline"><img src="<?= $u2->getAvatarURL(); ?>" width="16" height="16"/> <?= $u2->getDisplayName(); ?></a> shared your post &bull; <?= Util::timeago($notification["time"]); ?>
					</div>
					<hr/>
					<div class="row">
						<div class="col-1">
							<a href="/<?= $u->getUsername(); ?>" class="clearUnderline">
								<img class="rounded mx-1 my-1" src="<?= $u->getAvatarURL(); ?>" width="40" height="40"/>
							</a>
						</div>

						<div class="col-11">
							<p class="mb-0">
								<a href="/<?= $u->getUsername(); ?>" class="clearUnderline">
									<span class="font-weight-bold"><?= $u->getDisplayName(); ?></span>
								</a>

								<span class="text-muted font-weight-normal">@<?= $u->getUsername(); ?></span>

								&bull;

								<?= Util::timeago($post->getTime()); ?>
							</p>

							<p class="mb-0 convertEmoji">
								<?= Util::convertPost($post->getText()); ?>
							</p>

							<?php if(Util::isLoggedIn()){ ?>
							<div class="mt-1 postActionButtons ignoreParentClick float-left">
								<span class="replyButton" data-toggle="tooltip" title="Reply">
									<i class="fas fa-share"></i>
								</span><span class="replyCount small text-primary mr-1">
									<?= $post->getReplies(); ?>
								</span><span<?= Util::getCurrentUser()->getId() != $u->getId() ? ' class="shareButton" data-toggle="tooltip" title="Share"' : ' data-toggle="tooltip" title="You can not share this post"'; ?> data-post-id="<?= $post->getId() ?>">
									<i class="fas fa-share-alt<?= Util::getCurrentUser()->hasShared($post->getId()) ? ' text-primary' : "" ?>"<?= Util::getCurrentUser()->hasShared($post->getId()) ? "" : ' style="color: gray"' ?>></i>
								</span><span class="shareCount small text-primary ml-1 mr-1">
									<?= $post->getShares(); ?>
								</span><span class="favoriteButton" data-post-id="<?= $post->getId() ?>">
									<i class="fas fa-star"<?= Util::getCurrentUser()->hasFavorited($post->getId()) ? ' style="color: gold"' : ' style="color: gray"' ?>></i>
								</span><span class="favoriteCount small ml-1 mr-1" style="color: #ff960c">
									<?= $post->getFavorites(); ?>
								</span>
							</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
						<?php
					}
				}
			}

			echo Util::paginate($currentPage,$itemsPerPage,$num,"/notifications/(:num)");

			$user->markNotificationsAsRead();
		} else {
			echo Util::createAlert("noNotifications","You have no notifications yet!",ALERT_TYPE_INFO);
		}
	} else {
		echo Util::createAlert("noNotifications","You have no notifications yet!",ALERT_TYPE_INFO);
	}

	?>
		</div>
	</div>
</div>
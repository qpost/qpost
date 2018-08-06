<div class="card-body">
	<div class="row">
		<div class="col-lg-8">
			<?= Util::createAlert("feedInfo","The home feed shows the posts and activities of you and the people you follow on the network. Follow people you are interested in to see what they're doing!",ALERT_TYPE_INFO,true,true); ?>
			<?= Util::createAlert("socialNetFeedInfo","Did you know about the <b>social net</b>? We created that page to suggest users that you might be interested in. <a href=\"/socialnet\">Click here to check it out!</a> (it's still experimental!)",ALERT_TYPE_INFO,true,true) ?>
			<h4>Feed</h4>

			<div class="card my-2 border-primary" style="background: #9FCCFC" id="homePostBox">
				<div class="card-body">
					<textarea class="form-control" id="homePostField" placeholder="Post something for your followers!"></textarea>

					<p class="mb-0 mt-2 float-left small" id="homeCharacterCounter">
						<?= POST_CHARACTER_LIMIT ?> characters left
					</p>

					<button type="button" class="btn btn-primary btn-sm float-right mb-0 mt-2" id="homePostButton">Post</button>
				</div>
			</div>

			<?php

				$user = Util::getCurrentUser();
				$mysqli = Database::Instance()->get();
				
				$a = $user->getFollowingAsArray();
				array_push($a,$user->getId());

				$i = $mysqli->real_escape_string(implode(",",$a));

				$results = [];

				$stmt = $mysqli->prepare("SELECT f.`id` AS `postID`,f.`text` AS `postText`,f.`time` AS `postTime`,f.`sessionId`,f.`post` AS `sharedPost`,f.`type` AS `postType`,f.`count.replies`,f.`count.shares`,f.`count.favorites`,u.* FROM `feed` AS f INNER JOIN `users` AS u ON f.`user` = u.`id` WHERE f.`post` IS NULL AND (f.`type` = 'POST' OR f.`type` = 'SHARE') AND f.`user` IN ($i) AND u.`privacy.level` != 'CLOSED' ORDER BY f.`time` DESC LIMIT 60");
				//$stmt->bind_param("s",$i);
				if($stmt->execute()){
					$result = $stmt->get_result();

					if($result->num_rows){
						while($row = $result->fetch_assoc()){
							array_push($results,[
								"post" => FeedEntry::getEntryFromData($row["postID"],$row["id"],$row["postText"],null,$row["sharedPost"],$row["sessionId"],$row["postType"],$row["count.replies"],$row["count.shares"],$row["count.favorites"],$row["postTime"]),
								"user" => User::getUserByData($row["id"],$row["displayName"],$row["username"],$row["email"],$row["avatar"],$row["bio"],$row["token"],$row["privacy.level"],$row["featuredBox.title"],$row["featuredBox.content"],$row["lastGigadriveUpdate"],$row["gigadriveJoinDate"],$row["time"])
							]);
						}
					}
				}
				$stmt->close();

				if(count($results) > 0){
					echo '<div class="feedContainer">';

					for($i = 0; $i < count($results); $i++){
						$result = $results[$i];
						$post = $result["post"];
						$u = $result["user"];

						$first = $i == 0;
						$last = $i == count($results)-1;

						if($last)
							echo '<script>var HOME_FEED_FIRST_POST = ' . $post->getId() . ';</script>';

						if($first)
							echo '<script>var HOME_FEED_LAST_POST = ' . $post->getId() . ';</script>';

						if($post->getType() == "POST"){

						?>
			<div class="card feedEntry mb-2 statusTrigger" data-status-render="<?= $post->getId() ?>" data-entry-id="<?= $post->getId() ?>">
				<div class="card-body">
					<div class="row">
						<div class="col-1">
							<a href="/<?= $u->getUsername(); ?>" class="clearUnderline ignoreParentClick">
								<img class="rounded mx-1 my-1" src="<?= $u->getAvatarURL(); ?>" width="40" height="40"/>
							</a>
						</div>

						<div class="col-11">
							<p class="mb-0">
								<a href="/<?= $u->getUsername(); ?>" class="clearUnderline ignoreParentClick">
									<span class="font-weight-bold"><?= $u->getDisplayName(); ?></span>
								</a>

								<span class="text-muted font-weight-normal">@<?= $u->getUsername(); ?></span>

								&bull;

								<?= Util::timeago($post->getTime()); ?>
							</p>

							<p class="mb-0 convertEmoji">
								<?= Util::convertPost($post->getText()); ?>
							</p>

							<?= Util::getPostActionButtons($post); ?>
						</div>
					</div>
				</div>
			</div>
						<?php

						} else if($post->getType() == "SHARE"){
							$sharedPost = $post->getPost();
							$sharedUser = $sharedPost->getUser();

							if(is_null($sharedPost) || is_null($sharedUser))
								continue;

							?>
			<div class="card feedEntry mb-2 statusTrigger" data-status-render="<?= $sharedPost->getId() ?>" data-entry-id="<?= $post->getId() ?>">
				<div class="card-body">
					<div class="small text-muted">
						<i class="fas fa-share-alt text-primary"></i> Shared by <a href="/<?= $u->getUsername(); ?>" class="clearUnderline ignoreParentClick"><?= $u->getDisplayName(); ?></a> &bull; <?= Util::timeago($post->getTime()); ?>
					</div>
					<div class="row">
						<div class="col-1">
							<a href="/<?= $sharedUser->getUsername(); ?>" class="clearUnderline ignoreParentClick">
								<img class="rounded mx-1 my-1" src="<?= $sharedUser->getAvatarURL(); ?>" width="40" height="40"/>
							</a>
						</div>

						<div class="col-11">
							<p class="mb-0">
								<a href="/<?= $sharedUser->getUsername(); ?>" class="clearUnderline ignoreParentClick">
									<span class="font-weight-bold"><?= $sharedUser->getDisplayName(); ?></span>
								</a>

								<span class="text-muted font-weight-normal">@<?= $sharedUser->getUsername(); ?></span>

								&bull;

								<?= Util::timeago($sharedPost->getTime()); ?>
							</p>

							<p class="mb-0 convertEmoji">
								<?= Util::convertPost($sharedPost->getText()); ?>
							</p>

							<?= Util::getPostActionButtons($sharedPost); ?>
						</div>
					</div>
				</div>
			</div>
							<?php
						}
					}

					echo '</div>';

					?>
			<div class="card homeFeedLoadMore px-3 py-3 text-center my-2 border-primary" style="cursor: pointer; background: #9FCCFC" onclick="loadOldHomeFeed();">
				Click to load more
			</div>
					<?php
				} else {
					echo Util::createAlert("emptyFeed","Your feed is empty! Follow somebody or post something to fill it!",ALERT_TYPE_INFO);
				}

			?>
		</div>

		<div class="col-lg-4">
			<div class="homeFeedSidebar">
				<form action="<?= $app->routeUrl("/search"); ?>" method="get">
					<div class="input-group input-group-sm">
						<input class="form-control" name="query" placeholder="Search <?= $app["config.site"]["name"] ?>" type="text"/>

						<div class="input-group-append">
							<button class="btn btn-primary px-3" type="submit"><i class="fas fa-search"></i></button>
						</div>
					</div>
				</form>

				<?php

					$openRequests = Util::getCurrentUser()->getOpenFollowRequests();
					if($openRequests > 0){
						?>
				<a href="/requests" class="btn btn-info btn-block mt-3 mb-2"><?= $openRequests ?> open follow request<?= $openRequests > 1 ? "s" : "" ?></a>
						<?php
					}


					$trendingUsers = [];
					$n = "trendingUsers";

					if(CacheHandler::existsInCache($n)){
						$trendingUsers = CacheHandler::getFromCache($n);
					} else {
						$stmt = $mysqli->prepare("SELECT COUNT(f.following) as `increase`,u.* FROM `users` AS u LEFT JOIN `follows` AS f ON f.`following` = u.`id` WHERE f.`time` > (NOW() - INTERVAL 24 HOUR) AND u.`privacy.level` = 'PUBLIC' GROUP BY u.`id` ORDER BY `increase` DESC LIMIT 5");
						if($stmt->execute()){
							$result = $stmt->get_result();
							
							if($result->num_rows){
								while($row = $result->fetch_assoc()){
									array_push($trendingUsers,[
										"increase" => $row["increase"],
										"user" => User::getUserByData($row["id"],$row["displayName"],$row["username"],$row["email"],$row["avatar"],$row["bio"],$row["token"],$row["privacy.level"],$row["featuredBox.title"],$row["featuredBox.content"],$row["lastGigadriveUpdate"],$row["gigadriveJoinDate"],$row["time"])
									]);
								}

								CacheHandler::setToCache($n,$trendingUsers,3*60);
							}
						}
						$stmt->close();
					}

					$newUsers = [];
					$n = "newUsers";

					if(CacheHandler::existsInCache($n)){
						$newUsers = CacheHandler::getFromCache($n);
					} else {
						$stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `privacy.level` = 'PUBLIC' ORDER BY `time` DESC LIMIT 5");
						if($stmt->execute()){
							$result = $stmt->get_result();
							
							if($result->num_rows){
								while($row = $result->fetch_assoc()){
									array_push($newUsers,User::getUserByData($row["id"],$row["displayName"],$row["username"],$row["email"],$row["avatar"],$row["bio"],$row["token"],$row["privacy.level"],$row["featuredBox.title"],$row["featuredBox.content"],$row["lastGigadriveUpdate"],$row["gigadriveJoinDate"],$row["time"]));
								}

								CacheHandler::setToCache($n,$newUsers,2*60);
							}
						}
						$stmt->close();
					}

					if(count($trendingUsers) > 0){
						?>
				<div class="card my-3">
					<div class="card-header">
						<ul class="nav nav-pills nav-fill" id="users-tablist" role="tablist">
							<li class="nav-item">
								<a class="nav-link active" id="trending-tab" data-toggle="pill" href="#trendingUsers" role="tab" aria-controls="trendingUsers" aria-selected="true">
									Trending users
								</a>
							</li>

							<li class="nav-item">
								<a class="nav-link" id="new-tab" data-toggle="pill" href="#newUsers" role="tab" aria-controls="newUsers" aria-selected="false">
									New users
								</a>
							</li>
						</ul>
					</div>

					<div class="tab-content" id="users-tablist-content">
						<div class="tab-pane fade show active" id="trendingUsers" role="tabpanel" aria-labelledby="trending-tab">
							<?php

								foreach($trendingUsers as $trendingUser){
									$increase = $trendingUser["increase"];
									$u = $trendingUser["user"];

									?>
								<div class="px-2 py-1 my-1" style="height: 70px">
									<a href="/<?= $u->getUsername(); ?>" class="clearUnderline float-left">
										<img src="<?= $u->getAvatarURL() ?>" width="64" height="64" class="rounded"/>
									</a>

									<div class="ml-2 float-left">
										<a href="/<?= $u->getUsername(); ?>" class="clearUnderline">
											<div class="font-weight-bold float-left small mt-1" style="max-width: 100px; overflow: hidden !important; text-overflow: ellipsis !important; white-space: nowrap !important; word-wrap: normal !important;"><?= $u->getDisplayName() ?></div>
											<div class="text-muted small float-right mt-1 ml-1" style="max-width: 80px; overflow: hidden !important; text-overflow: ellipsis !important; white-space: nowrap !important; word-wrap: normal !important;">@<?= $u->getUsername(); ?></div><br/>
										</a>

										<?= Util::followButton($u->getId(),true,["mt-0","btn-sm","ignoreParentClick"]) ?>
									</div>
								</div>
									<?php
								}

							?>
						</div>

						<div class="tab-pane fade" id="newUsers" role="tabpanel" aria-labelledby="new-tab">
							<?php

								foreach($newUsers as $u){
										?>
								<div class="px-2 py-1 my-1" style="height: 70px">
									<a href="/<?= $u->getUsername(); ?>" class="clearUnderline float-left">
										<img src="<?= $u->getAvatarURL() ?>" width="64" height="64" class="rounded"/>
									</a>

									<div class="ml-2 float-left">
										<a href="/<?= $u->getUsername(); ?>" class="clearUnderline">
											<div class="font-weight-bold float-left small mt-1" style="max-width: 100px; overflow: hidden !important; text-overflow: ellipsis !important; white-space: nowrap !important; word-wrap: normal !important;"><?= $u->getDisplayName() ?></div>
											<div class="text-muted small float-right mt-1 ml-1" style="max-width: 80px; overflow: hidden !important; text-overflow: ellipsis !important; white-space: nowrap !important; word-wrap: normal !important;">@<?= $u->getUsername(); ?></div><br/>
										</a>

										<?= Util::followButton($u->getId(),true,["mt-0","btn-sm","ignoreParentClick"]) ?>
									</div>
								</div>
										<?php
								}

							?>
						</div>
					</div>
				</div>
					<?php
				}

				echo Util::renderAd(Util::AD_TYPE_BLOCK,true,["mb-1"]);

			?>
			</div>
		</div>
	</div>
</div>
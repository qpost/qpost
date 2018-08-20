<div class="legacyCardBody">
	<div class="row">
		<div class="col-lg-8">
			<?= Util::createAlert("feedInfo","The home feed shows the posts and activities of you and the people you follow on the network. Follow people you are interested in to see what they're doing!",ALERT_TYPE_INFO,true,true); ?>
			<?php

				echo Util::renderCreatePostForm(["mb-2"]);

				echo Util::renderAd(Util::AD_TYPE_LEADERBOARD,true,["my-1"]);

				$user = Util::getCurrentUser();
				$mysqli = Database::Instance()->get();
				
				$a = $user->getFollowingAsArray();
				array_push($a,$user->getId());

				$i = $mysqli->real_escape_string(implode(",",$a));

				$results = [];

				$stmt = $mysqli->prepare("SELECT f.`id` AS `postID`,f.`text` AS `postText`,f.`time` AS `postTime`,f.`sessionId`,f.`post` AS `sharedPost`,f.`type` AS `postType`,f.`count.replies`,f.`count.shares`,f.`count.favorites`,f.`attachments`,u.* FROM `feed` AS f INNER JOIN `users` AS u ON f.`user` = u.`id` WHERE f.`post` IS NULL AND (f.`type` = 'POST' OR f.`type` = 'SHARE') AND f.`user` IN ($i) AND u.`privacy.level` != 'CLOSED' ORDER BY f.`time` DESC LIMIT 60");
				//$stmt->bind_param("s",$i);
				if($stmt->execute()){
					$result = $stmt->get_result();

					if($result->num_rows){
						while($row = $result->fetch_assoc()){
							array_push($results,[
								"post" => FeedEntry::getEntryFromData($row["postID"],$row["id"],$row["postText"],null,$row["sharedPost"],$row["sessionId"],$row["postType"],$row["count.replies"],$row["count.shares"],$row["count.favorites"],$row["attachments"],$row["postTime"]),
								"user" => User::getUserByData($row["id"],$row["gigadriveId"],$row["displayName"],$row["username"],$row["password"],$row["email"],$row["avatar"],$row["bio"],$row["token"],$row["birthday"],$row["privacy.level"],$row["featuredBox.title"],$row["featuredBox.content"],$row["lastGigadriveUpdate"],$row["gigadriveJoinDate"],$row["time"],$row["emailActivated"],$row["emailActivationToken"],$row["lastUsernameChange"])
							]);
						}
					}
				}
				$stmt->close();

				if(count($results) > 0){
					echo '<ul class="list-group feedContainer">';

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

						echo $post->toListHTML();
					}

					echo '</ul>';

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
			<div class="homeFeedSidebar d-none d-md-block sticky-top" style="top: 70px">
				<div class="homeFeedProfileBox card mb-3">
					<div class="px-2 py-2">
						<div class="d-block" style="height: 50px">
							<a href="/<?= Util::getCurrentUser()->getUsername() ?>" class="clearUnderline float-left">
								<img src="<?= Util::getCurrentUser()->getAvatarURL() ?>" class="rounded" width="48" height="48"/>
							</a>

							<div class="ml-2 float-left mt-1">
								<a href="/<?= Util::getCurrentUser()->getUsername() ?>" class="clearUnderline float-left">
									<div class="font-weight-bold" style="max-width: 168px; overflow: hidden !important; text-overflow: ellipsis !important; white-space: nowrap !important; word-wrap: normal !important;">
										<?= Util::getCurrentUser()->getDisplayName() ?>
									</div>

									<div class="text-muted small" style="max-width: 168px; overflow: hidden !important; text-overflow: ellipsis !important; white-space: nowrap !important; word-wrap: normal !important; margin-top: -7px;">
										@<?= Util::getCurrentUser()->getUsername() ?>
									</div>
								</a>
							</div>

							<?= Util::followButton(Util::getCurrentUser(),true,["float-right","mt-2","btn-sm"],false) ?>
						</div>

						<div class="row mt-2 text-center pr-3">
							<div class="col">
								<a href="/<?= Util::getCurrentUser()->getUsername() ?>" class="clearUnderline">
									<div class="font-weight-bold">
										<?= Util::getCurrentUser()->getPosts() ?>
									</div>
									<div class="text-uppercase text-muted small">Posts</div>
								</a>
							</div>

							<div class="col">
								<a href="/<?= Util::getCurrentUser()->getUsername() ?>/following" class="clearUnderline">
									<div class="font-weight-bold">
										<?= Util::getCurrentUser()->getFollowing() ?>
									</div>
									<div class="text-uppercase text-muted small">Following</div>
								</a>
							</div>

							<div class="col">
								<a href="/<?= Util::getCurrentUser()->getUsername() ?>/followers" class="clearUnderline">
									<div class="font-weight-bold">
										<?= Util::getCurrentUser()->getFollowers() ?>
									</div>
									<div class="text-uppercase text-muted small">Followers</div>
								</a>
							</div>
						</div>
					</div>
				</div>

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

					$limit = 5;

					if(CacheHandler::existsInCache($n)){
						$trendingUsers = CacheHandler::getFromCache($n);
					} else {
						$stmt = $mysqli->prepare("SELECT COUNT(f.following) as `increase`,u.* FROM `users` AS u LEFT JOIN `follows` AS f ON f.`following` = u.`id` WHERE f.`time` > (NOW() - INTERVAL 24 HOUR) AND u.`privacy.level` = 'PUBLIC' AND `emailActivated` = 1 GROUP BY u.`id` ORDER BY `increase` DESC LIMIT " . $limit);
						if($stmt->execute()){
							$result = $stmt->get_result();
							
							if($result->num_rows){
								while($row = $result->fetch_assoc()){
									array_push($trendingUsers,[
										"increase" => $row["increase"],
										"user" => User::getUserByData($row["id"],$row["gigadriveId"],$row["displayName"],$row["username"],$row["password"],$row["email"],$row["avatar"],$row["bio"],$row["token"],$row["birthday"],$row["privacy.level"],$row["featuredBox.title"],$row["featuredBox.content"],$row["lastGigadriveUpdate"],$row["gigadriveJoinDate"],$row["time"],$row["emailActivated"],$row["emailActivationToken"],$row["lastUsernameChange"])
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
						$stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `privacy.level` = 'PUBLIC' AND `emailActivated` = 1 ORDER BY `time` DESC LIMIT " . $limit);
						if($stmt->execute()){
							$result = $stmt->get_result();
							
							if($result->num_rows){
								while($row = $result->fetch_assoc()){
									array_push($newUsers,User::getUserByData($row["id"],$row["gigadriveId"],$row["displayName"],$row["username"],$row["password"],$row["email"],$row["avatar"],$row["bio"],$row["token"],$row["birthday"],$row["privacy.level"],$row["featuredBox.title"],$row["featuredBox.content"],$row["lastGigadriveUpdate"],$row["gigadriveJoinDate"],$row["time"],$row["emailActivated"],$row["emailActivationToken"],$row["lastUsernameChange"]));
								}

								CacheHandler::setToCache($n,$newUsers,2*60);
							}
						}
						$stmt->close();
					}

					$currentUser = Util::getCurrentUser()->getId();

					// query is a combination of https://stackoverflow.com/a/12915720 and https://stackoverflow.com/a/24165699
					$suggestedUsers = [];
					$stmt = $mysqli->prepare("SELECT COUNT(*)       AS mutuals, u.* FROM users      AS me INNER JOIN follows    AS my_friends ON my_friends.follower = me.id INNER JOIN follows    AS their_friends ON their_friends.follower = my_friends.following INNER JOIN  users 	   AS u ON u.id = their_friends.following WHERE u.emailActivated = 1 AND me.id = ? AND their_friends.following != ? AND NOT EXISTS (SELECT 1 FROM follows fu3 WHERE fu3.follower = ? AND fu3.following = their_friends.following) GROUP BY me.id, their_friends.following LIMIT " . $limit);
					$stmt->bind_param("iii",$currentUser,$currentUser,$currentUser);
					if($stmt->execute()){
						$result = $stmt->get_result();

						if($result->num_rows){
							while($row = $result->fetch_assoc()){
								$u = User::getUserByData($row["id"],$row["gigadriveId"],$row["displayName"],$row["username"],$row["password"],$row["email"],$row["avatar"],$row["bio"],$row["token"],$row["birthday"],$row["privacy.level"],$row["featuredBox.title"],$row["featuredBox.content"],$row["lastGigadriveUpdate"],$row["gigadriveJoinDate"],$row["time"],$row["emailActivated"],$row["emailActivationToken"],$row["lastUsernameChange"]);
								$mutuals = $row["mutuals"];

								array_push($suggestedUsers,[
									"user" => $u,
									"mutuals" => $mutuals
								]);
							}
						}
					}
					$stmt->close();

					?>
				<div class="card my-3">
					<div class="card-header">
						<ul class="nav nav-pills nav-fill" id="users-tablist" role="tablist">
							<?php if(count($suggestedUsers) > 0){ ?>
							<li class="nav-item">
								<a class="nav-link active small" id="suggested-tab" data-toggle="pill" href="#suggestedUsers" role="tab" aria-controls="suggestedUsers" aria-selected="<?= count($suggestedUsers) > 0 ? "true" : "false" ?>">
									Suggested
								</a>
							</li>
							<?php } ?>

							<li class="nav-item">
								<a class="nav-link<?= count($suggestedUsers) == 0 ? " active" : "" ?> small" id="trending-tab" data-toggle="pill" href="#trendingUsers" role="tab" aria-controls="trendingUsers" aria-selected="<?= count($suggestedUsers) > 0 ? "false" : "true" ?>">
									Trending
								</a>
							</li>

							<li class="nav-item">
								<a class="nav-link small" id="new-tab" data-toggle="pill" href="#newUsers" role="tab" aria-controls="newUsers" aria-selected="false">
									New
								</a>
							</li>
						</ul>
					</div>

					<div class="tab-content" id="users-tablist-content">
						<?php if(count($suggestedUsers) > 0){ ?>
						<div class="tab-pane fade show active" id="suggestedUsers" role="tabpanel" aria-labelledby="suggested-tab">
							<?php

								foreach($suggestedUsers as $suggestedUser){
									$mutuals = $suggestedUser["mutuals"];
									$u = $suggestedUser["user"];

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
						<?php } ?>

						<div class="tab-pane fade<?= count($suggestedUsers) == 0 ? " show active" : "" ?>" id="trendingUsers" role="tabpanel" aria-labelledby="trending-tab">
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

				<div class="card">
					<div class="px-2 py-2 small">
						&copy; <?= date("Y") ?> Gigadrive &bull;

						<a href="https://gigadrivegroup.com/legal/contact" target="_blank" class="mx-1">
							Contact Info
						</a>

						<a href="https://gigadrivegroup.com/legal/tos" target="_blank" class="mx-1">
							Terms of Service
						</a>

						<a href="https://gigadrivegroup.com/legal/privacy" target="_blank" class="mx-1">
							Privacy Policy
						</a>

						<a href="https://gigadrivegroup.com/legal/disclaimer" target="_blank" class="mx-1">
							Disclaimer
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
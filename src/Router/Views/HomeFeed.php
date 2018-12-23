<?php

$user = Util::getCurrentUser();

?><div class="legacyCardBody">
	<div class="row">
		<div class="col-lg-4 col-xl-3 d-none d-lg-block">
			<div class="homeFeedSidebar sticky-top" style="top: 70px">
				<div class="homeFeedProfileBox card mb-3">
					<div class="px-2 py-2">
						<div class="d-block" style="height: 50px">
							<a href="/<?= $user->getUsername() ?>" class="clearUnderline float-left">
								<img src="<?= $user->getAvatarURL() ?>" class="rounded" width="48" height="48"/>
							</a>

							<div class="ml-2 float-left mt-1">
								<a href="/<?= $user->getUsername() ?>" class="clearUnderline float-left">
									<div class="font-weight-bold" style="max-width: 70px; overflow: hidden !important; text-overflow: ellipsis !important; white-space: nowrap !important; word-wrap: normal !important;">
										<?= $user->getDisplayName() . $user->renderCheckMark() ?>
									</div>

									<div class="text-muted small" style="max-width: 70px; overflow: hidden !important; text-overflow: ellipsis !important; white-space: nowrap !important; word-wrap: normal !important; margin-top: -5px;">
										@<?= $user->getUsername() ?>
									</div>
								</a>
							</div>

							<?= Util::followButton($user,true,["float-right","mt-2","btn-sm"],false) ?>
						</div>

						<div class="row mt-2 text-center pr-3">
							<div class="col-6">
								<a href="/<?= $user->getUsername() ?>" class="clearUnderline">
									<div class="font-weight-bold">
										<?= Util::formatNumberShort($user->getPosts()) ?>
									</div>
									<div class="text-uppercase text-muted small">Posts</div>
								</a>
							</div>

							<div class="col-6">
								<a href="/<?= $user->getUsername() ?>/followers" class="clearUnderline">
									<div class="font-weight-bold">
										<?= Util::formatNumberShort($user->getFollowers()) ?>
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

				<?= Util::renderAd(Util::AD_TYPE_BLOCK,true,["mt-3"]) ?>
			</div>
		</div>

		<div class="col-lg-8 col-xl-6">
			<?= Util::createAlert("feedInfo","The home feed shows the posts and activities of you and the people you follow on the network. Follow people you are interested in to see what they're doing!",ALERT_TYPE_INFO,true,true); ?>
			<?php

				echo Util::renderCreatePostForm(["mb-2","homePostField"]);

				echo Util::renderAd(Util::AD_TYPE_LEADERBOARD,true,["my-1"]);

				$mysqli = Database::Instance()->get();
				
			?>
			<div class="text-center homeFeedLoadSpinner">
				<i class="fas fa-spinner fa-pulse mt-3" style="font-size: 36px"></i>
			</div>

			<script type="text/javascript">
				var HOME_FEED_FIRST_POST;
				var HOME_FEED_LAST_POST;

				$.ajax({
					url: "/scripts/extendHomeFeed",
					data: {
						csrf_token: CSRF_TOKEN,
						mode: "loadFirst"
					},
					method: "POST",
					
					success: function(result){
						let json = result;
						
						if(json.hasOwnProperty("result")){
							let newHtml = "";
							
							let a = true;
							
							json.result.forEach(post => {
								let postId = post.id;
								
								if(a == true){
									HOME_FEED_LAST_POST = postId;
									a = false;
								}
								
								newHtml = newHtml.concat(post.listHtml);
							});

							HOME_FEED_FIRST_POST = json.result[json.result.length-1].id;

							$(".feedContainer").html(newHtml);
							$(".homeFeedLoadSpinner").remove();
							$(".homeFeedLoadMore").removeClass("d-none");

							loadBasic();
						} else {
							console.log(result);
						}
					},
					
					error: function(xhr,status,error){
						console.log(xhr);
						console.log(status);
						console.log(error);

						$(".feedContainer").html('<div class="alert alert-danger" role="alert">Failed to load home feed.</div>');
					}
				});
			</script>

			<ul class="list-group feedContainer mt-2"></ul>

			<div class="d-none card homeFeedLoadMore px-3 py-3 text-center my-2" style="cursor: pointer; background: #9FCCFC" onclick="loadOldHomeFeed();">
				Click to load more
			</div>
		</div>

		<div class="col-xl-3 d-none d-xl-block">
			<div class="homeFeedSidebar sticky-top" style="top: 70px">
				<?php

					$openRequests = $user->getOpenFollowRequests();
					if($openRequests > 0){
						?>
				<a href="/requests" class="btn btn-info btn-block mb-3"><?= $openRequests ?> open follow request<?= $openRequests > 1 ? "s" : "" ?></a>
						<?php
					}

					$i = 0;
					$currentUser = $user->getId();

					// query is a combination of https://stackoverflow.com/a/12915720 and https://stackoverflow.com/a/24165699
					$suggestedUsers = [];
					$stmt = $mysqli->prepare("SELECT COUNT(*)       AS mutuals, u.`id` FROM users      AS me INNER JOIN follows    AS my_friends ON my_friends.follower = me.id INNER JOIN follows    AS their_friends ON their_friends.follower = my_friends.following INNER JOIN  users 	   AS u ON u.id = their_friends.following WHERE u.emailActivated = 1 AND me.id = ? AND their_friends.following != ? AND NOT EXISTS (SELECT 1 FROM follows fu3 WHERE fu3.follower = ? AND fu3.following = their_friends.following) GROUP BY me.id, their_friends.following LIMIT 10");
					$stmt->bind_param("iii",$currentUser,$currentUser,$currentUser);
					if($stmt->execute()){
						$result = $stmt->get_result();

						if($result->num_rows){
							while($row = $result->fetch_assoc()){
								if($i == 5) break;

								$u = User::getUserById($row["id"]);

								if(!$u->mayView()) continue;

								$mutuals = $row["mutuals"];

								array_push($suggestedUsers,[
									"user" => $u,
									"mutuals" => $mutuals
								]);

								$i++;
							}
						}
					}
					$stmt->close();

					?>
				<?php if(count($suggestedUsers) > 0){ ?>
				<div class="card">
					<div class="card-header">
						Suggested
					</div>

					<div class="tab-content" id="users-tablist-content">
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
										<a href="/<?= $u->getUsername(); ?>" class="clearUnderline" data-user-id="<?= $u->getId() ?>">
											<div class="text-muted small float-left mt-1" style="max-width: 160px; overflow: hidden !important; text-overflow: ellipsis !important; white-space: nowrap !important; word-wrap: normal !important;">@<?= $u->getUsername() . $u->renderCheckMark(); ?></div><br/>
										</a>

										<?= Util::followButton($u->getId(),true,["mt-0","btn-sm","ignoreParentClick"]) ?>
									</div>
								</div>
									<?php
								}

						?>
					</div>
				</div>
				<?php } ?>

				<div class="birthdayContainer"></div>

				<div class="card mt-3">
					<div class="px-2 py-2 small">
						&copy; <?= date("Y") ?> Gigadrive &bull;

						<a href="https://gigadrivegroup.com/legal/contact" target="_blank" class="mx-1">
							Contact Info
						</a>

						<a href="https://gigadrivegroup.com/legal/terms-of-service" target="_blank" class="mx-1">
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
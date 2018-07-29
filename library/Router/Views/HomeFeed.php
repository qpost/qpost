<div class="card-body">
	<div class="row">
		<div class="col-lg-8">
			<?= Util::createAlert("feedInfo","The home feed shows the posts and activities of you and the people you follow on the network. Follow people you are interested in to see what they're doing!",ALERT_TYPE_INFO,true,true); ?>
			<h4>Feed</h4>

			<div class="card my-2 border-primary" style="background: #9FCCFC">
				<div class="card-body">
					<textarea class="form-control" id="homePostField" placeholder="Post something for your followers!"></textarea>

					<p class="mb-0 mt-2 float-left small">
						<?= POST_CHARACTER_LIMIT ?> characters left
					</p>

					<button type="button" class="btn btn-primary btn-sm float-right mb-0 mt-2">Post</button>
				</div>
			</div>

			<?php

				$user = Util::getCurrentUser();
				$mysqli = Database::Instance()->get();
				
				$a = $user->getFollowingAsArray();
				array_push($a,$user->getId());

				$i = $mysqli->real_escape_string(implode(",",$a));

				$results = [];

				$stmt = $mysqli->prepare("SELECT f.`id` AS `postID`,f.`text` AS `postText`,f.`time` AS `postTime`,u.* FROM `feed` AS f INNER JOIN `users` AS u ON f.`user` = u.`id` WHERE f.`type` = 'POST' AND f.`user` IN ($i) ORDER BY `time` DESC LIMIT 60");
				//$stmt->bind_param("s",$i);
				if($stmt->execute()){
					$result = $stmt->get_result();

					if($result->num_rows){
						while($row = $result->fetch_assoc()){
							array_push($results,[
								"post" => [
									"id" => $row["postID"],
									"text" => $row["postText"],
									"time" => $row["postTime"]
								],
								"user" => User::getUserByData($row["id"],$row["displayName"],$row["username"],$row["email"],$row["avatar"],$row["bio"],$row["token"],$row["privacy.level"],$row["time"])
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

						if($first)
							echo '<script>var HOME_FEED_FIRST_POST = ' . $post["id"] . ';</script>';

						if($last)
							echo '<script>var HOME_FEED_LAST_POST = ' . $post["id"] . ';</script>';

						?>
			<div class="card feedEntry<?= !$last ? " mb-2" : "" ?>" data-entry-id="<?= $post["id"]; ?>">
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

								<?= Util::timeago($post["time"]); ?>
							</p>

							<p class="mb-0">
								<?= Util::convertLineBreaksToHTML($post["text"]); ?>
							</p>
						</div>
					</div>
				</div>
			</div>
						<?php
					}

					echo '</div>';

					?>
			<div class="card homeFeedLoadMore px-3 py-3 text-center my-2" style="cursor: pointer; background: #9FCCFC">
				Click to load more
			</div>
					<?php
				} else {
					echo Util::createAlert("emptyFeed","Your feed is empty! Follow somebody or post something to fill it!",ALERT_TYPE_INFO);
				}

			?>
		</div>

		<div class="col-lg-4">
			<form action="<?= $app->routeUrl("/search"); ?>" method="get">
				<div class="input-group input-group-sm">
					<input class="form-control" name="query" placeholder="Search <?= $app["config.site"]["name"] ?>" type="text"/>

					<div class="input-group-append">
						<button class="btn btn-primary px-3" type="submit"><i class="fas fa-search"></i></button>
					</div>
				</div>
			</form>

			<?php

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
									"user" => User::getUserByData($row["id"],$row["displayName"],$row["username"],$row["email"],$row["avatar"],$row["bio"],$row["token"],$row["privacy.level"],$row["time"])
								]);
							}

							CacheHandler::setToCache($n,$trendingUsers,3*60);
						}
					}
					$stmt->close();
				}

				if(count($trendingUsers) > 0){
					?>
			<h5 class="mb-0 mt-2">Trending Users</h5>
					<?php

					foreach($trendingUsers as $trendingUser){
						$increase = $trendingUser["increase"];
						$u = $trendingUser["user"];

						?>
			<div class="card userCard my-3" data-user-id="<?= $u->getId(); ?>">
				<div class="card-body">
					<center>
						<a href="<?= $app->routeUrl("/" . $u->getUsername()); ?>" class="clearUnderline"><img src="<?= $u->getAvatarURL(); ?>" width="60" height="60"/>

						<h6 class="mb-0"><?= $u->getDisplayName(); ?></h6></a>
						<p class="text-muted my-0" style="font-size: 12px">@<?= $u->getUsername(); ?></p>

						<?= Util::followButton($u->getId(),true,["btn-block","mt-2","btn-sm"]) ?>
					</center>
				</div>
			</div>
						<?php
					}
				}

			?>
		</div>
	</div>
</div>
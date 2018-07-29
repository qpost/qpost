<div class="card-body">
	<div class="row">
		<div class="col-lg-8">
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
								"user" => User::getUserByData($row["id"],$row["displayName"],$row["username"],$row["email"],$row["avatar"],$row["bio"],$row["token"],$row["time"])
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
		</div>
	</div>
</div>
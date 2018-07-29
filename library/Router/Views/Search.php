<div class="card-body">
	<div class="row">
		<div class="col-lg-8 offset-lg-2">
			<form action="<?= $app->routeUrl("/search"); ?>" method="get">
				<div class="input-group input-group-lg">
					<input autofocus class="form-control" name="query" value="<?= !is_null($query) ? $query : ""; ?>" placeholder="Search <?= $app["config.site"]["name"] ?>" type="text"/>

					<div class="input-group-append">
						<button class="btn btn-primary px-4" type="submit"><i class="fas fa-search"></i></button>
					</div>
				</div>
			</form>
		</div>
	</div>

<?php if(!empty(trim($query))){ ?>

	<nav class="nav nav-pills nav-justified my-3">
		<a class="nav-item nav-link<?php if(isset($type) && $type == "posts") echo " active"; ?>" href="<?= $app->routeUrl("/search?query=" . urlencode($query) . "&type=posts"); ?>">Posts</a>
		<a class="nav-item nav-link<?php if(isset($type) && $type == "users") echo " active"; ?>" href="<?= $app->routeUrl("/search?query=" . urlencode($query) . "&type=users"); ?>">Users</a>
	</nav>

<?php

	$num = 0;
	$mysqli = Database::Instance()->get();

	$q = "%" . $query . "%";

	if($type == "posts"){
		$n = "searchnum_posts_" . $query;

		if(CacheHandler::existsInCache($n)){
			$num = CacheHandler::getFromCache($n);
		} else {
			$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `feed` AS p INNER JOIN `users` AS u ON p.user = u.id WHERE (p.`text` LIKE ? OR u.`displayName` LIKE ? OR u.`username` LIKE ?) AND p.`type` = 'POST'");
			$stmt->bind_param("sss",$q,$q,$q);
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
	} else if($type == "users"){
		$n = "searchnum_users_" . $query;

		if(CacheHandler::existsInCache($n)){
			$num = CacheHandler::getFromCache($n);
		} else {
			$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `users` AS u WHERE u.`displayName` LIKE ? OR u.`username` LIKE ? OR u.`bio` LIKE ?");
			$stmt->bind_param("sss",$q,$q,$q);
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
	}

	if($num > 0){
		$itemsPerPage = 10;

		$results = [];

		$mysqli = Database::Instance()->get();

		if($type == "posts"){
			$stmt = $mysqli->prepare("SELECT p.`id` AS `postID`,p.`text` AS `postText`,p.`time` AS `postTime`,u.* FROM `feed` AS p INNER JOIN `users` AS u ON p.user = u.id WHERE (p.`text` LIKE ? OR u.`displayName` LIKE ? OR u.`username` LIKE ?) AND p.`type` = 'POST' ORDER BY p.`time` DESC LIMIT " . (($page-1)*$itemsPerPage) . " , " . $itemsPerPage);
			$stmt->bind_param("sss",$q,$q,$q);
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

					CacheHandler::setToCache($n,$num,2*60);
				}
			}
			$stmt->close();
		} else if($type == "users"){
			$stmt = $mysqli->prepare("SELECT u.* FROM `users` AS u WHERE u.`displayName` LIKE ? OR u.`username` LIKE ? OR u.`bio` LIKE ? LIMIT " . (($page-1)*$itemsPerPage) . " , " . $itemsPerPage);
			$stmt->bind_param("sss",$q,$q,$q);
			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					while($row = $result->fetch_assoc()){
						array_push($results,User::getUserByData($row["id"],$row["displayName"],$row["username"],$row["email"],$row["avatar"],$row["bio"],$row["token"],$row["privacy.level"],$row["time"]));
					}
				}
			}
			$stmt->close();
		}
		
		if(count($results) > 0){
			echo '<p class="mb-0 small text-muted">' . $num . ' result' . ($num == 1 ? "" : "s") . '</p>';

			echo Util::paginate($page,$itemsPerPage,$num,"/search?query=" . urlencode($query) . "&type=" . $type . "&page=(:num)");

			if($type == "posts"){
				echo '<div class="card postContainer mt-2"><div class="card-body">';

				foreach($results as $result){
					$post = $result["post"];
					$u = $result["user"];

					?>
				<div class="card post<?= !$last ? " mb-2" : "" ?>" data-post-id="<?= $post["id"]; ?>">
					<div class="card-body">
						<div class="row">
							<div class="col-1">
								<a href="/<?= $u->getUsername(); ?>" class="clearUnderline">
									<img class="rounded mx-1 my-1" src="<?= $u->getAvatarURL(); ?>" width="40" height="40"/>
								</a>
							</div>

							<div class="col-11">
								<p class="mb-0">
									<a href="/<?= $u->getUsername(); ?>" class="clearUnderline"><span class="font-weight-bold"><?= $u->getDisplayName(); ?></span></a>
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

				echo '</div></div>';
			} else if($type == "users"){
				echo '<div class="row mt-2">';

				foreach($results as $u){
					?>
				<div class="card userCard col-md-4" data-user-id="<?= $u->getId(); ?>">
					<div class="card-body">
						<center>
							<a href="<?= $app->routeUrl("/" . $u->getUsername()); ?>" class="clearUnderline"><img src="<?= $u->getAvatarURL(); ?>" width="60" height="60"/>

							<h5 class="mb-0"><?= $u->getDisplayName(); ?></a></h5>
							<p class="text-muted my-0" style="font-size: 16px">@<?= $u->getUsername(); ?></p>

							<?= !is_null($u->getBio()) ? '<p class="mb-0 mt-2">' . Util::convertLineBreaksToHTML($u->getBio()) . '</p>' : ""; ?>

							<?= Util::followButton($u->getId(),true,["btn-block","mt-2"]) ?>
						</center>
					</div>
				</div>
					<?php
				}

				echo '</div>';
			}

			echo Util::paginate($page,$itemsPerPage,$num,"/search?query=" . urlencode($query) . "&type=" . $type . "&page=(:num)");
		} else {
			echo Util::createAlert("noResults","No results could be found for that search.",ALERT_TYPE_INFO);
		}
	} else {
		echo Util::createAlert("noResults","No results could be found for that search.",ALERT_TYPE_INFO);
	}

}

?>
</div>
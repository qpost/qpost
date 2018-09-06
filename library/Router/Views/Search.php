<div class="legacyCardBody">
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

<?php if(!Util::isEmpty(trim($query))){ ?>

	<nav class="nav nav-pills nav-justified my-3">
		<a class="nav-item nav-link<?php if(isset($type) && $type == "posts") echo " active"; ?>" href="<?= $app->routeUrl("/search?query=" . urlencode($query) . "&type=posts"); ?>">Posts</a>
		<a class="nav-item nav-link<?php if(isset($type) && $type == "users") echo " active"; ?>" href="<?= $app->routeUrl("/search?query=" . urlencode($query) . "&type=users"); ?>">Users</a>
	</nav>

	<div class="row">
		<div class="col-lg-10 offset-lg-1">
<?php

	$num = 0;
	$mysqli = Database::Instance()->get();

	$q = "%" . $query . "%";

	if($type == "posts"){
		$n = "searchnum_posts_" . $query;

		if(CacheHandler::existsInCache($n)){
			$num = CacheHandler::getFromCache($n);
		} else {
			$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `feed` AS p INNER JOIN `users` AS u ON p.user = u.id WHERE p.`post` IS NULL AND (p.`text` LIKE ? OR u.`displayName` LIKE ? OR u.`username` LIKE ?) AND p.`type` = 'POST' AND u.`privacy.level` = 'PUBLIC'");
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
			$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `users` AS u WHERE (u.`displayName` LIKE ? OR u.`username` LIKE ? OR u.`bio` LIKE ?) AND u.`privacy.level` != 'CLOSED'");
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
			$stmt = $mysqli->prepare("SELECT p.`id` AS `postID`,p.`text` AS `postText`,p.`time` AS `postTime`,p.`sessionId`,p.`count.replies`,p.`count.shares`,p.`count.favorites`,p.`attachments`,u.* FROM `feed` AS p INNER JOIN `users` AS u ON p.user = u.id WHERE p.`post` IS NULL AND (p.`text` LIKE ? OR u.`displayName` LIKE ? OR u.`username` LIKE ?) AND p.`type` = 'POST' AND u.`privacy.level` = 'PUBLIC' ORDER BY p.`time` DESC LIMIT " . (($page-1)*$itemsPerPage) . " , " . $itemsPerPage);
			$stmt->bind_param("sss",$q,$q,$q);
			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					while($row = $result->fetch_assoc()){
						$f = FeedEntry::getEntryFromData($row["postID"],$row["id"],$row["postText"],null,null,$row["sessionId"],"POST",$row["count.replies"],$row["count.shares"],$row["count.favorites"],$row["attachments"],$row["postTime"]);
						$u = User::getUserByData($row["id"],$row["gigadriveId"],$row["displayName"],$row["username"],$row["password"],$row["email"],$row["avatar"],$row["bio"],$row["token"],$row["birthday"],$row["privacy.level"],$row["featuredBox.title"],$row["featuredBox.content"],$row["lastGigadriveUpdate"],$row["gigadriveJoinDate"],$row["time"],$row["emailActivated"],$row["emailActivationToken"],$row["lastUsernameChange"],$row["verified"]);

						if(!$f->mayView() || !$u->mayView()) continue;

						array_push($results,[
							"post" => $f,
							"user" => $u
						]);
					}

					CacheHandler::setToCache($n,$num,2*60);
				}
			}
			$stmt->close();
		} else if($type == "users"){
			$stmt = $mysqli->prepare("SELECT u.* FROM `users` AS u WHERE (u.`displayName` LIKE ? OR u.`username` LIKE ? OR u.`bio` LIKE ?) AND u.`privacy.level` != 'CLOSED' LIMIT " . (($page-1)*$itemsPerPage) . " , " . $itemsPerPage);
			$stmt->bind_param("sss",$q,$q,$q);
			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					while($row = $result->fetch_assoc()){
						$u = User::getUserByData($row["id"],$row["gigadriveId"],$row["displayName"],$row["username"],$row["password"],$row["email"],$row["avatar"],$row["bio"],$row["token"],$row["birthday"],$row["privacy.level"],$row["featuredBox.title"],$row["featuredBox.content"],$row["lastGigadriveUpdate"],$row["gigadriveJoinDate"],$row["time"],$row["emailActivated"],$row["emailActivationToken"],$row["lastUsernameChange"],$row["verified"]);

						if(!$u->mayView()) continue;

						array_push($results,$u);
					}
				}
			}
			$stmt->close();
		}
		
		if(count($results) > 0){
			echo '<p class="mb-0 small text-muted">' . $num . ' result' . ($num == 1 ? "" : "s") . '</p>';

			echo Util::paginate($page,$itemsPerPage,$num,"/search?query=" . urlencode($query) . "&type=" . $type . "&page=(:num)");

			if($type == "posts"){
				echo '<ul class="list-group feedContainer mt-2">';

				foreach($results as $result){
					$post = $result["post"];
					$u = $result["user"];

					if(!$post->mayView()) continue;

					echo $post->toListHTML();
				}

				echo '</ul>';
			} else if($type == "users"){
				?><div class="row my-2"><?php
				foreach($results as $u){
					echo $u->renderForUserList();
				}
				?></div><?php
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
	</div>
</div>
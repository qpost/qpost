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
<div class="legacyCardBody">
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
				?>
			<div class="card mb-2"<?= $notification["seen"] == false ? ' style="background: #D6EAFF; border-color: #9FCCFC;border-width: 3px"' : ""; ?>>
				<div class="card-body">
					<?php
						$post = !is_null($notification["post"]) ? FeedEntry::getEntryById($notification["post"]) : null;

						if($notification["type"] == NOTIFICATION_TYPE_MENTION){
							$u = !is_null($post) ? $post->getUser() : null;

							?>
							<div class="small text-muted">
								<i class="fas fa-at text-blue"></i> <a href="/<?= $u->getUsername(); ?>" class="clearUnderline ignoreParentClick convertEmoji"><img src="<?= $u->getAvatarURL(); ?>" width="16" height="16"/> <?= $u->getDisplayName() . $u->renderCheckMark(); ?></a> mentioned you &bull; <?= Util::timeago($notification["time"]); ?>
							</div>
							<?php
						} else if($notification["type"] == NOTIFICATION_TYPE_REPLY){
							$u = !is_null($post) ? $post->getUser() : null;
							if(is_null($u)) continue;

							?>
							<div class="small text-muted">
								<i class="fas fa-share text-blue"></i> <a href="/<?= $u->getUsername(); ?>" class="clearUnderline ignoreParentClick convertEmoji"><img src="<?= $u->getAvatarURL(); ?>" width="16" height="16"/> <?= $u->getDisplayName() . $u->renderCheckMark(); ?></a> replied to your post &bull; <?= Util::timeago($notification["time"]); ?>
							</div>
							<?php
						} else if($notification["type"] == NOTIFICATION_TYPE_SHARE){
							$u = User::getUserById($notification["follower"]);
							?>
							<div class="small text-muted">
								<i class="fas fa-share-alt text-blue"></i> <a href="/<?= $u->getUsername(); ?>" class="clearUnderline ignoreParentClick convertEmoji"><img src="<?= $u->getAvatarURL(); ?>" width="16" height="16"/> <?= $u->getDisplayName() . $u->renderCheckMark(); ?></a> shared your post &bull; <?= Util::timeago($notification["time"]); ?>
							</div>
							<?php
						} else if($notification["type"] == NOTIFICATION_TYPE_FAVORITE){
							$u = User::getUserById($notification["follower"]);

							?>
							<div class="small text-muted">
								<i class="fas fa-star" style="color:gold"></i> <a href="/<?= $u->getUsername(); ?>" class="clearUnderline ignoreParentClick convertEmoji"><img src="<?= $u->getAvatarURL(); ?>" width="16" height="16"/> <?= $u->getDisplayName() . $u->renderCheckMark(); ?></a> favorited your post &bull; <?= Util::timeago($notification["time"]); ?>
							</div>
							<?php
						} else if($notification["type"] == NOTIFICATION_TYPE_NEW_FOLLOWER){
							$u2 = User::getUserById($notification["follower"]);

							if(!is_null($u2)){
								?>
					<div class="my-1" style="border-color: #CCC;<?= $notification["seen"] == false ? " background: " . (Util::isUsingNightMode() ? "#595959" : "#D6EAFF") . "; border-color: #9FCCFC;border-width: 3px" : ""; ?>">
						<div class="float-left mr-1">
							<a href="/<?= $u2->getUsername(); ?>" class="clearUnderline">
								<img class="rounded mx-1 my-1" src="<?= $u2->getAvatarURL(); ?>" width="40" height="40"/>
							</a>
						</div>

						<div class="float-left">
							<div class="text-muted small">New follower - @<?= $u2->getUsername(); ?></div>
							<b><a href="/<?= $u2->getUsername(); ?>" class="clearUnderline convertEmoji"><?= $u2->getDisplayName() . $u2->renderCheckMark(); ?></a></b> is now following you. &bull; <span class="text-muted"><?= Util::timeago($notification["time"]); ?></span>
						</div>
					</div>
								<?php
		
								$l = true;
							}
						}

						if(!is_null($post)){
					?>
					<hr/>
					<?= $post->toListHTML() ?>
					<?php } ?>
				</div>
			</div>
			<?php

			}

			echo Util::paginate($currentPage,$itemsPerPage,$num,"/notifications/(:num)");
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
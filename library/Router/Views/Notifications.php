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
				} else if($notification["type"] == NOTIFICATION_TYPE_NEW_FOLLOWER){
					$u2 = User::getUserById($notification["follower"]);

					if(!is_null($u2)){
						?>
			<div class="my-1 px-2 py-2 border-top<?= $l ? " border-bottom" : ""; ?>" style="border-color: #CCC;<?= $notification["seen"] == false ? " background: #9FCCFC" : ""; ?>">
				<div class="row">
					<div class="col-lg-1">
						<a href="/<?= $u2->getUsername(); ?>" class="clearUnderline">
							<img class="rounded mx-1 my-1" src="<?= $user->getAvatarURL(); ?>" width="40" height="40"/>
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
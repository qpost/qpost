<?php

$mysqli = Database::Instance()->get();
$itemsPerPage = 40;

$num = $user->getFollowing();
$uID = $user->getId();

$showNoUsersInfo = false;

if($num > 0){
	$users = [];

	$stmt = $mysqli->prepare("SELECT u.* FROM `follows` AS f INNER JOIN `users` AS u ON f.`following` = u.`id` WHERE f.`follower` = ? ORDER BY f.`time` DESC LIMIT " . (($currentPage-1)*$itemsPerPage) . " , " . $itemsPerPage);
	$stmt->bind_param("i",$uID);
	if($stmt->execute()){
		$result = $stmt->get_result();

		if($result->num_rows){
			while($row = $result->fetch_assoc()){
				array_push($users,User::getUserByData($row["id"],$row["gigadriveId"],$row["displayName"],$row["username"],$row["password"],$row["email"],$row["avatar"],$row["bio"],$row["token"],$row["birthday"],$row["privacy.level"],$row["featuredBox.title"],$row["featuredBox.content"],$row["lastGigadriveUpdate"],$row["gigadriveJoinDate"],$row["time"],$row["emailActivated"],$row["emailActivationToken"],$row["lastUsernameChange"]));
			}
		}
	}
	$stmt->close();

	echo Util::paginate($currentPage,$itemsPerPage,$num,"/" . $user->getUsername() . "/following/(:num)");

	if(count($users) > 0){
		echo '<div class="row mt-2">';

		for($i = 0; $i < count($users); $i++){
			$u = $users[$i];
			$last = $i == count($users)-1;

			$u->cacheFollower($uID);
		?>
		<div class="col-md-4 px-1 py-1">
			<div class="card userCard" data-user-id="<?= $u->getId(); ?>" style="height: 327px">
				<div class="px-2 py-2">
					<center>
						<a href="<?= $app->routeUrl("/" . $u->getUsername()); ?>" class="clearUnderline"><img src="<?= $u->getAvatarURL(); ?>" width="60" height="60" class="rounded mb-1"/>

						<h5 class="mb-0"><?= $u->getDisplayName(); ?></h5></a>
						<p class="text-muted my-0" style="font-size: 16px">@<?= $u->getUsername(); ?></p>

						<?= Util::followButton($u->getId(),true,["btn-block","mt-2"]) ?>

						<?= !is_null($u->getBio()) ? '<p class="mb-0 mt-2">' . Util::convertLineBreaksToHTML($u->getBio()) . '</p>' : ""; ?>
					</center>
				</div>
			</div>
		</div>
		<?php
		}

		echo '</div>';
	} else {
		$showNoUsersInfo = true;
	}

	echo Util::paginate($currentPage,$itemsPerPage,$num,"/" . $user->getUsername() . "/following/(:num)");
} else {
	$showNoUsersInfo = true;
}

if($showNoUsersInfo){
	echo '<div class="mt-2">' . Util::createAlert("noUsers","<b>There's nothing here yet!</b><br/>@" . $user->getUsername() . " has not followed anybody yet!",ALERT_TYPE_INFO) . '</div>';
}
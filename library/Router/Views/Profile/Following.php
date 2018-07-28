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
				array_push($users,User::getUserByData($row["id"],$row["displayName"],$row["username"],$row["email"],$row["avatar"],$row["bio"],$row["time"]));
			}
		}
	}
	$stmt->close();

	echo Util::paginate($currentPage,$itemsPerPage,$num,"/" . $user->getUsername() . "/following/(:num)");

	if(count($users) > 0){
		echo '<div class="row">';

		for($i = 0; $i < count($users); $i++){
			$user = $users[$i];
			$last = $i == count($users)-1;
		?>
		<div class="card userCard col-md-4" data-user-id="<?= $user->getId(); ?>">
			<div class="card-body">
				<?php var_dump($user); ?>
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
	echo '<div class="mt-2">' . Util::createAlert("noPosts","<b>There's nothing here yet!</b><br/>@" . $user->getUsername() . " has not followed anybody yet!",ALERT_TYPE_INFO) . '</div>';
}
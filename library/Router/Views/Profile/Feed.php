<?php

$mysqli = Database::Instance()->get();
$itemsPerPage = 40;

$num = $user->getFeedEntries();
$uID = $user->getId();

$showNoEntriesInfo = false;

if(isset($_SESSION["profileLoadPost"])){
	if(!isset($preventStatusModal) || $preventStatusModal != false){
		echo '<script>showStatusModal(' . $_SESSION["profileLoadPost"] . ');</script>';
	}
}

if(Util::isLoggedIn() && $uID == Util::getCurrentUser()->getId())
	echo Util::renderCreatePostForm();

if($num > 0){
	$feedEntries = [];

	$stmt = $mysqli->prepare("SELECT * FROM `feed` WHERE ((`post` IS NULL AND `type` = 'POST') OR (`type` != 'POST')) AND `user` = ? ORDER BY `time` DESC LIMIT " . (($currentPage-1)*$itemsPerPage) . " , " . $itemsPerPage);
	$stmt->bind_param("i",$uID);
	if($stmt->execute()){
		$result = $stmt->get_result();

		if($result->num_rows){
			while($row = $result->fetch_assoc()){
				array_push($feedEntries,FeedEntry::getEntryFromData($row["id"],$row["user"],$row["text"],$row["following"],$row["post"],$row["sessionId"],$row["type"],$row["count.replies"],$row["count.shares"],$row["count.favorites"],$row["time"]));
			}
		}
	}
	$stmt->close();

	if(count($feedEntries) > 0){
		echo '<ul class="list-group feedContainer mt-2">';

		for($i = 0; $i < count($feedEntries); $i++){
			$entry = $feedEntries[$i];
			
			echo $entry->toListHTML();
		}

		echo '</ul>';
	} else {
		$showNoEntriesInfo = true;
	}

	echo Util::paginate($currentPage,$itemsPerPage,$num,"/" . $user->getUsername() . "/(:num)");
} else {
	$showNoEntriesInfo = true;
}

if($showNoEntriesInfo){
	echo '<div class="mt-2">' . Util::createAlert("noEntries","<b>There's nothing here yet!</b><br/>@" . $user->getUsername() . " has not posted anything yet!",ALERT_TYPE_INFO) . '</div>';
}
<?php

$mysqli = Database::Instance()->get();
$itemsPerPage = 40;

$num = $user->getFeedEntries();
$uID = $user->getId();

$showNoEntriesInfo = false;

if(Util::isLoggedIn() && $uID == Util::getCurrentUser()->getId()){
?>
<div class="card mt-2 border-primary" style="background: #9FCCFC" id="profilePostBox">
	<div class="card-body">
		<textarea class="form-control" id="profilePostField" placeholder="Post something for your followers!"></textarea>

		<p class="mb-0 mt-2 float-left small" id="profileCharacterCounter">
			<?= POST_CHARACTER_LIMIT ?> characters left
		</p>

		<button type="button" class="btn btn-primary btn-sm float-right mb-0 mt-2" id="profilePostButton">Post</button>
	</div>
</div>
<?php
}

if($num > 0){
	$feedEntries = [];

	$stmt = $mysqli->prepare("SELECT * FROM `feed` WHERE `user` = ? ORDER BY `time` DESC LIMIT " . (($currentPage-1)*$itemsPerPage) . " , " . $itemsPerPage);
	$stmt->bind_param("i",$uID);
	if($stmt->execute()){
		$result = $stmt->get_result();

		if($result->num_rows){
			while($row = $result->fetch_assoc()){
				array_push($feedEntries,FeedEntry::getEntryFromData($row["id"],$row["user"],$row["text"],$row["following"],$row["sessionId"],$row["type"],$row["time"]));
			}
		}
	}
	$stmt->close();

	if(count($feedEntries) > 0){
		echo '<div class="feedContainer mt-2">';

		$l = false;

		for($i = 0; $i < count($feedEntries); $i++){
			$entry = $feedEntries[$i];
			$last = $i == count($feedEntries)-1;

			if($entry->getType() == "POST"){
		?>
		<div class="card feedEntry<?= !$last ? " mb-2" : "" ?>" data-entry-id="<?= $entry->getId(); ?>">
			<div class="card-body">
				<div class="row">
					<div class="col-1">
						<img class="rounded mx-1 my-1" src="<?= $user->getAvatarURL(); ?>" width="40" height="40"/>
					</div>

					<div class="col-11">
						<p class="mb-0">
							<span class="font-weight-bold"><?= $user->getDisplayName(); ?></span>
							<span class="text-muted font-weight-normal">@<?= $user->getUsername(); ?></span>

							&bull;

							<?= Util::timeago($entry->getTime()); ?>
						</p>

						<p class="mb-0 convertEmoji">
							<?= Util::convertPost($entry->getText()); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
				$l = false;
			} else if($entry->getType() == "NEW_FOLLOWING") {
				$u2 = $entry->getFollowing();
				?>
		<p class="my-1 px-2 py-2 border-top<?= $l ? " border-bottom" : ""; ?>" style="border-color: #CCC">
			<b><a href="/<?= $user->getUsername(); ?>" class="clearUnderline"><?= $user->getDisplayName(); ?></a></b> is now following <a href="/<?= $u2->getUsername(); ?>" class="clearUnderline"><?= $u2->getDisplayName(); ?></a> &bull; <span class="text-muted"><?= Util::timeago($entry->getTime()); ?></span>
		</p>
				<?php
				$l = true;
			}
		}

		echo '</div>';
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
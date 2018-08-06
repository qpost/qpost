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
		echo '<div class="feedContainer mt-2">';

		$l = false;

		for($i = 0; $i < count($feedEntries); $i++){
			$entry = $feedEntries[$i];
			$last = $i == count($feedEntries)-1;

			if($entry->getType() == "POST"){
		?>
		<div class="card feedEntry<?= !$last ? " mb-2" : "" ?> statusTrigger" data-status-render="<?= $entry->getId() ?>" data-entry-id="<?= $entry->getId(); ?>">
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

						<?= Util::getPostActionButtons($entry); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
			} else if($entry->getType() == "NEW_FOLLOWING") {
				$u2 = $entry->getFollowing();
				?>
		<div class="my-1 px-2 py-0" data-entry-id="<?= $entry->getId() ?>">
			<?php if(Util::isLoggedIn() && Util::getCurrentUser()->getId() == $entry->getUserId()){ ?>
			<div class="float-right">
				<span class="deleteButton ml-2" data-post-id="<?= $entry->getId() ?>" data-toggle="tooltip" title="Delete">
					<i class="fas fa-trash-alt"></i>
				</span>
			</div>
			<?php } ?>
			<b><a href="/<?= $user->getUsername(); ?>" class="clearUnderline"><?= $user->getDisplayName(); ?></a></b> is now following <a href="/<?= $u2->getUsername(); ?>" class="clearUnderline"><?= $u2->getDisplayName(); ?></a> &bull; <span class="text-muted"><?= Util::timeago($entry->getTime()); ?></span>
		</div>
				<?php
			} else if($entry->getType() == "SHARE"){
				$sharedPost = $entry->getPost();
				$u = $sharedPost->getUser();

				?>
		<div class="card feedEntry<?= !$last ? " mb-2" : "" ?> statusTrigger" data-status-render="<?= $sharedPost->getId() ?>" data-entry-id="<?= $sharedPost->getId(); ?>">
			<div class="card-body">
				<div class="small text-muted">
					<i class="fas fa-share-alt text-primary"></i> Shared by <?= $user->getDisplayName(); ?> &bull; <?= Util::timeago($entry->getTime()); ?>
				</div>
				<div class="row">
					<div class="col-1">
						<a href="/<?= $u->getUsername(); ?>" class="clearUnderline ignoreParentClick">
							<img class="rounded mx-1 my-1" src="<?= $u->getAvatarURL(); ?>" width="40" height="40"/>
						</a>
					</div>

					<div class="col-11">
						<p class="mb-0">
							<a href="/<?= $u->getUsername(); ?>" class="clearUnderline ignoreParentClick">
								<span class="font-weight-bold"><?= $u->getDisplayName(); ?></span>
							</a>

							<span class="text-muted font-weight-normal">@<?= $u->getUsername(); ?></span>

							&bull;

							<?= Util::timeago($sharedPost->getTime()); ?>
						</p>

						<p class="mb-0 convertEmoji">
							<?= Util::convertPost($sharedPost->getText()); ?>
						</p>

						<?= Util::getPostActionButtons($sharedPost); ?>
					</div>
				</div>
			</div>
		</div>
				<?php
				$l = false;
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
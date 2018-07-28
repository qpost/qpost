<?php

$mysqli = Database::Instance()->get();
$itemsPerPage = 40;

$num = $user->getPosts();
$uID = $user->getId();

if($num > 0){
	$posts = [];

	$stmt = $mysqli->prepare("SELECT * FROM `posts` WHERE `user` = ? ORDER BY `time` DESC LIMIT " . (($currentPage-1)*$itemsPerPage) . " , " . $itemsPerPage);
	$stmt->bind_param("i",$uID);
	if($stmt->execute()){
		$result = $stmt->get_result();

		if($result->num_rows){
			while($row = $result->fetch_assoc()){
				array_push($posts,$row);
			}
		}
	}
	$stmt->close();

	echo Util::paginate($currentPage,$itemsPerPage,$num,"/" . $user->getUsername() . "/(:num)");

	if(count($posts) > 0){
		echo '<div class="card postContainer mt-2"><div class="card-body">';

		for($i = 0; $i < count($posts); $i++){
			$post = $posts[$i];
			$last = $i == count($posts)-1;
		?>
		<div class="card post<?= !$last ? " mb-2" : "" ?>" data-post-id="<?= $post["id"]; ?>">
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

							<?= Util::timeago($post["time"]); ?>
						</p>

						<p class="mb-0">
							<?= $post["text"]; ?>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
		}

		echo '</div></div>';
	}

	echo Util::paginate($currentPage,$itemsPerPage,$num,"/" . $user->getUsername() . "/(:num)");
}
<div class="card-body">
	<h4>Your social net</h4>

	<?php

	echo Util::createAlert("socialNetInfo","The social net shows you a list of users that you might be interested in.",ALERT_TYPE_INFO,true,true);

	$currentUser = Util::getCurrentUser()->getId();

	// https://stackoverflow.com/a/24165699

	$mysqli = Database::Instance()->get();
	$stmt = $mysqli->prepare("SELECT
		COUNT(*) AS mutuals,
		u.*
		FROM follows fu
		JOIN follows fu2
			ON fu2.following = fu.follower
			AND NOT EXISTS (SELECT 1 FROM follows fu3 
				WHERE fu3.following = ?
				AND fu3.follower = fu2.follower)
		INNER JOIN users AS u
			ON u.id = fu2.follower
		WHERE fu.following = ?
		AND fu2.follower != ?
		GROUP BY fu2.follower
		ORDER BY mutuals DESC");
	$stmt->bind_param("iii",$currentUser,$currentUser,$currentUser);
	if($stmt->execute()){
		$result = $stmt->get_result();

		if($result->num_rows){
			while($row = $result->fetch_assoc()){
				$u = User::getUserByData($row["id"],$row["displayName"],$row["username"],$row["email"],$row["avatar"],$row["bio"],$row["token"],$row["privacy.level"],$row["featuredBox.title"],$row["featuredBox.content"],$row["lastGigadriveUpdate"],$row["time"]);
				$mutuals = $row["mutuals"];

				?>
				<div class="row my-2">
					<div class="card userCard col-md-6 offset-md-3 mb-3" data-user-id="<?= $u->getId(); ?>">
						<div class="card-body">
							<center>
								<a href="<?= $app->routeUrl("/" . $u->getUsername()); ?>" class="clearUnderline"><img src="<?= $u->getAvatarURL(); ?>" width="60" height="60"/>

								<h5 class="mb-0"><?= $u->getDisplayName(); ?></a></h5>
								<p class="text-muted my-0" style="font-size: 16px">@<?= $u->getUsername(); ?></p>

								<?= (($u->getPrivacyLevel() == PRIVACY_LEVEL_PUBLIC || (Util::isLoggedIn() && $u->isFollower($_SESSION["id"]))) && (!is_null($u->getBio()))) ? '<p class="mb-0 mt-2">' . Util::convertLineBreaksToHTML($u->getBio()) . '</p>' : ""; ?>

								<?= Util::followButton($u->getId(),true,["btn-block","mt-2"]) ?>
							</center>
						</div>
					</div>
				</div>
				<?php
			}
		} else {
			echo Util::createAlert("emptySocialNet","Your social net seems to be empty. Follow more people to get this page filled up!",ALERT_TYPE_DANGER);
		}
	}
	$stmt->close();

	?>
</div>
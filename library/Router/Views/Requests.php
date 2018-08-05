<div class="card-body">
    <?php

    $mysqli = Database::Instance()->get();

    if(isset($_POST["action"]) && isset($_POST["user"]) && is_numeric($_POST["user"])){
        $user = User::getUserById($_POST["user"]);

        if(!is_null($user)){
            if(!$user->isFollowing(Util::getCurrentUser()) && $user->hasSentFollowRequest(Util::getCurrentUser())){
                if($_POST["action"] == "accept"){
                    $user->follow(Util::getCurrentUser());
                } else if($_POST["action"] == "deny"){
                    $u1 = Util::getCurrentUser()->getId();
                    $u2 = $user->getId();

                    $stmt = $mysqli->prepare("DELETE FROM `follow_requests` WHERE `follower` = ? AND `following` = ?");
					$stmt->bind_param("ii",$u2,$u1);
					$stmt->execute();
					$stmt->close();

					Util::getCurrentUser()->reloadOpenFollowRequests();
                }       
            }
        }
    }

    $user = Util::getCurrentUser();
    $uid = $user->getId();
    if($user->getOpenFollowRequests() > 0){
        $stmt = $mysqli->prepare("SELECT u.* FROM `follow_requests` AS f INNER JOIN `users` AS u ON f.`follower` = u.`id` WHERE f.`following` = ? ORDER BY f.`time` DESC");
        $stmt->bind_param("i",$uid);
        if($stmt->execute()){
            $result = $stmt->get_result();

            if($result->num_rows){
                while($row = $result->fetch_assoc()){
                    $u = User::getUserByData($row["id"],$row["displayName"],$row["username"],$row["email"],$row["avatar"],$row["bio"],$row["token"],$row["privacy.level"],$row["featuredBox.title"],$row["featuredBox.content"],$row["lastGigadriveUpdate"],$row["time"]);

                    ?>
    <div class="row my-2">
		<div class="card userCard col-md-6 offset-md-3 mb-3" data-user-id="<?= $u->getId(); ?>">
			<div class="card-body">
				<center>
					<a href="<?= $app->routeUrl("/" . $u->getUsername()); ?>" class="clearUnderline"><img src="<?= $u->getAvatarURL(); ?>" width="60" height="60"/>

					<h5 class="mb-0"><?= $u->getDisplayName(); ?></a></h5>
					<p class="text-muted my-0" style="font-size: 16px">@<?= $u->getUsername(); ?></p>

					<?= (($u->getPrivacyLevel() == PRIVACY_LEVEL_PUBLIC || (Util::isLoggedIn() && $u->isFollower($_SESSION["id"]))) && (!is_null($u->getBio()))) ? '<p class="mb-0 mt-2">' . Util::convertLineBreaksToHTML($u->getBio()) . '</p>' : ""; ?>

					<form action="/requests" method="post">
                        <?= Util::insertCSRFToken() ?>

                        <input type="hidden" name="action" value="accept"/>
                        <input type="hidden" name="user" value="<?= $u->getId() ?>"/>

                        <input type="submit" class="btn btn-success" style="width: 100px" value="Accept"/>
                    </form>

                    <form action="/requests" method="post">
                        <?= Util::insertCSRFToken() ?>

                        <input type="hidden" name="action" value="deny"/>
                        <input type="hidden" name="user" value="<?= $u->getId() ?>"/>

                        <input type="submit" class="btn btn-danger mt-2" style="width: 100px" value="Deny"/>
                    </form>
				</center>
			</div>
		</div>
	</div>
                    <?php
                }
            } else {
                echo Util::createAlert("noRequests","You currently have no open follow requests.",ALERT_TYPE_INFO);
                $user->reloadOpenFollowRequests();
            }
        }
    } else {
        echo Util::createAlert("noRequests","You currently have no open follow requests.",ALERT_TYPE_INFO);
    }

    ?>
</div>
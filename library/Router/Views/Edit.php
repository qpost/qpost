<?php

$user = Util::getCurrentUser();

$errorMsg = null;
$successMsg = null;

$featuredBoxLimit = 5;

if(isset($_POST["displayName"]) && isset($_POST["bio"]) && isset($_POST["featuredBoxTitle"])){
	$displayName = $_POST["displayName"];
	$bio = $_POST["bio"];
	$featuredBoxTitle = trim($_POST["featuredBoxTitle"]);

	if(!empty(trim($displayName))){
		if(strlen($displayName) >= 1 && strlen($displayName) <= 25){
			if(strlen($bio) <= 400){
				if(empty($featuredBoxTitle) || strlen($featuredBoxTitle) <= 25){
					$boxUsers = [];

					for($i = 1; $i <= $featuredBoxLimit; $i++){
						if(isset($_POST["featuredBoxUser" . $i])){
							$c = $_POST["featuredBoxUser" . $i];

							if(!empty($c)){
								$linkedUser = User::getUserByUsername($c);

								if(is_null($linkedUser)){
									$errorMsg = "The user @" . Util::sanatizeString($c) . " does not exist.";
								} else if($linkedUser->getId() == Util::getCurrentUser()->getId()) {
									$errorMsg = "You can't link yourself in your Featured box.";
								} else {
									array_push($boxUsers,$linkedUser->getId());
								}
							}
						}
					}

					if(is_null($errorMsg)){
						if(count($boxUsers) == 0)
							$boxUsers = null;

						if(empty($featuredBoxTitle))
							$featuredBoxTitle = null;

						$displayName = Util::sanatizeString($displayName);
						$bio = Util::sanatizeString($bio);
						$userId = $user->getId();

						if(empty($bio))
							$bio = null;

						$boxUsersSerialized = is_null($boxUsers) ? null : json_encode($boxUsers);

						$mysqli = Database::Instance()->get();
						$stmt = $mysqli->prepare("UPDATE `users` SET `displayName` = ?, `bio` = ?, `featuredBox.title` = ?, `featuredBox.content` = ? WHERE `id` = ?");
						$stmt->bind_param("ssssi",$displayName,$bio,$featuredBoxTitle,$boxUsersSerialized,$userId);
						if($stmt->execute()){
							$successMsg = "Your changes have been saved.";
							$user->reload();
						} else {
							$errorMsg = "An error occurred. (" . $stmt->error . ")";
						}
						$stmt->close();
					}
				} else {
					$errorMsg = "The Featured box title must be less than 25 characters long.";
				}
			} else {
				$errorMsg = "The bio must be less than 400 characters long.";
			}
		} else {
			$errorMsg = "The display name must be between 1 and 25 characters long.";
		}
	} else {
		$errorMsg = "Please enter a display name.";
	}
}

?><div class="card-body">
	<?php

	if(!is_null($errorMsg))
		echo Util::createAlert("errorMsg",$errorMsg,ALERT_TYPE_DANGER,true);

	if(!is_null($successMsg))
		echo Util::createAlert("successMsg",$successMsg,ALERT_TYPE_SUCCESS,true);

	?>

	<form action="<?= $app->routeUrl("/edit") ?>" method="post">
		<?= Util::insertCSRFToken(); ?>

		<fieldset>
			<div class="form-group row">
				<label for="displayName" class="control-label col-sm-2 col-form-label">Display name</label>

				<div class="col-sm-10 input-group mb-3">
					<input class="form-control" type="text" name="displayName" id="displayName" min="1" max="25" value="<?= isset($_POST["displayName"]) ? Util::sanatizeString($_POST["displayName"]) : $user->getDisplayName(); ?>"/>
				</div>
			</div>

			<div class="form-group row">
				<label for="username" class="control-label col-sm-2 col-form-label">Username *</label>

				<div class="col-sm-10 input-group mb-3">
					<div class="input-group-prepend">
						<span class="input-group-text">@</span>
					</div>
					<input class="form-control disabled" disabled type="text" name="username" id="username" value="<?= $user->getUsername(); ?>"/>
				</div>
			</div>

			<div class="form-group row">
				<label for="bio" class="control-label col-sm-2 col-form-label">Bio</label>

				<div class="col-sm-10 input-group mb-3">
					<textarea class="form-control" name="bio" id="bio" style="resize:none !important;" max="400"><?= isset($_POST["bio"]) ? Util::sanatizeString($_POST["bio"]) : (!is_null($user->getBio()) ? $user->getBio() : "") ?></textarea>
				</div>
			</div>

			<div class="form-group row">
				<label for="bio" class="control-label col-sm-2 col-form-label">Profile Picture *</label>

				<div class="col-sm-10 input-group mb-3">
					<img src="<?= $user->getAvatarURL(); ?>" width="300" height="300"/>
				</div>
			</div>

			<div class="form-group row">
				<label for="bio" class="control-label col-sm-2 col-form-label">Featured Box Title</label>

				<div class="col-sm-10 input-group mb-3">
					<input class="form-control" type="text" name="featuredBoxTitle" id="featuredBoxTitle" max="25" value="<?= isset($_POST["featuredBoxTitle"]) ? Util::sanatizeString($_POST["featuredBoxTitle"]) : $user->getFeaturedBoxTitle(); ?>" placeholder="Featured"/>
				</div>
			</div>

			<div class="form-group row">
				<label for="bio" class="control-label col-sm-2 col-form-label">Featured Box Users</label>

				<div class="col-sm-10 mb-3">
					<?php

						for($i = 1; $i <= $featuredBoxLimit; $i++){
							?>
					<div class="input-group mb-2">
						<div class="input-group-prepend">
							<span class="input-group-text">@</span>
						</div>
						<input class="form-control" type="text" name="featuredBoxUser<?= $i ?>" id="featuredBoxUser<?= $i ?>" value="<?= isset($_POST["featuredBoxUser" . $i]) ? Util::sanatizeString($_POST["featuredBoxUser" . $i]) : (count($user->getFeaturedBoxContent()) >= $i ? User::getUserById($user->getFeaturedBoxContent()[$i-1])->getUsername() : ""); ?>"/>
					</div>
							<?php
						}

					?>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-sm-10 input-group mb-3 offset-sm-2">
					<button type="submit" class="btn btn-primary">Save changes</button>
				</div>
			</div>
		</fieldset>
	</form>

	<p class="mb-0 small">Fields marked with a <b>*</b> can only be changed on <a href="https://gigadrivegroup.com/account" target="_blank">gigadrivegroup.com</a>.</p>
</div>
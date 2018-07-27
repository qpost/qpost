<?php

$user = Util::getCurrentUser();

$errorMsg = null;
$successMsg = null;

if(isset($_POST["displayName"]) && isset($_POST["bio"])){
	$displayName = $_POST["displayName"];
	$bio = $_POST["bio"];

	if(!empty(trim($displayName))){
		if(strlen($displayName) >= 1 && strlen($displayName) <= 25){
			if(strlen($bio) <= 400){
				$displayName = Util::sanatizeString($displayName);
				$bio = Util::sanatizeString($bio);
				$userId = $user->getId();

				if(empty($bio))
					$bio = null;

				$mysqli = Database::Instance()->get();
				$stmt = $mysqli->prepare("UPDATE `users` SET `displayName` = ?, `bio` = ? WHERE `id` = ?");
				$stmt->bind_param("ssi",$displayName,$bio,$userId);
				if($stmt->execute()){
					$successMsg = "Your changes have been saved.";
					$user->reload();
				} else {
					$errorMsg = "An error occurred. (" . $stmt->error . ")";
				}
				$stmt->close();
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
	<h4>Edit your profile</h4>

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
				<label for="username" class="control-label col-sm-2 col-form-label">Username</label>

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
				<div class="col-sm-10 input-group mb-3 offset-sm-2">
					<button type="submit" class="btn btn-primary">Save changes</button>
				</div>
			</div>
		</fieldset>
	</form>

	<p class="mb-0 small">Some of your account data can only be changed on <a href="https://gigadrivegroup.com/account" target="_blank">gigadrivegroup.com</a>.</p>
</div>
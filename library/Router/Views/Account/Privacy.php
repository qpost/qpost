<?php

$user = Util::getCurrentUser();
$uID = $user->getId();

$errorMsg = null;
$successMsg = null;

if(isset($_POST["privacyLevel"])){
	if(!empty($_POST["privacyLevel"]) && ($_POST["privacyLevel"] == PRIVACY_LEVEL_PUBLIC || $_POST["privacyLevel"] == PRIVACY_LEVEL_PRIVATE  || $_POST["privacyLevel"] == PRIVACY_LEVEL_CLOSED)){
		$privacyLevel = $_POST["privacyLevel"];

		$mysqli = Database::Instance()->get();
		$stmt = $mysqli->prepare("UPDATE `users` SET `privacy.level` = ? WHERE `id` = ?");
		$stmt->bind_param("si",$privacyLevel,$uID);

		if($stmt->execute()){
			$successMsg = "Your changes have been saved.";
			$user->reload();
		} else {
			$errorMsg = "An error occurred. (" . $stmt->error . ")";
		}

		$stmt->close();
	}
}

if(!is_null($errorMsg))
	echo Util::createAlert("errorMsg",$errorMsg,ALERT_TYPE_DANGER,true);

if(!is_null($successMsg))
	echo Util::createAlert("successMsg",$successMsg,ALERT_TYPE_SUCCESS,true);

?><form action="/account/privacy" method="post">
	<?= Util::insertCSRFToken(); ?>

	<fieldset>
		<div class="form-group row mb-0">
			<label for="privacyLevel" class="control-label col-sm-2 col-form-label">Privacy level</label>

			<div class="col-sm-10 input-group">
				<select class="form-control" name="privacyLevel" id="privacyLevel">
					<option value="<?= PRIVACY_LEVEL_PUBLIC ?>"<?= $user->getPrivacyLevel() == PRIVACY_LEVEL_PUBLIC ? " selected" : "" ?>>Public - everyone can see your profile</option>
					<option value="<?= PRIVACY_LEVEL_PRIVATE ?>"<?= $user->getPrivacyLevel() == PRIVACY_LEVEL_PRIVATE ? " selected" : "" ?>>Private - only your followers can see your profile, followers must be confirmed</option>
					<option value="<?= PRIVACY_LEVEL_CLOSED ?>"<?= $user->getPrivacyLevel() == PRIVACY_LEVEL_CLOSED ? " selected" : "" ?>>Closed - only you can see your profile</option>
				</select>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-10 offset-sm-2 mt-0 mb-3 small">
				Your profile and your posts will only appear in searches when selecting "Public".
			</div>
		</div>

		<div class="form-group row">
			<div class="col-sm-10 input-group mb-3 offset-sm-2">
				<button type="submit" class="btn btn-primary">Save changes</button>
			</div>
		</div>
	</fieldset>
</form>
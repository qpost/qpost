<?php

$user = Util::getCurrentUser();

$errorMsg = null;
$successMsg = null;

$featuredBoxLimit = 5;

if(isset($_POST["displayName"]) && isset($_POST["bio"]) && isset($_POST["featuredBoxTitle"]) && isset($_POST["birthday"]) && isset($_POST["submit"])){
	$displayName = $_POST["displayName"];
	$bio = $_POST["bio"];
	$featuredBoxTitle = trim($_POST["featuredBoxTitle"]);
	$birthday = trim($_POST["birthday"]);
	$username = $user->getUsername();	

	if(!empty(trim($displayName))){
		if(strlen($displayName) >= 1 && strlen($displayName) <= 25){
			if(strlen($bio) <= 160){
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

					$usernameChange = false;

					if(!$user->isGigadriveLinked() && isset($_POST["username"])){
						$username = trim($_POST["username"]);

						if($username !== $user->getUsername()){
							if(is_null($user->getLastUsernameChange()) || (time()-strtotime($user->getLastUsernameChange())) >= 30*24*60*60){
								if(!empty($username)){
									if(strlen($username) >= 3){
										if(strlen($username) <= 16){
											if(ctype_alnum($username)){
												if(Util::isUsernameAvailable($username)){
													$usernameChange = true;
												} else {
													$errorMsg = "That username is not available anymore.";
												}
											} else {
												$errorMsg = "Your username may only consist of letters and numbers.";
											}
										} else {
											$errorMsg = "Your username may not be longer than 16 characters.";
										}
									} else {
										$errorMsg = "Your username must at least be 3 characters long.";
									}
								} else {
									$errorMsg = "Please enter a username.";
								}
							} else {
								$errorMsg = "You may only change your username every 30 days.";
							}
						}
					} else {
						$username = $user->getUsername();
					}

					$avatarUrl = $user->getAvatarURL();

					if(count($_FILES) > 0){
						$validFiles = 0;
						foreach($_FILES as $file){
							if(is_uploaded_file(realpath($file["tmp_name"]))){
								$validFiles++;
							}
						}

						if($validFiles > 0 && isset($_FILES["file"])){
							$file = $_FILES["file"];

							if(Util::startsWith($file["type"],"image/")){
								$tmpName = realpath($file["tmp_name"]);
								$fileName = $file["name"];

								if(is_uploaded_file($tmpName)){
									$ext = strtolower(pathinfo($fileName,PATHINFO_EXTENSION));

									if($ext == "jpg" || $ext == "jpeg" || $ext == "png" || $ext == "gif"){
										if(getimagesize($file["tmp_name"]) !== false){
											if($file["size"] <= 1000000){
												$size = 300;

												$tmpFile = __DIR__ . "/../../../tmp/" . rand(1,10000) . ".jpg";

												$image = new \Gumlet\ImageResize($file["tmp_name"]);
												$image->crop($size,$size);
												$image->save($tmpFile);
												
												$upload = Util::storeFileOnCDN("serv/qpost/avatars/" . $user->getId() . "/",$tmpFile);
												if(is_array($upload) && isset($upload["result"])){
													$avatarUrl = sprintf(GIGADRIVE_CDN_UPLOAD_FINAL_URL,"serv/qpost/avatars/" . $user->getId() . "/" . $upload["result"]);
												} else {
													$errorMsg = "An error occurred." . (isset($upload["error"]) ? " (" . $upload["error"] . ")" : "");
												}

												unlink($tmpFile);
											} else {
												$errorMsg = "The selected file is too large.";
											}
										} else {
											$errorMsg = "Plase select an image file.";
										}
									} else {
										$errorMsg = "Please select an image file.";
									}
								} else {
									$errorMsg = "An error occurred.";
								}
							} else {
								$errorMsg = "Please select an image file.";
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

						$birthdayTime = strtotime($birthday);
						$birthday = null;

						if($birthdayTime !== false){
							if($birthdayTime <= time()-(13*365*24*60*60)){
								$birthday = date("Y-m-d",$birthdayTime);
							} else {
								$errorMsg = "Please select a valid birthday.";
							}
						}

						if(is_null($errorMsg)){
							if(empty($bio))
								$bio = null;

							$boxUsersSerialized = is_null($boxUsers) ? null : json_encode($boxUsers);

							$mysqli = Database::Instance()->get();
							$stmt = $mysqli->prepare("UPDATE `users` SET `displayName` = ?, `username` = ?, `avatar` = ?, `bio` = ?, `featuredBox.title` = ?, `featuredBox.content` = ?, `birthday` = ? WHERE `id` = ?");
							$stmt->bind_param("sssssssi",$displayName,$username,$avatarUrl,$bio,$featuredBoxTitle,$boxUsersSerialized,$birthday,$userId);
							if($stmt->execute()){
								if($usernameChange)
									$user->updateLastUsernameChange();

								$successMsg = "Your changes have been saved.";
								$user->reload();
							} else {
								$errorMsg = "An error occurred. (" . $stmt->error . ")";
							}
							$stmt->close();
						}
					}
				} else {
					$errorMsg = "The Featured box title must be less than 25 characters long.";
				}
			} else {
				$errorMsg = "The bio must be less than 160 characters long.";
			}
		} else {
			$errorMsg = "The display name must be between 1 and 25 characters long.";
		}
	} else {
		$errorMsg = "Please enter a display name.";
	}
} else if(isset($_POST["deleteProfilePicture"])){
	$user->resetAvatar();
	$successMsg = "Your profile picture has been removed." . ($user->isGigadriveLinked() ? "<br/>Please note that this does not affect your linked Gigadrive account." : "");
}

?><div class="legacyCardBody">
	<?php

	if(!is_null($errorMsg))
		echo Util::createAlert("errorMsg",$errorMsg,ALERT_TYPE_DANGER,true);

	if(!is_null($successMsg))
		echo Util::createAlert("successMsg",$successMsg,ALERT_TYPE_SUCCESS,true);

	?>

	<form action="<?= $app->routeUrl("/edit") ?>" method="post" enctype="multipart/form-data">
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

				<div class="col-sm-10 input-group">
					<div class="input-group-prepend">
						<span class="input-group-text">@</span>
					</div>
					<input class="form-control disabled" <?php if($user->isGigadriveLinked()){ ?>disabled <?php } ?>type="text" min="3" max="16" name="username" id="username" value="<?= isset($_POST["username"]) ? Util::sanatizeString($_POST["username"]) : $user->getUsername(); ?>"/>
				</div>
			</div>

			<?php if($user->isGigadriveLinked()){ ?>
			<div class="form-group row">
				<div class="col-sm-10 input-group mb-3 offset-sm-2 small">
					Your username can only be changed on <a href="https://gigadrivegroup.com/account" target="_blank" class="ml-1">gigadrivegroup.com</a>.
				</div>
			</div>
			<?php } else { ?>
			<div class="form-group row">
				<div class="col-sm-10 input-group mb-3 offset-sm-2 small">
					<b class="mr-1">Note:</b> You can only change your username every 30 days!
				</div>
			</div>
			<?php } ?>

			<div class="form-group row">
				<label for="bio" class="control-label col-sm-2 col-form-label">Bio</label>

				<div class="col-sm-10 input-group mb-3">
					<textarea class="form-control" name="bio" id="bio" style="resize:none !important;" max="400"><?= isset($_POST["bio"]) ? Util::sanatizeString($_POST["bio"]) : (!is_null($user->getBio()) ? $user->getBio() : "") ?></textarea>
				</div>
			</div>

			<div class="form-group row">
				<label for="birthday" class="control-label col-sm-2 col-form-label">Birthday</label>

				<div class="col-sm-10 input-group mb-3">
					<input type="text" class="form-control birthdayDatepicker" name="birthday" id="birthday" value="<?= isset($_POST["birthday"]) ? Util::sanatizeString($_POST["birthday"]) : (!is_null($user->getBirthday()) ? date("m/d/Y",strtotime($user->getBirthday())) : "") ?>"/>
				</div>
			</div>

			<div class="form-group row">
				<label class="control-label col-sm-2 col-form-label">Profile Picture</label>

				<div class="col-sm-10 mb-3 text-center">
					<div class="mt-3">
						<img src="<?= $user->getAvatarURL() ?>" width="300" height="300" class="rounded"/>

						<div class="custom-file">
							<input type="file" class="custom-file-input" id="customFile" name="file">
							<label class="custom-file-label text-left" for="customFile">Choose file</label>
						</div>

						<small>
							Allowed file types: .jpg, .png, .gif | Max size: 1 MB<br/>
							Optimal resolution: 300x300 pixels<br/>
						</small>

						<button type="submit" name="deleteProfilePicture" class="btn btn-danger btn-sm mt-2">Delete profile picture</button>
					</div>
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
					<button type="submit" name="submit" class="btn btn-primary">Save changes</button>
				</div>
			</div>
		</fieldset>
	</form>
</div>
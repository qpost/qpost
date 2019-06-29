<?php

use Gumlet\ImageResize;
use qpost\Account\User;
use qpost\Database\Database;
use qpost\Util\Util;

$app->bind("/edit", function () {
	if(Util::isLoggedIn()){
		$user = Util::getCurrentUser();

		$errorMsg = null;
		$successMsg = null;

		$featuredBoxLimit = 5;

		if (isset($_POST["displayName"]) && isset($_POST["bio"]) && isset($_POST["featuredBoxTitle"]) && isset($_POST["birthday"]) && isset($_POST["submit"])) {
			$displayName = Util::fixString($_POST["displayName"]);
			$bio = Util::fixString($_POST["bio"]);
			$featuredBoxTitle = trim(Util::fixString($_POST["featuredBoxTitle"]));
			$birthday = trim(Util::fixString($_POST["birthday"]));
			$username = $user->getUsername();

			if (!Util::isEmpty(trim($displayName))) {
				if (strlen($displayName) >= 1 && strlen($displayName) <= 25) {
					if (strlen($bio) <= 160) {
						if (Util::isEmpty($featuredBoxTitle) || strlen($featuredBoxTitle) <= 25) {
							if (!Util::contains($displayName, "☑️") && !Util::contains($displayName, "✔️") && !Util::contains($displayName, "✅") && !Util::contains($displayName, "🗹") && !Util::contains($displayName, "🗸")) {
								$boxUsers = [];

								for ($i = 1; $i <= $featuredBoxLimit; $i++) {
									if (isset($_POST["featuredBoxUser" . $i])) {
										$c = $_POST["featuredBoxUser" . $i];

										if (!Util::isEmpty($c)) {
											$linkedUser = User::getUserByUsername($c);

											if (is_null($linkedUser)) {
												$errorMsg = "The user @" . Util::sanatizeString($c) . " does not exist.";
											} else if ($linkedUser->getId() == Util::getCurrentUser()->getId()) {
												$errorMsg = "You can't link yourself in your Featured box.";
											} else {
												array_push($boxUsers, $linkedUser->getId());
											}
										}
									}
								}

								$usernameChange = false;
								$verified = $user->isVerified();

								if (isset($_POST["username"])) {
									$username = trim($_POST["username"]);

									if ($username !== $user->getUsername()) {
										if (is_null($user->getLastUsernameChange()) || (time() - strtotime($user->getLastUsernameChange())) >= 30 * 24 * 60 * 60) {
											if (!Util::isEmpty($username)) {
												if (strlen($username) >= 3) {
													if (strlen($username) <= 16) {
														if (ctype_alnum($username)) {
															if (Util::isUsernameAvailable($username)) {
																$usernameChange = true;
																$verified = false;
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

								if (count($_FILES) > 0) {
									$validFiles = 0;
									foreach ($_FILES as $file) {
										if (is_uploaded_file(realpath($file["tmp_name"]))) {
											$validFiles++;
										}
									}

									if ($validFiles > 0 && isset($_FILES["file"])) {
										$file = $_FILES["file"];

										if (Util::startsWith($file["type"], "image/")) {
											$tmpName = realpath($file["tmp_name"]);
											$fileName = $file["name"];

											if (is_uploaded_file($tmpName)) {
												$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

												if ($ext == "jpg" || $ext == "jpeg" || $ext == "png" || $ext == "gif") {
													if (getimagesize($file["tmp_name"]) !== false) {
														if ($file["size"] <= 1000000) {
															$size = 300;

															$tmpFile = __DIR__ . "/../../../tmp/" . rand(1, 10000) . ".jpg";

															$image = new ImageResize($file["tmp_name"]);
															$image->crop($size, $size);
															$image->save($tmpFile);

															$upload = Util::storeFileOnCDN($tmpFile);
															if (is_array($upload) && isset($upload["url"])) {
																$avatarUrl = $upload["url"];
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

								if (is_null($errorMsg)) {
									if (count($boxUsers) == 0)
										$boxUsers = null;

									if (Util::isEmpty($featuredBoxTitle))
										$featuredBoxTitle = null;

									$displayName = Util::sanatizeString($displayName);
									$bio = Util::sanatizeString($bio);
									$userId = $user->getId();

									$birthdayTime = strtotime($birthday);
									$birthday = null;

									if ($birthdayTime !== false) {
										if ($birthdayTime <= time() - (13 * 365 * 24 * 60 * 60)) {
											$birthday = date("Y-m-d", $birthdayTime);
										} else {
											$errorMsg = "Please select a valid birthday.";
										}
									}

									if (is_null($errorMsg)) {
										if (Util::isEmpty($bio))
											$bio = null;

										$boxUsersSerialized = is_null($boxUsers) ? null : json_encode($boxUsers);

										$mysqli = Database::Instance()->get();
										$stmt = $mysqli->prepare("UPDATE `users` SET `displayName` = ?, `username` = ?, `avatar` = ?, `bio` = ?, `featuredBox.title` = ?, `featuredBox.content` = ?, `birthday` = ?, `verified` = ? WHERE `id` = ?");
										$stmt->bind_param("sssssssii", $displayName, $username, $avatarUrl, $bio, $featuredBoxTitle, $boxUsersSerialized, $birthday, $verified, $userId);
										if ($stmt->execute()) {
											if ($usernameChange)
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
								$errorMsg = "Invalid display name.";
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
		} else if (isset($_POST["deleteProfilePicture"])) {
			$user->resetAvatar();
			$successMsg = "Your profile picture has been removed." . ($user->isGigadriveLinked() ? "<br/>Please note that this does not affect your linked Gigadrive account." : "");
		}

		return twig_render("pages/edit.html.twig", [
			"title" => "Edit your profile",
			"nav" => NAV_PROFILE,
			"errorMsg" => $errorMsg,
			"successMsg" => $successMsg,
			"featuredBoxLimit" => $featuredBoxLimit
		]);
	} else {
		return $this->reroute("/");
	}
});
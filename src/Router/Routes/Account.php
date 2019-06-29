<?php

use qpost\Account\IPInformation;
use qpost\Account\PrivacyLevel;
use qpost\Account\Token;
use qpost\Account\User;
use qpost\Database\Database;
use qpost\Util\Util;

$app->bind("/account",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	return twig_render("pages/account/index.html.twig", [
		"title" => "Account",
		"nav" => NAV_ACCOUNT
	]);
});

$app->bind("/account/privacy",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$user = Util::getCurrentUser();
	$uID = $user->getId();

	$errorMsg = null;
	$successMsg = null;

	if (isset($_POST["privacyLevel"])) {
		if (!Util::isEmpty($_POST["privacyLevel"]) && ($_POST["privacyLevel"] == PrivacyLevel::PUBLIC || $_POST["privacyLevel"] == PrivacyLevel::PRIVATE || $_POST["privacyLevel"] == PrivacyLevel::CLOSED)) {
			$privacyLevel = $_POST["privacyLevel"];

			$mysqli = Database::Instance()->get();
			$stmt = $mysqli->prepare("UPDATE `users` SET `privacy.level` = ? WHERE `id` = ?");
			$stmt->bind_param("si", $privacyLevel, $uID);

			if ($stmt->execute()) {
				if ($user->getOpenFollowRequests() > 0 && $privacyLevel == PrivacyLevel::PUBLIC) {
					$s = $mysqli->prepare("DELETE FROM `follow_requests` WHERE `following` = ?");
					$s->bind_param("i", $uID);
					$s->execute();
					$s->close();

					$user->reloadOpenFollowRequests();
				}

				$successMsg = "Your changes have been saved.";
				$user->reload();
			} else {
				$errorMsg = "An error occurred. (" . $stmt->error . ")";
			}

			$stmt->close();
		}
	}

	return twig_render("pages/account/privacy.html.twig", [
		"title" => "Privacy",
		"nav" => NAV_ACCOUNT,
		"errorMsg" => $errorMsg,
		"successMsg" => $successMsg
	]);
});

$app->bind("/account/sessions",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$mysqli = Database::Instance()->get();

	$errorMsg = null;
	$successMsg = null;

	if (isset($_POST["action"]) && !Util::isEmpty($_POST["action"])) {
		$action = $_POST["action"];

		if ($action == "logOutSession") {
			if (isset($_POST["sesstoken"]) && !Util::isEmpty($_POST["sesstoken"])) {
				$token = Token::getTokenById($_POST["sesstoken"]);

				if (!is_null($token) && $token->getUserId() == Util::getCurrentUser()->getId()) {
					$token->expire();
					$successMsg = "The session has been killed.";
				}
			}
		}
	}

	$sessions = [];

	$uID = Util::getCurrentUser()->getId();

	$stmt = $mysqli->prepare("SELECT `id` FROM `tokens` WHERE `expiry` > NOW() AND `user` = ? ORDER BY `lastAccessTime` DESC");
	$stmt->bind_param("i", $uID);
	if ($stmt->execute()) {
		$result = $stmt->get_result();
		if ($result->num_rows) {
			while ($row = $result->fetch_assoc()) {
				$token = Token::getTokenById($row["id"]);
				if (is_null($token)) continue;

				$userAgent = parse_user_agent($token->getUserAgent());
				$platform = $userAgent["platform"];
				$browser = $userAgent["browser"];
				$browserVersion = $userAgent["version"];

				$icon = "fas fa-globe";
				if (strpos($platform, "Xbox") !== false) $icon = "fab fa-xbox";
				if (strpos($platform, "PlayStation") !== false) $icon = "fab fa-playstation";
				if (strpos($platform, "Macintosh") !== false) $icon = "fab fa-apple";
				if (strpos($platform, "iPhone") !== false) $icon = "fab fa-apple";
				if (strpos($platform, "iPad") !== false) $icon = "fab fa-apple";
				if (strpos($platform, "iPod") !== false) $icon = "fab fa-apple";
				if (strpos($browser, "Android") !== false) $icon = "fab fa-android";
				if (strpos($browser, "BlackBerry") !== false) $icon = "fab fa-blackberry";
				if (strpos($browser, "Kindle") !== false) $icon = "fab fa-amazon";
				if (strpos($browser, "Firefox") !== false) $icon = "fab fa-firefox";
				if (strpos($browser, "Safari") !== false) $icon = "fab fa-safari";
				if (strpos($browser, "Internet Explorer") !== false) $icon = "fab fa-internet-explorer";
				if (strpos($browser, "Chrome") !== false) $icon = "fab fa-chrome";
				if (strpos($browser, "Opera") !== false) $icon = "fab fa-opera";
				if (strpos($browser, "Edge") !== false) $icon = "fab fa-edge";

				$ipInfo = IPInformation::getInformationFromIP($token->getIP());

				$sessions[] = [
					"platform" => $platform,
					"browser" => $browser,
					"browserVersion" => $browserVersion,
					"icon" => $icon,
					"token" => $token,
					"ipInfo" => $ipInfo,
					"current" => $_COOKIE["sesstoken"] == $token->getId()
				];
			}
		}
	}
	$stmt->close();

	return twig_render("pages/account/sessions.html.twig", [
		"title" => "Active sessions",
		"nav" => NAV_ACCOUNT,
		"errorMsg" => $errorMsg,
		"successMsg" => $successMsg,
		"sessions" => $sessions
	]);
});

$app->bind("/account/change-password",function(){
	if(!Util::isLoggedIn()) return $this->reroute("/login");
	if(Util::getCurrentUser()->isGigadriveLinked()) return $this->reroute("/account");

	$user = Util::getCurrentUser();

	$successMsg = null;
	$errorMsg = null;

	if (isset($_POST["currentPassword"]) && isset($_POST["newPassword"]) && isset($_POST["newPassword2"])) {
		$currentPassword = $_POST["currentPassword"];
		$newPassword = $_POST["newPassword"];
		$newPassword2 = $_POST["newPassword2"];

		if (!Util::isEmpty($currentPassword) && !Util::isEmpty($newPassword) && !Util::isEmpty($newPassword2)) {
			if (password_verify($currentPassword, $user->getPassword())) {
				if ($newPassword == $newPassword2) {
					$newHash = password_hash($newPassword, PASSWORD_BCRYPT);

					$mysqli = Database::Instance()->get();
					$userId = $user->getId();

					$stmt = $mysqli->prepare("UPDATE `users` SET `password` = ? WHERE `id` = ?");
					$stmt->bind_param("si", $newHash, $userId);
					if ($stmt->execute()) {
						$user->reload();
						$successMsg = "Your password has been changed.";
					} else {
						$errorMsg = "An error occurred. " . $stmt->error;
					}
					$stmt->close();
				} else {
					$errorMsg = "The new passwords do not match.";
				}
			} else {
				$errorMsg = "Your current password is not correct.";
			}
		} else {
			$errorMsg = "Please fill all of the fields.";
		}
	}

	return twig_render("pages/account/changePassword.html.twig", [
		"title" => "Change your password",
		"nav" => NAV_ACCOUNT,
		"errorMsg" => $errorMsg,
		"successMsg" => $successMsg
	]);
});

$app->bind("/account/verify-email",function(){
	$successMsg = null;
	$errorMsg = null;

	if (isset($_GET["account"]) && isset($_GET["verificationtoken"])) {
		$user = User::getUserById($_GET["account"]);

		if (!is_null($user)) {
			if ($user->isEmailActivated() == false && $user->getEmailActivationToken() == $_GET["verificationtoken"]) {
				$mysqli = Database::Instance()->get();

				$userID = $user->getId();

				$stmt = $mysqli->prepare("UPDATE `users` SET `emailActivated` = 1 WHERE `id` = ?");
				$stmt->bind_param("i", $userID);
				if ($stmt->execute()) {
					$user->reload();
					$successMsg = "Your email has been activated. You can now log in.";
				} else {
					$errorMsg = "An error occurred. " . $stmt->error;
				}
				$stmt->close();
			} else {
				$errorMsg = "An error occurred.";
			}
		} else {
			$errorMsg = "An error occurred.";
		}
	} else {
		$errorMsg = "An error occurred.";
	}

	return twig_render("pages/account/verifyEmail.html.twig", [
		"title" => "Verify your Email address",
		"errorMsg" => $errorMsg,
		"successMsg" => $successMsg
	]);
});
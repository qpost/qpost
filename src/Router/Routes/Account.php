<?php

namespace qpost\Router;

use DateTime;
use Doctrine\DBAL\Types\Type;
use qpost\Account\IPInformation;
use qpost\Account\PrivacyLevel;
use qpost\Account\Token;
use qpost\Account\User;
use qpost\Database\Database;
use qpost\Database\EntityManager;
use qpost\Navigation\NavPoint;
use qpost\Util\Util;

create_route("/account", function () {
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	return twig_render("pages/account/index.html.twig", [
		"title" => "Account",
		"nav" => NavPoint::ACCOUNT
	]);
});

create_route("/account/privacy", function () {
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
				if ($user->getOpenRequestsCount() > 0 && $privacyLevel == PrivacyLevel::PUBLIC) {
					$s = $mysqli->prepare("DELETE FROM `follow_requests` WHERE `following` = ?");
					$s->bind_param("i", $uID);
					$s->execute();
					$s->close();
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
		"nav" => NavPoint::ACCOUNT,
		"errorMsg" => $errorMsg,
		"successMsg" => $successMsg
	]);
});

create_route("/account/sessions", function () {
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$entityManager = EntityManager::instance();
	$user = Util::getCurrentUser();

	$errorMsg = null;
	$successMsg = null;

	if (isset($_POST["action"]) && !Util::isEmpty($_POST["action"])) {
		$action = $_POST["action"];

		if ($action == "logOutSession") {
			if (isset($_POST["sesstoken"]) && !Util::isEmpty($_POST["sesstoken"])) {
				/**
				 * @var Token $token
				 */
				$token = $entityManager->getRepository(Token::class)->findOneBy(["id" => $_POST["sesstoken"]]);

				if (!is_null($token) && $token->getUser()->getId() == $user->getId()) {
					$token->expire();
					$successMsg = "The session has been killed.";
				}
			}
		}
	}

	$sessions = [];

	/**
	 * @var Token[] $tokens
	 */
	$tokens = $entityManager->getRepository(Token::class)->createQueryBuilder("t")
		->where("t.expiry > :today")
		->setParameter("today", new DateTime("now"), Type::DATETIME)
		->andWhere("t.user = :user")
		->setParameter("user", $user)
		->orderBy("t.lastAccessTime", "DESC")
		->getQuery()
		->getResult();

	foreach ($tokens as $token) {
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

	return twig_render("pages/account/sessions.html.twig", [
		"title" => "Active sessions",
		"nav" => NavPoint::ACCOUNT,
		"errorMsg" => $errorMsg,
		"successMsg" => $successMsg,
		"sessions" => $sessions
	]);
});

create_route("/account/change-password", function () {
	if(!Util::isLoggedIn()) return $this->reroute("/login");

	$user = Util::getCurrentUser();

	if (!is_null($user->getGigadriveData())) return $this->reroute("/account");

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

					$entityManager = EntityManager::instance();

					$user->setPassword($newHash);

					$entityManager->persist($user);
					$entityManager->flush();

					$successMsg = "Your password has been changed.";
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
		"nav" => NavPoint::ACCOUNT,
		"errorMsg" => $errorMsg,
		"successMsg" => $successMsg
	]);
});

create_route("/account/verify-email", function () {
	$successMsg = null;
	$errorMsg = null;

	if (isset($_GET["account"]) && isset($_GET["verificationtoken"])) {
		$entityManager = EntityManager::instance();

		/**
		 * @var User $user
		 */
		$user = $entityManager->getRepository(User::class)->findOneBy(["id" => $_GET["account"]]);

		if (!is_null($user)) {
			if ($user->isEmailActivated() == false && $user->getEmailActivationToken() == $_GET["verificationtoken"]) {
				$user->setEmailActivated(true);

				$entityManager->persist($user);
				$entityManager->flush();

				$successMsg = "Your email has been activated. You can now log in.";
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
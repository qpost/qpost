<?php

use qpost\Account\User;use qpost\Util\Util;

if (isset($title) && !Util::isEmpty($title)) {
	$title = $title . " - " . $app["config.site"]["name"];
} else {
	$title = $app["config.site"]["name"];
}

if (!isset($originalTitle))
	$originalTitle = $title;

if (!isset($description) || is_null($description) || Util::isEmpty($description))
	$description = DEFAULT_DESCRIPTION;

if (!isset($socialImage) || is_null($socialImage) || Util::isEmpty($socialImage))
	$socialImage = DEFAULT_TWITTER_IMAGE;

$currentUser = Util::getCurrentUser();

/*if(isset($_SESSION["profileLoadPost"])){
	$post = FeedEntry::getEntryById($_SESSION["profileLoadPost"]);

	if(!is_null($post)){
		if(!is_null($post->getText()) && !Util::isEmpty(trim($post->getText()))){
			$title = $post->getUser()->getDisplayName() . " on qpost: \"" . Util::limitString($post->getText(),34,true) . "\"";
			$description = Util::limitString($post->getText(),150,true);
			$socialImage = $post->getUser()->getAvatarURL();

			if(!is_null($post->getAttachments()) && is_array($post->getAttachments()) && count($post->getAttachments()) > 0){
				foreach($post->getAttachmentObjects() as $mediaFile){
					if($mediaFile->getType() == "IMAGE"){
						$bigSocialImage = $mediaFile->getURL();
						break;
					}
				}
			}
		}
	}
}*/

?><!DOCTYPE html>
<html lang="en">
<head>
	<title><?= Util::sanatizeHTMLAttribute($title) ?></title>

	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
	<meta http-equiv="x-ua-compatible" content="ie=edge"/>
	<meta name="apple-mobile-web-app-capable" content="yes">

	<meta name="og:site_name" content="<?= Util::sanatizeHTMLAttribute($app["config.site"]["name"]) ?>"/>
	<meta name="og:title" content="<?= Util::sanatizeHTMLAttribute($title) ?>"/>
	<meta name="og:description" content="<?= Util::sanatizeHTMLAttribute($description) ?>"/>
	<?php if (isset($bigSocialImage) && !is_null($bigSocialImage) && !Util::isEmpty($bigSocialImage)) { ?>
		<meta name="og:image" content="<?= Util::sanatizeHTMLAttribute($bigSocialImage) ?>"/><?php } ?>

	<meta name="twitter:title" content="<?= Util::sanatizeHTMLAttribute($title) ?>"/>
	<meta name="twitter:description" content="<?= Util::sanatizeHTMLAttribute($description) ?>"/>
	<meta name="twitter:image" content="<?= Util::sanatizeHTMLAttribute($socialImage) ?>"/>
	<meta name="twitter:card" content="summary"/>

	<link rel="apple-touch-icon" sizes="180x180" href="/public/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/public/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/public/favicon-16x16.png">
	<link rel="manifest" href="/public/site.webmanifest">
	<link rel="mask-icon" href="/public/safari-pinned-tab.svg" color="#007bff">
	<meta name="apple-mobile-web-app-title" content="qpost">
	<meta name="application-name" content="qpost">
	<meta name="msapplication-TileColor" content="#007bff">
	<meta name="theme-color" content="#007bff">

	<meta name="description" content="<?= Util::sanatizeHTMLAttribute($description) ?>"/>
	<meta name="keywords" content="social,network,posts,profiles,pictures,bio,gigadrive"/>

	<noscript>
		<meta http-equiv="refresh" content="0; URL=https://gigadrivegroup.com/badbrowser">
	</noscript>

	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css"
		  integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous">

	<?= $app->style([
		"https://cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.0.3/cookieconsent.min.css",
		"assets:css/datepicker.min.css",
		"assets:css/jquery.highlight-within-textarea.css"]); ?>

	<?= $app->script([
		"assets:js/dropzone.js",
		"/build/bundle.js",
		"https://www.google.com/recaptcha/api.js",
		"https://cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.0.3/cookieconsent.min.js",
		"https://twemoji.maxcdn.com/2/twemoji.min.js?11.0",
		"assets:js/jquery.highlight-within-textarea.js"]); ?>

	<script>var CSRF_TOKEN = "<?= Util::sanatizeHTMLAttribute(CSRF_TOKEN) ?>";
		var POST_CHARACTER_LIMIT = <?= Util::getCharacterLimit() ?>;
			<?= Util::isLoggedIn() && !is_null($currentUser) ? 'var CURRENT_USER = ' . $currentUser->getId() . ';' : ""; ?>var restoreUrl = "<?= isset($_SESSION["profileLoadPost"]) ? "/" . FeedEntry::getEntryById($_SESSION["profileLoadPost"])->getUser()->getUsername() : "" ?>";
		var restoreTitle = "<?= isset($_SESSION["profileLoadPost"]) ? $originalTitle : "" ?>";
		var CURRENT_STATUS_MODAL = 0;</script><?php unset($_SESSION["profileLoadPost"]); ?>

	<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({
			google_ad_client: "ca-pub-6156128043207415",
			enable_page_level_ads: true
		});
	</script>
</head>
<body>
<nav id="mainNav"
	 class="navbar navbar-expand-lg navbar-dark fixed-top">
	<div class="container-fluid container">
		<div class="navbar-header">
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-navigation"
					aria-controls="main-navigation" aria-expanded="false" aria-label="Toggle navigation" style="">
				<span class="navbar-toggler-icon"></span>
			</button>

			<a class="navbar-brand" href="/"<?= !Util::isLoggedIn() ? " data-no-instant" : "" ?>>
				<img src="/assets/img/navlogo.png" style="height: 30px"/>
			</a>
		</div>

		<div class="collapse navbar-collapse" id="main-navigation">
			<ul class="nav navbar-nav ml-auto">
				<?php

				if (Util::isLoggedIn() && !is_null($currentUser)) {
					$unreadMessages = $currentUser->getUnreadMessages();
					$unreadNotifications = $currentUser->getUnreadNotifications();

					?>
					<li class="nav-item<?= (isset($nav) && $nav == NAV_HOME) ? " active" : ""; ?>">
						<a href="/" class="nav-link">
							home
						</a>
					</li>

					<li class="nav-item<?= (isset($nav) && $nav == NAV_PROFILE) ? " active" : ""; ?>">
						<a href="/<?= $currentUser->getUsername(); ?>" class="nav-link">
							my profile
						</a>
					</li>

					<li class="nav-item<?= (isset($nav) && $nav == NAV_NOTIFICATIONS) ? " active" : ""; ?>">
						<a href="/notifications" class="nav-link notificationTabMainNav" data-no-instant>
							notifications<?= !is_null($unreadNotifications) && $unreadNotifications > 0 ? " <b>(" . $unreadNotifications . ")</b>" : ""; ?>
						</a>
					</li>

					<!--<li class="nav-item<?= (isset($nav) && $nav == NAV_MESSAGES) ? " active" : ""; ?>">
								<a href="/messages" class="nav-link">
									messages<?= !is_null($unreadMessages) && $unreadMessages > 0 ? " <b>(" . $unreadMessages . ")</b>" : "</b>"; ?>
								</a>
							</li>-->

					<li class="nav-item dropdown<?= (isset($nav) && $nav == NAV_ACCOUNT) ? " active" : ""; ?>">
						<a href="#" class="nav-link dropdown-toggle" id="accountDropdown" role="button"
						   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<img src="<?= $currentUser->getAvatarUrl() ?>" width="24" height="24"
								 class="rounded border border-white"/>
						</a>

						<div class="dropdown-menu dropdown-menu-right shadow" aria-labelledBy="accountDropdown">
							<a href="/<?= $currentUser->getUsername() ?>" class="dropdown-item">
								<div class="font-weight-bold" style="font-size: 21px">
									<?= $currentUser->getDisplayName() ?>
								</div>
								<div class="text-muted" style="margin-top: -8px">
									@<?= $currentUser->getUsername() ?>
								</div>
							</a>

							<div class="dropdown-divider"></div>

							<a href="/<?= $currentUser->getUsername() ?>" class="dropdown-item"><i
										class="far fa-user"></i> Profile</a>
							<a href="/notifications" class="dropdown-item" data-no-instant><i class="far fa-bell"></i>
								Notifications</a>
							<a href="/messages" class="dropdown-item"><i class="far fa-envelope"></i> Messages</a>

							<div class="dropdown-divider"></div>

							<a href="/edit" class="dropdown-item">Edit profile</a>
							<a href="/account" class="dropdown-item">Settings and privacy</a>
							<a href="/logout" class="dropdown-item" data-no-instant>Log out</a>

							<div class="dropdown-divider"></div>

							<a href="#" class="dropdown-item nightModeToggle" data-no-instant>
								Toggle night mode
							</a>
						</div>
					</li>
					<?php
				}

				?>
			</ul>

			<ul class="nav navbar-nav navbar-right">
				<?php

				if (Util::isLoggedIn()) {
					?>
					<?php
				} else {
					?>
					<li class="nav-item<?= (isset($nav) && $nav == NAV_ACCOUNT) ? " active" : ""; ?>">
						<a href="/login" class="nav-link" data-no-instant>
							log in
						</a>
					</li>
					<?php
				}

				?>
			</ul>
		</div>
	</div>
</nav>

<div class="container navMargin">
	<div class="legacyCard">
		<?php if (Util::isLoggedIn()) { ?>
			<div class="d-none notificationPermissionAlert py-2 mb-3">
				<div class="container">
					<div class="float-left mt-1">
						Do you want to receive desktop notifications to keep up to date with the people you follow?
					</div>

					<div class="float-right">
						<button class="btn btn-primary btn-sm enableNotifications">Yes</button>
						<button class="btn btn-light btn-sm hideNotifications" data-toggle="tooltip"
								title="Hide this alert for 7 days">No
						</button>
					</div>
				</div>
			</div>

			<?php if (isset($user)) { ?>
				<div class="modal fade" id="blockModal" aria-labelledby="blockModalLabel" aria-hidden="true"
					 tabindex="-1" role="dialog">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="blockModalLabel">Block @<?= $user->getUsername() ?></h5>

								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>

							<div class="modal-body">
								@<?= $user->getUsername(); ?> will no longer be able to follow or message you, and you
								will not see notifications from @<?= $user->getUsername(); ?>.
							</div>

							<div class="modal-footer">
								<form action="/<?= $user->getUsername(); ?>" method="post">
									<?= Util::insertCSRFToken(); ?>
									<input type="hidden" name="action" value="block"/>
									<button type="submit" class="btn btn-danger">Block</button>
								</form>

								<button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
							</div>
						</div>
					</div>
				</div>
			<?php } ?>

		<?php } ?>

		<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-hidden="true"></div>
		<div class="modal fade" id="mediaModal" tabindex="-1" role="dialog" aria-hidden="true"></div>
		<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true"></div>

		<span class="dz-message"></span>

		<div class="preview-template d-none">
			<div class="dz-preview dz-file-preview well mt-2 mr-2 float-left" id="dz-preview-template">
				<div style="width: 100px; height: 100px; background-size: cover;" data-dz-thumbnail
					 class="rounded border border-mainColor bg-dark"></div>
				<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
				<div class="dz-success-mark"><span></span></div>
				<div class="dz-error-mark"><span></span></div>
				<div class="dz-error-message"><span data-dz-errormessage></span></div>
			</div>
		</div>

		<div class="wrapper"><?php

			if (isset($showProfile) && $showProfile == true && isset($user)) {
				?>
				<div class="legacyCardBody">
					<?php

					if (Util::isLoggedIn() && !is_null($currentUser)) {
						if (isset($_POST["action"]) && $_POST["action"] == "block") {
							$currentUser->block($user);
						} else if (isset($_POST["action"]) && $_POST["action"] == "unblock") {
							$currentUser->unblock($user);
						}
					}


					if (Util::isLoggedIn() && !is_null($currentUser) && $currentUser->hasBlocked($user)) {
						echo Util::createAlert("blocking", "<b>You blocked @" . $user->getUsername() . "</b><br/>@" . $user->getUsername() . " won't be able to view your profile or posts.", ALERT_TYPE_DANGER);
					}

					?>
					<div class="row">
						<div class="col col-xl-3 col-lg-4 mb-3">
							<div class="sticky-top" style="top: 70px">
								<div class="d-none d-lg-block">
									<center><img class="rounded border-mainColor mb-2 border"
												 src="<?= $user->getAvatarURL(); ?>" width="200" height="200"/></center>
									<h4 class="mb-0 convertEmoji"
										style="word-wrap: break-word;"><?= $user->getDisplayName() . $user->renderCheckMark(); ?></h4>
									<span class="text-muted"
										  style="font-size: 16px">@<?= $user->getUsername(); ?></span> <?= Util::isLoggedIn() && !is_null($currentUser) && $user->isFollowing($currentUser) ? '<span class="text-uppercase small followsYouBadge px-1 py-1">follows you</span>' : ""; ?>

									<?= !is_null($user->getBio()) ? '<p class="mb-0 mt-2 convertEmoji" style="word-wrap: break-word;">' . Util::convertPost($user->getBio()) . '</p>' : ""; ?>

									<p class="my-2 text-muted">
										<?php

										$date = strtotime($user->getTime());

										?><i class="fas fa-globe"></i><span
												style="margin-left: 5px">Joined <?= date("F Y", $date); ?></span>
										<?= !is_null($user->getBirthday()) ? '<br/><i class="fas fa-birthday-cake"></i><span style="margin-left: 7px">' . date("F jS Y", strtotime($user->getBirthday())) . '</span>' : "" ?>
									</p>

									<?= Util::followButton($user->getId(), true, ["btn-block", "mt-2"], false) ?>

									<?php

									if (Util::isLoggedIn() && !is_null($currentUser) && $currentUser->getId() != $user->getId()) {
										if ($currentUser->hasBlocked($user)) {
											?>
											<form action="/<?= $user->getUsername(); ?>" method="post">
												<?= Util::insertCSRFToken(); ?>
												<input type="hidden" name="action" value="unblock"/>

												<button type="submit" class="btn btn-light btn-block mt-2">
													Unblock
												</button>
											</form>
											<?php
										} else {
											?>
											<button type="button" class="btn btn-light btn-block mt-2"
													data-toggle="modal" data-target="#blockModal">
												Block
											</button>
											<?php
										}
									}
									?>
								</div>

								<div class="d-lg-none">
									<div class="row">
										<div class="col-4">
											<center><img class="rounded border-mainColor mb-2 border w-100"
														 src="<?= $user->getAvatarURL(); ?>" style="max-width: 128px"/>
											</center>
										</div>

										<div class="col-8">
											<h4 class="mb-0 convertEmoji"
												style="word-wrap: break-word;"><?= $user->getDisplayName() . $user->renderCheckMark(); ?></h4>
											<span class="text-muted"
												  style="font-size: 16px">@<?= $user->getUsername(); ?></span> <?= Util::isLoggedIn() && !is_null($currentUser) && $user->isFollowing($currentUser) ? '<span class="text-uppercase small followsYouBadge px-1 py-1">follows you</span>' : ""; ?>

											<?= !is_null($user->getBio()) ? '<p class="mb-0 mt-2 convertEmoji" style="word-wrap: break-word;">' . Util::convertPost($user->getBio()) . '</p>' : ""; ?>
										</div>
									</div>

									<p class="my-2 text-muted">
										<?php

										$date = strtotime($user->getTime());

										?><i class="fas fa-globe"></i><span
												style="margin-left: 5px">Joined <?= date("F Y", $date); ?></span>
										<?= !is_null($user->getBirthday()) ? '<i class="fas fa-birthday-cake ml-3"></i><span style="margin-left: 7px">' . date("F jS Y", strtotime($user->getBirthday())) . '</span>' : "" ?>
									</p>

									<?= Util::followButton($user->getId(), true, [], false) ?>

									<?php

									if (Util::isLoggedIn() && !is_null($currentUser) && $currentUser->getId() != $user->getId()) {
										if ($currentUser->hasBlocked($user)) {
											?>
											<form action="/<?= $user->getUsername(); ?>" method="post">
												<?= Util::insertCSRFToken(); ?>
												<input type="hidden" name="action" value="unblock"/>

												<button type="submit" class="btn btn-light">
													Unblock
												</button>
											</form>
											<?php
										} else {
											?>
											<button type="button" class="btn btn-light" data-toggle="modal"
													data-target="#blockModal">
												Block
											</button>
											<?php
										}
									}
									?>
								</div>
								<?php

								echo Util::renderAd(Util::AD_TYPE_LEADERBOARD, true, ["mt-3", "d-lg-none"]);

								?>
							</div>
						</div>

						<div class="col-lg-8 col-xl-6">
							<nav class="nav nav-pills nav-justified">
								<a class="nav-item nav-link<?php if (isset($profileTab) && $profileTab == PROFILE_TAB_FEED) echo " active"; ?>"
								   href="<?= $app->routeUrl("/" . $user->getUsername()); ?>">Feed
									(<?= Util::formatNumberShort($user->getPosts()); ?>)</a>
								<a class="nav-item nav-link<?php if (isset($profileTab) && $profileTab == PROFILE_TAB_FOLLOWING) echo " active"; ?>"
								   href="<?= $app->routeUrl("/" . $user->getUsername() . "/following"); ?>">Following
									(<?= Util::formatNumberShort($user->getFollowing()); ?>)</a>
								<a class="nav-item nav-link<?php if (isset($profileTab) && $profileTab == PROFILE_TAB_FOLLOWERS) echo " active"; ?>"
								   href="<?= $app->routeUrl("/" . $user->getUsername() . "/followers"); ?>">Followers
									(<?= Util::formatNumberShort($user->getFollowers()); ?>)</a>
							</nav>
							<?= $content_for_layout ?>
						</div>

						<div class="col-xl-3 d-none d-xl-block">
							<div class="sticky-top" style="top: 70px">
								<?php

								$followersYouFollow = $user->followersYouFollow();
								if (!is_null($followersYouFollow) && count($followersYouFollow) > 0) {
									?>
									<i class="far fa-user text-muted"></i> <?= count($followersYouFollow) ?> follower<?= count($followersYouFollow) > 1 ? "s" : "" ?> you know

									<div class="d-inline-block ml-1">
										<?php

										for ($i = 0; $i < min(count($followersYouFollow), 24); $i++) {
											$follower = $followersYouFollow[$i];

											echo '<div class="float-left mt-1 mr-1">';
											echo '<a href="/' . $follower->getUsername() . '" class="clearUnderline">';
											echo '<img src="' . $follower->getAvatarURL() . '" class="rounded" width="56" height="56" data-toggle="tooltip" title="' . $follower->getDisplayName() . ' (@' . $follower->getUsername() . ')"/>';
											echo '</a>';
											echo '</div>';
										}

										?>
									</div>
									<?php
								}

								if (!is_null($user->getFeaturedBoxContent()) && count($user->getFeaturedBoxContent()) > 0) {
									$boxTitle = is_null($user->getFeaturedBoxTitle()) ? "Featured" : $user->getFeaturedBoxTitle();

									?>
									<h5 class="mt-2 mb-0 convertEmoji"
										style="word-wrap: break-word;"><?= Util::sanatizeString($boxTitle) ?></h5>
									<?php

									foreach ($user->getFeaturedBoxContent() as $uid) {
										$featuredUser = User::getUserById($uid);

										if (is_null($featuredUser)) continue;

										?>
										<div class="my-2">
											<a href="/<?= $featuredUser->getUsername() ?>" class="clearUnderline">
												<div class="card">
													<div class="px-2 py-2">
														<img src="<?= $featuredUser->getAvatarURL() ?>" width="48"
															 height="48" class="float-left rounded mr-2"/>
														<div class="float-left">
															<b class="float-left"
															   style="overflow: hidden !important; text-overflow: ellipsis !important; white-space: nowrap !important; word-wrap: normal !important; width: 150px !important;"><?= $featuredUser->getDisplayName() . $featuredUser->renderCheckMark() ?></b>
															<div class="small text-muted">
																@<?= $featuredUser->getUsername() ?></div>
														</div>
													</div>
												</div>
											</a>
										</div>
										<?php
									}
								}

								?>
							</div>
						</div>
					</div>
				</div>
				<?php
			} else if (isset($showAccountNav) && $showAccountNav == true) {
				?>
				<div class="legacyCardBody">
					<div class="row">
						<div class="col-lg-3 mb-3">
							<ul class="nav nav-pills flex-column">
								<li class="nav-item"><a
											class="nav-link<?php if (isset($accountNav) && $accountNav == ACCOUNT_NAV_HOME) echo ' active'; ?>"
											href="/account">Account</a></li>
								<li class="nav-item"><a
											class="nav-link<?php if (isset($accountNav) && $accountNav == ACCOUNT_NAV_PRIVACY) echo ' active'; ?>"
											href="/account/privacy">Privacy</a></li>
								<li class="nav-item"><a
											class="nav-link<?php if (isset($accountNav) && $accountNav == ACCOUNT_NAV_SESSIONS) echo ' active'; ?>"
											href="/account/sessions">Active sessions</a></li>
								<?php if (!$currentUser->isGigadriveLinked()) { ?>
									<li class="nav-item"><a
											class="nav-link<?php if (isset($accountNav) && $accountNav == ACCOUNT_NAV_CHANGE_PASSWORD) echo ' active'; ?>"
											href="/account/change-password">Change password</a></li><?php } ?>
								<li class="nav-item"><a
											class="nav-link<?php if (isset($accountNav) && $accountNav == ACCOUNT_NAV_LOGOUT) echo ' active'; ?>"
											href="/logout" data-no-instant>Logout</a></li>
							</ul>

							<?= Util::renderAd(Util::AD_TYPE_VERTICAL, true, ["my-3"]) ?>
						</div>

						<div class="col-lg-9">
							<?= $content_for_layout ?>
						</div>
					</div>
				</div>
				<?php
			} else {
				echo $content_for_layout;
			}
			?>
		</div>
	</div>

	<?php if (!isset($hideFooter) || $hideFooter == false) { ?>
		<hr class="mt-2"/>

		<footer class="small text-muted my-2">
			&copy; Copyright <?= date("Y"); ?> Gigadrive Group - All rights reserved.

			<div class="float-right">
				<a href="https://gigadrivegroup.com/legal/contact" target="_blank">
					Contact Info
				</a>

				&bull;

				<a href="https://gigadrivegroup.com/legal/terms-of-service" target="_blank">
					Terms of Service
				</a>

				&bull;

				<a href="https://gigadrivegroup.com/legal/privacy" target="_blank">
					Privacy Policy
				</a>

				&bull;

				<a href="https://gigadrivegroup.com/legal/disclaimer" target="_blank">
					Disclaimer
				</a>
			</div>
		</footer>
	<?php } ?>
</div>

</body>
</html>
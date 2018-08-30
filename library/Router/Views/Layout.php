<?php

if(isset($title) && !empty($title)){
	$title = $title . " - " . $app["config.site"]["name"];
} else {
	$title = $app["config.site"]["name"];
}

$originalTitle = $title;

if(!isset($description) || is_null($description) || empty($description))
	$description = DEFAULT_DESCRIPTION;

if(!isset($socialImage) || is_null($socialImage) || empty($socialImage))
	$socialImage = DEFAULT_TWITTER_IMAGE;
	
if(isset($_SESSION["profileLoadPost"])){
	$post = FeedEntry::getEntryById($_SESSION["profileLoadPost"]);

	if(!is_null($post)){
		if(!is_null($post->getText()) && !empty(trim($post->getText()))){
			$title = $post->getUser()->getDisplayName() . " on qpost: \"" . Util::limitString($post->getText(),34,true) . "\"";
			$description = Util::limitString($post->getText(),150,true);
			$socialImage = $post->getUser()->getAvatarURL();
		}
	}
}

?><!DOCTYPE html>
<html lang="en">
	<head>
		<title><?= Util::sanatizeHTMLAttribute($title) ?></title>

		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
		<meta http-equiv="x-ua-compatible" content="ie=edge"/>
		<meta name="apple-mobile-web-app-capable" content="yes">

		<meta name="og:site_name" content="<?= Util::sanatizeHTMLAttribute($app["config.site"]["name"]) ?>" />
		<meta name="og:title" content="<?= Util::sanatizeHTMLAttribute($title) ?>" />
		<meta name="og:description" content="<?= Util::sanatizeHTMLAttribute($description) ?>" />

		<meta name="twitter:title" content="<?= Util::sanatizeHTMLAttribute($title) ?>" />
		<meta name="twitter:description" content="<?= Util::sanatizeHTMLAttribute($description) ?>" />
		<meta name="twitter:image" content="<?= Util::sanatizeHTMLAttribute($socialImage) ?>" />
		<meta name="twitter:card" content="summary" />

		<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
		<link rel="manifest" href="/site.webmanifest">
		<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#007bff">
		<meta name="apple-mobile-web-app-title" content="qpost">
		<meta name="application-name" content="qpost">
		<meta name="msapplication-TileColor" content="#007bff">
		<meta name="theme-color" content="#007bff">

		<meta name="description" content="<?= Util::sanatizeHTMLAttribute($description) ?>" />
		<meta name="keywords" content="social,network,posts,profiles,pictures,bio,gigadrive" />

		<noscript><meta http-equiv="refresh" content="0; URL=https://gigadrivegroup.com/badbrowser"></noscript>

		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous">

		<?= $app->style([
			"assets:css/bootstrap.min.css",
			"https://cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.0.3/cookieconsent.min.css",
			"assets:css/main.css",
			"assets:css/datepicker.min.css",
			"assets:css/jquery.highlight-within-textarea.css"]); ?>

		<?php

		if(Util::isUsingNightMode())
			echo $app->style("assets:css/nightmode.css");

		?>

		<?= $app->script([
			"https://code.jquery.com/jquery-latest.min.js",
			"https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js",
			"assets:js/bootstrap.min.js",
			"assets:js/jquery.timeago.js",
			"assets:js/app.js",
			"assets:js/datepicker.min.js",
			"https://www.google.com/recaptcha/api.js",
			"https://cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.0.3/cookieconsent.min.js",
			"https://twemoji.maxcdn.com/2/twemoji.min.js?11.0",
			"assets:js/dropzone.js",
			"assets:js/jquery.highlight-within-textarea.js"]); ?>

		<script>var CSRF_TOKEN = "<?= Util::sanatizeHTMLAttribute(CSRF_TOKEN) ?>";var POST_CHARACTER_LIMIT = <?= POST_CHARACTER_LIMIT ?>;<?= Util::isLoggedIn() ? 'var CURRENT_USER = ' . Util::getCurrentUser()->getId() . ';' : ""; ?>var restoreUrl = "<?= isset($_SESSION["profileLoadPost"]) ? "/" . FeedEntry::getEntryById($_SESSION["profileLoadPost"])->getUser()->getUsername() : "" ?>";var restoreTitle = "<?= isset($_SESSION["profileLoadPost"]) ? $originalTitle : "" ?>";var CURRENT_STATUS_MODAL = 0;</script><?php unset($_SESSION["profileLoadPost"]); ?>
	</head>
	<body>
		<nav id="mainNav" class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
			<div class="container-fluid container">
				<div class="navbar-header">
					<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-navigation" aria-controls="main-navigation" aria-expanded="false" aria-label="Toggle navigation" style="">
						<span class="navbar-toggler-icon"></span>
					</button>

					<a class="navbar-brand" href="/"<?= !Util::isLoggedIn() ? " data-no-instant" : "" ?>>
						<img src="/assets/img/navlogo.png" style="height: 30px"/>
					</a>
				</div>

				<div class="collapse navbar-collapse" id="main-navigation">
					<ul class="nav navbar-nav ml-auto">
						<?php

						if(Util::isLoggedIn()){
							$unreadMessages = Util::getCurrentUser()->getUnreadMessages();
							$unreadNotifications = Util::getCurrentUser()->getUnreadNotifications();

							?>
							<li class="nav-item<?= (isset($nav) && $nav == NAV_HOME) ? " active" : ""; ?>">
								<a href="/" class="nav-link">
									home
								</a>
							</li>

							<li class="nav-item<?= (isset($nav) && $nav == NAV_PROFILE) ? " active" : ""; ?>">
								<a href="/<?= Util::getCurrentUser()->getUsername(); ?>" class="nav-link">
									my profile
								</a>
							</li>

							<li class="nav-item<?= (isset($nav) && $nav == NAV_NOTIFICATIONS) ? " active" : ""; ?>">
								<a href="/notifications" class="nav-link notificationTabMainNav" data-no-instant>
									notifications<?= !is_null($unreadNotifications) && $unreadNotifications > 0 ? " <b>(" . $unreadNotifications . ")</b>" : "</b>"; ?>
								</a>
							</li>

							<!--<li class="nav-item<?= (isset($nav) && $nav == NAV_MESSAGES) ? " active" : ""; ?>">
								<a href="/messages" class="nav-link">
									messages<?= !is_null($unreadMessages) && $unreadMessages > 0 ? " <b>(" . $unreadMessages . ")</b>" : "</b>"; ?>
								</a>
							</li>-->

							<li class="nav-item dropdown<?= (isset($nav) && $nav == NAV_ACCOUNT) ? " active" : ""; ?>">
								<a href="#" class="nav-link dropdown-toggle" id="accountDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<img src="<?= Util::getCurrentUser()->getAvatarUrl() ?>" width="24" height="24" class="rounded border border-white"/>
								</a>

								<div class="dropdown-menu dropdown-menu-right shadow" aria-labelledBy="accountDropdown">
									<a href="/<?= Util::getCurrentUser()->getUsername() ?>" class="dropdown-item">
										<div class="font-weight-bold" style="font-size: 21px">
											<?= Util::getCurrentUser()->getDisplayName() ?>
										</div>
										<div class="text-muted" style="margin-top: -8px">
											@<?= Util::getCurrentUser()->getUsername() ?>
										</div>
									</a>

									<div class="dropdown-divider"></div>

									<a href="/<?= Util::getCurrentUser()->getUsername() ?>" class="dropdown-item"><i class="far fa-user"></i> Profile</a>
									<a href="/notifications" class="dropdown-item" data-no-instant><i class="far fa-bell"></i> Notifications</a>
									<a href="/messages" class="dropdown-item"><i class="far fa-envelope"></i> Messages</a>

									<div class="dropdown-divider"></div>

									<a href="/account" class="dropdown-item">Settings and privacy</a>
									<a href="/logout" class="dropdown-item" data-no-instant>Log out</a>

									<div class="dropdown-divider"></div>

									<a href="/nightmode" class="dropdown-item" data-no-instant>Night mode <span style="margin-top: -21px" class="float-right badge badge-<?= Util::isUsingNightMode() ? "success" : "danger" ?>"><?= Util::isUsingNightMode() ? "On" : "Off" ?></span></a>
								</div>
							</li>
							<?php
						}

						?>
					</ul>

					<ul class="nav navbar-nav navbar-right">
						<?php

						if(Util::isLoggedIn()){
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

		<div class="container">
			<div class="legacyCard">
				<?php if(Util::isLoggedIn()){ ?>
				<div class="d-none notificationPermissionAlert py-2 mb-3">
					<div class="container">
						<div class="float-left mt-1">
							Do you want to receive desktop notifications to keep up to date with the people you follow?
						</div>

						<div class="float-right">
							<button class="btn btn-primary btn-sm enableNotifications">Yes</button>
							<button class="btn btn-light btn-sm hideNotifications" data-toggle="tooltip" title="Hide this alert for 7 days">No</button>
						</div>
					</div>
				</div>
				<?php } ?>

				<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-hidden="true"></div>
				<div class="modal fade" id="mediaModal" tabindex="-1" role="dialog" aria-hidden="true"></div>
				<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true"></div>
				<span class="dz-message"></span>
				
				<div class="preview-template d-none">
					<div class="dz-preview dz-file-preview well mt-2 mr-2 float-left" id="dz-preview-template">
						<img data-dz-thumbnail width="100" height="100" class="rounded border border-primary bg-dark"/>
						<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
						<div class="dz-success-mark"><span></span></div>
						<div class="dz-error-mark"><span></span></div>
						<div class="dz-error-message"><span data-dz-errormessage></span></div>
					</div>
				</div>

				<div class="wrapper"><?php

				if(isset($showProfile) && $showProfile == true && isset($user)){
					?>
				<div class="legacyCardBody">
					<?php

						if(Util::isLoggedIn()){
							if(isset($_POST["action"]) && $_POST["action"] == "block"){
								Util::getCurrentUser()->block($user);
							} else if(isset($_POST["action"]) && $_POST["action"] == "unblock"){
								Util::getCurrentUser()->unblock($user);
							}
						}


						if(Util::isLoggedIn() && Util::getCurrentUser()->hasBlocked($user)){
							echo Util::createAlert("blocking","<b>You blocked @" . $user->getUsername() . "</b><br/>@" . $user->getUsername() . " won't be able to view your profile or posts.",ALERT_TYPE_DANGER);
						}

					?>
					<div class="row">
						<div class="col-lg-3 mb-3">
							<div class="sticky-top" style="top: 70px">
								<center><img class="rounded border-primary mb-2 border border-primary" src="<?= $user->getAvatarURL(); ?>" width="200" height="200"/></center>
								<h4 class="mb-0 convertEmoji"><?= $user->getDisplayName(); ?></h4>
								<span class="text-muted" style="font-size: 16px">@<?= $user->getUsername(); ?></span> <?= Util::isLoggedIn() && $user->isFollowing(Util::getCurrentUser()) ? '<span class="text-uppercase small followsYouBadge px-1 py-1">follows you</span>' : ""; ?>

								<?= !is_null($user->getBio()) ? '<p class="mb-0 mt-2 convertEmoji">' . Util::convertPost($user->getBio()) . '</p>' : ""; ?>

								<p class="my-2 text-muted">
									<?php

										$date = strtotime($user->getTime());
										if(!is_null($user->getGigadriveRegistrationDate())){
											$date = strtotime($user->getGigadriveRegistrationDate());
										}

									?><i class="fas fa-globe"></i><span style="margin-left: 5px">Joined <?= date("F Y",$date); ?></span>
									<?= !is_null($user->getBirthday()) ? '<br/><i class="fas fa-birthday-cake"></i><span style="margin-left: 7px">' . date("F jS Y",strtotime($user->getBirthday())) . '</span>' : "" ?>
								</p>

								<?= Util::followButton($user->getId(),true,["btn-block","mt-2"],false) ?>

								<?php

									if(Util::isLoggedIn() && Util::getCurrentUser()->getId() != $user->getId()){
										if(Util::getCurrentUser()->hasBlocked($user)){
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
								<button type="button" class="btn btn-light btn-block mt-2" data-toggle="modal" data-target="#blockModal">
									Block
								</button>

								<div class="modal fade" id="blockModal" tabindex="-1" role="dialog" aria-labelledby="blockModalLabel" aria-hidden="true">
									<div class="modal-dialog modal-dialog-centered" role="document">
										<div class="modal-content">
											<div class="modal-header">
												<h5 class="modal-title" id="blockModalLabel">Block @<?= $user->getUsername(); ?></h5>

												<button type="button" class="close" data-dismiss="modal" aria-label="Close">
													<span aria-hidden="true">&times;</span>
												</button>
											</div>

											<div class="modal-body">
												@<?= $user->getUsername(); ?> will no longer be able to follow or message you, and you will not see notifications from @<?= $user->getUsername(); ?>.
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
											<?php
										}
									}

									if(!is_null($user->getFeaturedBoxContent()) && count($user->getFeaturedBoxContent()) > 0){
										$boxTitle = is_null($user->getFeaturedBoxTitle()) ? "Featured" : $user->getFeaturedBoxTitle();

										?>
								<h5 class="mt-4 mb-0 convertEmoji"><?= Util::sanatizeString($boxTitle) ?></h5>
										<?php

										foreach($user->getFeaturedBoxContent() as $uid){
											$featuredUser = User::getUserById($uid);

											if(is_null($featuredUser)) continue;

											?>
								<div class="my-2">
									<a href="/<?= $featuredUser->getUsername() ?>" class="clearUnderline">
										<div class="card">
											<div class="card-body">
												<img src="<?= $featuredUser->getAvatarURL() ?>" width="48" height="48" class="float-left rounded mr-2"/>

												<div class="mt-1">
													<b><?= $featuredUser->getDisplayName() ?></b>
													<div class="small text-muted">@<?= $featuredUser->getUsername() ?></div>
												</div>
											</div>
										</div>
									</a>
								</div>
											<?php
										}
									}

									echo Util::renderAd(Util::AD_TYPE_VERTICAL,true,["my-3"]);

								?>
							</div>
						</div>

						<div class="col-lg-9">
							<nav class="nav nav-pills nav-justified">
								<a class="nav-item nav-link<?php if(isset($profileTab) && $profileTab == PROFILE_TAB_FEED) echo " active"; ?>" href="<?= $app->routeUrl("/" . $user->getUsername()); ?>">Feed (<?= $user->getPosts(); ?>)</a>
								<a class="nav-item nav-link<?php if(isset($profileTab) && $profileTab == PROFILE_TAB_FOLLOWING) echo " active"; ?>" href="<?= $app->routeUrl("/" . $user->getUsername() . "/following"); ?>">Following (<?= $user->getFollowing(); ?>)</a>
								<a class="nav-item nav-link<?php if(isset($profileTab) && $profileTab == PROFILE_TAB_FOLLOWERS) echo " active"; ?>" href="<?= $app->routeUrl("/" . $user->getUsername() . "/followers"); ?>">Followers (<?= $user->getFollowers(); ?>)</a>
							</nav>
							<?= $content_for_layout ?>
						</div>
					</div>
				</div>
					<?php
				} else if(isset($showAccountNav) && $showAccountNav == true){
					?>
				<div class="legacyCardBody">
					<div class="row">
						<div class="col-lg-3 mb-3">
							<ul class="nav nav-pills flex-column">
								<li class="nav-item"><a class="nav-link<?php if(isset($accountNav) && $accountNav == ACCOUNT_NAV_HOME) echo ' active'; ?>" href="/account">Account</a></li>
								<li class="nav-item"><a class="nav-link<?php if(isset($accountNav) && $accountNav == ACCOUNT_NAV_PRIVACY) echo ' active'; ?>" href="/account/privacy">Privacy</a></li>
								<li class="nav-item"><a class="nav-link<?php if(isset($accountNav) && $accountNav == ACCOUNT_NAV_SESSIONS) echo ' active'; ?>" href="/account/sessions">Active sessions</a></li>
								<?php if(!Util::getCurrentUser()->isGigadriveLinked()){ ?><li class="nav-item"><a class="nav-link<?php if(isset($accountNav) && $accountNav == ACCOUNT_NAV_CHANGE_PASSWORD) echo ' active'; ?>" href="/account/change-password">Change password</a></li><?php } ?>
								<li class="nav-item"><a class="nav-link<?php if(isset($accountNav) && $accountNav == ACCOUNT_NAV_LOGOUT) echo ' active'; ?>" href="/logout" data-no-instant>Logout</a></li>
							</ul>

							<?= Util::renderAd(Util::AD_TYPE_VERTICAL,true,["my-3"]) ?>
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

			<?php if(!isset($hideFooter) || $hideFooter == false){ ?>
			<hr class="mt-2"/>

			<footer class="small text-muted my-2">
				&copy; Copyright <?= date("Y"); ?> Gigadrive Group - All rights reserved.

				<div class="float-right">
					<a href="https://gigadrivegroup.com/legal/contact" target="_blank">
						Contact Info
					</a>

					&bull;

					<a href="https://gigadrivegroup.com/legal/tos" target="_blank">
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

		<!--<script src="<?= $app->baseUrl("/assets/js/instantclick.min.js"); ?>" data-no-instant></script>
		<script data-no-instant>loadBasic();InstantClick.init();InstantClick.on("change",function(){loadBasic();loadNotificationAlert();loadDropzone();});</script>-->
		<script>if($(".convertEmoji").length){$(".convertEmoji").html(function(){return twemoji.parse($(this).html());});}</script>
	</body>
</html>
<?php

if(isset($title) && !empty($title)){
	$title = $title . " - " . $app["config.site"]["name"];
} else {
	$title = $app["config.site"]["name"];
}

if(!isset($description) || empty($description))
	$description = DEFAULT_DESCRIPTION;

if(!isset($socialImage) || empty($socialImage))
	$socialImage = DEFAULT_TWITTER_IMAGE;

?><!DOCTYPE html>
<html lang="en">
	<head>
		<title><?= $title ?></title>

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
		<meta name="twitter:site" content="@mcskinhistory" />

		<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
		<link rel="manifest" href="/site.webmanifest">
		<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
		<meta name="msapplication-TileColor" content="#007BFF">
		<meta name="theme-color" content="#007BFF">

		<meta name="description" content="<?= Util::sanatizeHTMLAttribute($description) ?>" />
		<meta name="keywords" content="social,network,posts,profiles,pictures,bio,gigadrive" />

		<noscript><meta http-equiv="refresh" content="0; URL=https://gigadrivegroup.com/badbrowser"></noscript>

		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.10/css/all.css" integrity="sha384-+d0P83n9kaQMCwj8F4RJB66tzIwOKmrdb46+porD/OvrJ+37WqIM7UoBtwHO6Nlg" crossorigin="anonymous">

		<?= $app->style([
			"assets:css/bootstrap.min.css",
			"https://cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.0.3/cookieconsent.min.css",
			"assets:css/main.css",
			"assets:css/twemoji-picker.css"]); ?>

		<?= $app->script([
			"https://code.jquery.com/jquery-latest.min.js",
			"https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js",
			"assets:js/bootstrap.min.js",
			"assets:js/jquery.timeago.js",
			"assets:js/app.js",
			"https://www.google.com/recaptcha/api.js",
			"https://cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.0.3/cookieconsent.min.js",
			"https://twemoji.maxcdn.com/2/twemoji.min.js?11.0",
			"assets:js/twemoji-picker.js"]); ?>

		<script>var CSRF_TOKEN = "<?= Util::sanatizeHTMLAttribute(CSRF_TOKEN) ?>";var POST_CHARACTER_LIMIT = <?= POST_CHARACTER_LIMIT ?>;<?= Util::isLoggedIn() ? 'var CURRENT_USER = ' . Util::getCurrentUser()->getId() . ';' : ""; ?>var restoreUrl = "";var restoreTitle = "";</script>
	</head>
	<body>
		<nav id="mainNavSmall" class="d-xs-block d-lg-none navbar navbar-expand-lg navbar-dark bg-primary">
			<div class="container-fluid container">
				<div class="navbar-header">
					<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-navigation-small" aria-controls="main-navigation-small" aria-expanded="false" aria-label="Toggle navigation" style="">
						<span class="navbar-toggler-icon"></span>
					</button>

					<a class="navbar-brand" href="/">
						<img src="/assets/img/navlogo.png" style="height: 30px"/>
					</a>
				</div>

				<div class="collapse navbar-collapse" id="main-navigation-small">
					<ul class="nav navbar-nav mr-auto">
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
								<a href="/notifications" class="nav-link" data-no-instant>
									notifications<?= !is_null($unreadNotifications) && $unreadNotifications > 0 ? " <b>(" . $unreadNotifications . ")</b>" : "</b>"; ?>
								</a>
							</li>

							<li class="nav-item<?= (isset($nav) && $nav == NAV_MESSAGES) ? " active" : ""; ?>">
								<a href="/messages" class="nav-link">
									messages<?= !is_null($unreadMessages) && $unreadMessages > 0 ? " <b>(" . $unreadMessages . ")</b>" : "</b>"; ?>
								</a>
							</li>

							<li class="nav-item<?= (isset($nav) && $nav == NAV_ACCOUNT) ? " active" : ""; ?>">
								<a href="/account" class="nav-link">
									account
								</a>
							</li>

							<li class="nav-item">
								<a href="/logout" class="nav-link" data-no-instant>
									log out
								</a>
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
			<div class="card rounded my-3">
				<nav id="mainNav" class="rounded-top d-none d-lg-block navbar navbar-expand-lg navbar-dark bg-primary">
					<div class="container-fluid container">
						<div class="navbar-header">
							<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-navigation" aria-controls="main-navigation" aria-expanded="false" aria-label="Toggle navigation" style="">
								<span class="navbar-toggler-icon"></span>
							</button>

							<a class="navbar-brand" href="/">
								<img src="/assets/img/navlogo.png" style="height: 30px"/>
							</a>
						</div>

						<div class="collapse navbar-collapse" id="main-navigation">
							<ul class="nav navbar-nav mr-auto">
								<?php

								if(Util::isLoggedIn()){
									?>
									<?php
								}

								?>
							</ul>

							<ul class="nav navbar-nav navbar-right">
								<?php

								if(Util::isLoggedIn()){
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
									<a href="/notifications" class="nav-link" data-no-instant>
										notifications<?= !is_null($unreadNotifications) && $unreadNotifications > 0 ? " <b>(" . $unreadNotifications . ")</b>" : "</b>"; ?>
									</a>
								</li>

								<li class="nav-item<?= (isset($nav) && $nav == NAV_MESSAGES) ? " active" : ""; ?>">
									<a href="/messages" class="nav-link">
										messages<?= !is_null($unreadMessages) && $unreadMessages > 0 ? " <b>(" . $unreadMessages . ")</b>" : "</b>"; ?>
									</a>
								</li>

								<li class="nav-item<?= (isset($nav) && $nav == NAV_ACCOUNT) ? " active" : ""; ?>">
									<a href="/account" class="nav-link">
										account
									</a>
								</li>

								<li class="nav-item">
									<a href="/logout" class="nav-link" data-no-instant>
										log out
									</a>
								</li>
									<?php
								} else {
									if($app->route != "/"){
									?>
								<li class="nav-item<?= (isset($nav) && $nav == NAV_ACCOUNT) ? " active" : ""; ?>">
									<a href="/login" class="nav-link" data-no-instant>
										log in
									</a>
								</li>
									<?php
									}
								}

								?>
							</ul>
						</div>
					</div>
				</nav>

				<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-hidden="true"></div>

				<?php

				if(isset($subtitle)){
					?>
				<div class="pageSubtitle">
					<?= $subtitle; ?>
				</div>
					<?php
				}

				if(isset($showProfile) && $showProfile == true && isset($user)){
					?>
				<div class="card-body">
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
							<center><img class="rounded border-primary mb-2" src="<?= $user->getAvatarURL(); ?>" width="200" height="200"/></center>
							<h4 class="mb-0"><?= $user->getDisplayName(); ?></h4>
							<span class="text-muted" style="font-size: 16px">@<?= $user->getUsername(); ?></span> <?= Util::isLoggedIn() && $user->isFollowing(Util::getCurrentUser()) ? '<span class="text-uppercase small bg-light text-muted px-1 py-1">follows you</span>' : ""; ?>

							<?= !is_null($user->getBio()) ? '<p class="mb-0 mt-2 convertEmoji">' . Util::convertPost($user->getBio()) . '</p>' : ""; ?>

							<p class="my-2 text-muted">
								<i class="fas fa-globe"></i> Joined <?= date("F Y",strtotime($user->getTime())); ?>
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

								echo Util::renderAd(Util::AD_TYPE_BLOCK,true,["my-3"]);

							?>
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
				<div class="card-body">
					<div class="row">
						<div class="col-lg-3 mb-3">
							<ul class="nav nav-pills flex-column">
								<li class="nav-item"><a class="nav-link<?php if(isset($accountNav) && $accountNav == ACCOUNT_NAV_HOME) echo ' active'; ?>" href="/account">Account</a></li>
								<li class="nav-item"><a class="nav-link<?php if(isset($accountNav) && $accountNav == ACCOUNT_NAV_PRIVACY) echo ' active'; ?>" href="/account/privacy">Privacy</a></li>
								<li class="nav-item"><a class="nav-link<?php if(isset($accountNav) && $accountNav == ACCOUNT_NAV_LOGOUT) echo ' active'; ?>" href="/logout" data-no-instant>Logout</a></li>
							</ul>

							<?= Util::renderAd(Util::AD_TYPE_BLOCK,true,["my-3"]) ?>
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

			<footer class="small text-muted">
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
		</div>

		<script src="<?= $app->baseUrl("/assets/js/instantclick.min.js"); ?>" data-no-instant></script>
		<script data-no-instant>loadBasic();InstantClick.init();InstantClick.on("change",function(){loadBasic();});</script>
		<script>if($(".convertEmoji").length){$(".convertEmoji").html(function(){return twemoji.parse($(this).html());});}</script>
	</body>
</html>
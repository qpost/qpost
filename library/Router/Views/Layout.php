<?php

if(isset($title) && !empty($title)){
	$title = $title . " - " . $app["config.site"]["name"];
} else {
	$title = $app["config.site"]["name"];
}

?><!DOCTYPE html>
<html lang="en">
	<head>
		<title><?= $title ?></title>

		<noscript><meta http-equiv="refresh" content="0; URL=https://gigadrivegroup.com/badbrowser"></noscript>

		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.10/css/all.css" integrity="sha384-+d0P83n9kaQMCwj8F4RJB66tzIwOKmrdb46+porD/OvrJ+37WqIM7UoBtwHO6Nlg" crossorigin="anonymous">

		<?= $app->style([
			"assets:css/bootstrap.min.css",
			"https://cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.0.3/cookieconsent.min.css",
			"assets:css/main.css"
			]); ?>

		<?= $app->script([
			"https://code.jquery.com/jquery-latest.min.js",
			"assets:js/bootstrap.min.js",
			"assets:js/jquery.timeago.js",
			"assets:js/app.js",
			"https://www.google.com/recaptcha/api.js"]); ?>

		<script>loadCookieConsent();var CSRF_TOKEN = "<?= htmlspecialchars(CSRF_TOKEN, ENT_QUOTES | ENT_HTML5, "UTF-8") ?>";</script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
	</head>
	<body>
		<nav id="mainNavSmall" class="d-xs-block d-lg-none navbar navbar-expand-lg navbar-<?= Util::isUsingNightMode() ? "dark bg-dark" : "dark bg-primary" ?>">
			<div class="container-fluid container">
				<div class="navbar-header">
					<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-navigation-small" aria-controls="main-navigation-small" aria-expanded="false" aria-label="Toggle navigation" style="">
						<span class="navbar-toggler-icon"></span>
					</button>

					<a class="navbar-brand" href="/">
						twitterClone
					</a>
				</div>

				<div class="collapse navbar-collapse" id="main-navigation-small">
					<ul class="nav navbar-nav mr-auto">
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
								<a href="/notifications" class="nav-link">
									notifications
								</a>
							</li>

							<li class="nav-item<?= (isset($nav) && $nav == NAV_MESSAGES) ? " active" : ""; ?>">
								<a href="/messages" class="nav-link">
									messages
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
								<i class="fas fa-sign-in-alt"></i> Sign In
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
				<nav id="mainNav" class="rounded-top d-none d-lg-block navbar navbar-expand-lg navbar-<?= Util::isUsingNightMode() ? "dark bg-dark" : "dark bg-primary" ?>">
					<div class="container-fluid container">
						<div class="navbar-header">
							<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-navigation" aria-controls="main-navigation" aria-expanded="false" aria-label="Toggle navigation" style="">
								<span class="navbar-toggler-icon"></span>
							</button>

							<a class="navbar-brand" href="/">
								twitterClone
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
									<a href="/notifications" class="nav-link">
										notifications
									</a>
								</li>

								<li class="nav-item<?= (isset($nav) && $nav == NAV_MESSAGES) ? " active" : ""; ?>">
									<a href="/messages" class="nav-link">
										messages
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
									<a href="/login" class="nav-link">
										<i class="fas fa-sign-in-alt"></i> Sign In
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
					<div class="row">
						<div class="col-lg-3 mb-3">
							<center><img class="rounded border-primary mb-2" src="<?= $user->getAvatarURL(); ?>" width="200" height="200"/></center>
							<h4 class="mb-0"><?= $user->getDisplayName(); ?></h4>
							<p class="text-muted my-0" style="font-size: 16px">@<?= $user->getUsername(); ?></p>

							<?= !is_null($user->getBio()) ? '<p class="mb-0 mt-2">' . Util::convertLineBreaksToHTML($user->getBio()) . '</p>' : ""; ?>

							<?= Util::followButton($user->getId(),true,["btn-block","mt-2"]) ?>
						</div>

						<div class="col-lg-9">
							<nav class="nav nav-pills nav-justified">
								<a class="nav-item nav-link<?php if(isset($profileTab) && $profileTab == PROFILE_TAB_POSTS) echo " active"; ?>" href="<?= $app->routeUrl("/" . $user->getUsername()); ?>">Posts (<?= $user->getPosts(); ?>)</a>
								<a class="nav-item nav-link<?php if(isset($profileTab) && $profileTab == PROFILE_TAB_FOLLOWING) echo " active"; ?>" href="<?= $app->routeUrl("/" . $user->getUsername() . "/following"); ?>">Following (<?= $user->getFollowing(); ?>)</a>
								<a class="nav-item nav-link<?php if(isset($profileTab) && $profileTab == PROFILE_TAB_FOLLOWERS) echo " active"; ?>" href="<?= $app->routeUrl("/" . $user->getUsername() . "/followers"); ?>">Followers (<?= $user->getFollowers(); ?>)</a>
							</nav>
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
		<script data-no-instant>InstantClick.init();InstantClick.on("change",function(){load();});</script>
	</body>
</html>
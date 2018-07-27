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

		<?= $app->style([
			"assets:css/bootstrap.min.css",
			"https://cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.0.3/cookieconsent.min.css"
			]); ?>

		<?= $app->script([
			"https://code.jquery.com/jquery-latest.min.js",
			"assets:js/bootstrap.min.js",
			"assets:js/jquery.timeago.js",
			"assets:js/app.js",
			"https://www.google.com/recaptcha/api.js"]); ?>

		<script>loadCookieConsent();</script>
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

				<div class="card-body">
					<?= $content_for_layout ?>
				</div>
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
<?php

if(isset($title) && !Util::isEmpty($title)){
	$title = $title . " - " . $app["config.site"]["name"];
} else {
	$title = $app["config.site"]["name"];
}

if(!isset($description) || Util::isEmpty($description))
	$description = DEFAULT_DESCRIPTION;

if(!isset($socialImage) || Util::isEmpty($socialImage))
	$socialImage = DEFAULT_TWITTER_IMAGE;

?><!DOCTYPE html>
<html lang="en">
	<head>
		<title><?= $title; ?></title>

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

		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.10/css/all.css" integrity="sha384-+d0P83n9kaQMCwj8F4RJB66tzIwOKmrdb46+porD/OvrJ+37WqIM7UoBtwHO6Nlg" crossorigin="anonymous">

		<?= $app->style([
			"assets:css/bootstrap.min.css",
			"https://cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.0.3/cookieconsent.min.css",
			"assets:css/main.css",
			"assets:css/home.css",
			"assets:css/twemoji-picker.css",
			"https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css"]); ?>

		<?= $app->script([
			"https://code.jquery.com/jquery-latest.min.js",
			"https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js",
			"assets:js/bootstrap.min.js",
			"assets:js/jquery.timeago.js",
			"assets:js/app.js",
			"https://www.google.com/recaptcha/api.js",
			"https://cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.0.3/cookieconsent.min.js",
			"https://twemoji.maxcdn.com/2/twemoji.min.js?11.0",
			"assets:js/twemoji-picker.js",
			"https://code.jquery.com/ui/1.12.1/jquery-ui.js"]); ?>
	</head>
	<body>
		<div class="container container-fluid">
			<ul class="nav homeTopNav">
				<li class="nav-item ml-auto">
					<a class="nav-link" href="/">home</a>
				</li>

				<li class="nav-item">
					<a class="nav-link" href="/features">features</a>
				</li>
			</ul>
        </div>

		<div class="homeWrapper">
			<?= $content_for_layout; ?>
		</div>

        <div class="container container-fluid mb-4">
			<div class="text-center small text-muted text-uppercase font-weight-bold homeFooter">
				<a href="https://gigadrivegroup.com/legal/contact" target="_blank" class="clearUnderline mx-3">
					Contact Info
				</a>

				<a href="https://gigadrivegroup.com/legal/terms-of-service" target="_blank" class="clearUnderline mx-3">
					Terms of Service
				</a>

				<a href="https://gigadrivegroup.com/legal/privacy-policy" target="_blank" class="clearUnderline mx-3">
					Privacy Policy
				</a>

				<a href="https://gigadrivegroup.com/legal/disclaimer" target="_blank" class="clearUnderline mx-3">
					Disclaimer
				</a>

                <br/><br/>

                &copy; <?= date("Y") ?> Gigadrive Group
			</div>
		</div>

		<script src="<?= $app->baseUrl("/assets/js/instantclick.min.js"); ?>" data-no-instant></script>
		<script data-no-instant>loadBasic();InstantClick.init();InstantClick.on("change",function(){loadBasic();loadNotificationAlert();});</script>
		<script>if($(".convertEmoji").length){$(".convertEmoji").html(function(){return twemoji.parse($(this).html());});}</script>
	</body>
</html>
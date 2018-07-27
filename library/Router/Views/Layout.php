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
		<?= $content_for_layout ?>

		<script src="<?= $app->baseUrl("/assets/js/instantclick.min.js"); ?>" data-no-instant></script>
		<script data-no-instant>InstantClick.init();InstantClick.on("change",function(){load();});</script>
	</body>
</html>
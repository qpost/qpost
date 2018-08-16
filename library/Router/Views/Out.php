<div class="legacyCardBody text-center">
	<h4><?= $host; ?> is not part of this website!</h4>

	<p>
		Are you sure you want to leave <?= $app["config.site"]["name"] ?> and head to <?= $link ?>?<br/>
		<?= $app["config.site"]["name"] ?> is not responsible for any content you find when clicking on "Yes".
	</p>

	<a href="<?= $link; ?>" class="btn btn-primary">Yes</a>
	<a href="javascript:history.back()" class="btn btn-light">No</a>
</div>
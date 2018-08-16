<div class="legacyCardBody text-center">
	<img src="<?= $user->getAvatarURL(); ?>" width="150" height="150"/>

	<h4 class="mb-1"><?= $user->getDisplayName(); ?></h4>
	<h3 class="mb-0 text-muted small">@<?= $user->getUsername(); ?></h3>

	<p class="mt-3 mb-0">
		You are blocked from following @<?= $user->getUsername(); ?> and viewing @<?= $user->getUsername(); ?>'s profile.
	</p>
</div>
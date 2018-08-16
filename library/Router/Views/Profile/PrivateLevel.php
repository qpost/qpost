<div class="legacyCardBody text-center">
	<img src="<?= $user->getAvatarURL(); ?>" width="150" height="150"/>

	<h4 class="mb-1"><?= $user->getDisplayName(); ?></h4>
	<h3 class="mb-0 text-muted small">@<?= $user->getUsername(); ?></h3>

	<p class="mt-3 mb-0">
		This user has their privacy level set to <b>Private</b>, meaning to view their profile you have to be a follower.<br/>
		Your follow request has to be confirmed by the user.
	</p>

	<?= Util::followButton($user,false,["mt-4","btn-lg"]); ?>
</div>
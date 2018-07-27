<?php

$user = Util::getCurrentUser();

?><div class="card-body">
	<h4>Edit your profile</h4>

	<form action="<?= $app->routeUrl("/edit") ?>" method="post">
		<?= Util::insertCSRFToken(); ?>

		<fieldset>
			<div class="form-group row">
				<label for="displayName" class="control-label col-sm-2 col-form-label">Display name</label>

				<div class="col-sm-10 input-group mb-3">
					<input class="form-control" type="text" name="displayName" id="displayName" value="<?= $user->getUsername(); ?>"/>
				</div>
			</div>

			<div class="form-group row">
				<label for="username" class="control-label col-sm-2 col-form-label">Username</label>

				<div class="col-sm-10 input-group mb-3">
					<div class="input-group-prepend">
						<span class="input-group-text">@</span>
					</div>
					<input class="form-control disabled" disabled type="text" name="username" id="username" value="<?= $user->getUsername(); ?>"/>
				</div>
			</div>
		</fieldset>
	</form>

	<p class="mb-0 small">Some of your account data can only be changed on <a href="https://gigadrivegroup.com/account" target="_blank">gigadrivegroup.com</a>.</p>
</div>
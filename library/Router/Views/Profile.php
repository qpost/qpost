<div class="card-body">
	<div class="row">
		<div class="col-lg-3">
			<h4 class="mb-0"><?= $user->getUsername(); ?></h4>
			<p class="text-muted my-0" style="font-size: 16px">@<?= $user->getUsername(); ?></p>

			<?= !is_null($user->getBio()) ? '<p class="mb-0 mt-2">' . $user->getBio() . '</p>' : ""; ?>
		</div>

		<div class="col-lg-9">
			right
		</div>
	</div>
</div>
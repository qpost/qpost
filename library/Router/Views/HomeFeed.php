<div class="card-body">
	<div class="row">
		<div class="col-lg-8">
			<h4>Feed</h4>

			<div class="card mt-2 border-primary" style="background: #9FCCFC">
				<div class="card-body">
					<textarea class="form-control" id="homePostField" placeholder="Post something for your followers!"></textarea>

					<p class="mb-0 mt-2 float-left small">
						<?= POST_CHARACTER_LIMIT ?> characters left
					</p>

					<button type="button" class="btn btn-primary btn-sm float-right mb-0 mt-2">Post</button>
				</div>
			</div>
		</div>

		<div class="col-lg-4">
			<form action="<?= $app->routeUrl("/search"); ?>" method="get">
				<div class="input-group input-group-sm">
					<input class="form-control" name="query" placeholder="Search <?= $app["config.site"]["name"] ?>" type="text"/>

					<div class="input-group-append">
						<button class="btn btn-primary px-3" type="submit"><i class="fas fa-search"></i></button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
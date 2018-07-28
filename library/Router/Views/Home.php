<div class="card-body">
	<div class="row">
		<div class="col-lg-4">
			<div class="card">
				<div class="card-body">
					<h4 class="text-center">Log In</h4>
					<a href="<?= $app->routeUrl("/login"); ?>" class="btn btn-success btn-block mt-1" data-no-instant>Login with Gigadrive</a>
				</div>
			</div>
		</div>

		<div class="col-lg-8">
			<p class="mb-0">
				<b>Welcome to <?= $app["config.site"]["name"] ?>!</b>
				<br/><br/>
				<?= $app["config.site"]["name"] ?> is a social network that uses the Gigadrive account system. To sign in, please create an account on <b><a href="https://gigadrivegroup.com" target="_blank">gigadrivegroup.com</a></b> and click the green button on the left on this page to connect it.
			</p>
		</div>
	</div>
</div>
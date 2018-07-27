<div class="card-body">
	<div class="row">
		<div class="col-lg-4">
			<div class="card">
				<div class="card-body">
					<form action="<?= $app->routeUrl("/login"); ?>" method="post">
						<h4 class="text-center">Log In</h4>
						<?= Util::insertCSRFToken(); ?>
						<input type="text" name="email" placeholder="E-Mail Address or Username" class="form-control mb-2"/>
						<input type="password" name="password" placeholder="Password" class="form-control mb-2"/>
						<input type="submit" class="btn btn-primary btn-block mb-1" value="Sign In"/>
						
						<a href="<?= $app->routeUrl("/register"); ?>" class="btn btn-light btn-block my-0">Create an account</a>

						<a href="<?= $app->routeUrl("/gigadriveLogin"); ?>" class="btn btn-success btn-block mt-1">Login with Gigadrive</a>
					</form>
				</div>
			</div>
		</div>

		<div class="col-lg-8">
			right
		</div>
	</div>
</div>
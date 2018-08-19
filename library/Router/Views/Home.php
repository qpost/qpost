<div class="container container-fluid">
	<div class="row">
		<div class="col-lg-4">
			<div class="card">
				<div class="homeLeftBox">
					<img src="/android-chrome-384x384.png" class="rounded" height="90"/>

					<h3>qpost</h3>

					<p class="text-muted">
						Follow the people you are interested in.
					</p>

					<a href="/login/gigadrive" class="btn btn-success clearUnderline btn-block" data-no-instant>Sign in with Gigadrive</a>

					<div class="text-center text-muted font-weight-bold small my-2">OR</div>

					<form action="/login" method="post">
						<?= Util::insertCSRFToken() ?>
						<input type="email" class="form-control mb-2" name="email" placeholder="Email"/>
						<input type="text" class="form-control mb-2" name="displayName" placeholder="Full Name"/>
						<input type="text" class="form-control mb-2" name="username" placeholder="Username"/>
						<input type="password" class="form-control mb-2" name="password" placeholder="Password"/>
						<input type="submit" class="btn btn-primary btn-block" value="Register"/>
					</form>

					<div class="mt-2 small">
						By clicking Register you agree to our <a href="https://gigadrivegroup.com/legal/terms-of-service" target="_blank">Terms of Service</a> and <a href="https://gigadrivegroup.com/legal/privacy-policy" target="_blank">Privacy Policy</a>.
					</div>
				</div>
			</div>

			<div class="card mt-3">
				<div class="card-body text-center">
					Already have an account? <a href="/login">Log in</a>
				</div>
			</div>
		</div>

		<div class="col-lg-8">
			<img src="/assets/img/responsivedemo.png" class="mt-5" style="width: 100%"/>
		</div>
	</div>
</div>
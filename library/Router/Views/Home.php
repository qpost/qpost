<?php

use Gigadrive\MailTemplates\MailTemplates;

$errorMsg = null;
$successMsg = null;

if(isset($_POST["email"]) && isset($_POST["displayName"]) && isset($_POST["username"]) && isset($_POST["password"])){
	$email = trim(Util::fixString($_POST["email"]));
	$displayName = trim(Util::fixString($_POST["displayName"]));
	$username = trim(Util::fixString($_POST["username"]));
	$password = trim($_POST["password"]);

	if(!Util::isEmpty($email) && !Util::isEmpty($displayName) && !Util::isEmpty($username) && !Util::isEmpty($password)){
		if(strlen($email) >= 3){
			if(filter_var($email,FILTER_VALIDATE_EMAIL)){
				if(strlen($displayName) >= 1 && strlen($displayName) <= 25){
					if(strlen($username) >= 3){
						if(strlen($username) <= 16){
							if(ctype_alnum($username)){
								if(!Util::contains($displayName,"☑️") && !Util::contains($displayName,"✔️") && !Util::contains($displayName,"✅") && !Util::contains($displayName,"🗹") && !Util::contains($displayName,"🗸")){
									if(Util::isEmailAvailable($email)){
										if(Util::isUsernameAvailable($username)){
											$displayName = Util::sanatizeString($displayName);
		
											$mysqli = Database::Instance()->get();
		
											$emailToken = Util::getRandomString(7);
		
											$password = password_hash($password,PASSWORD_BCRYPT);
		
											$stmt = $mysqli->prepare("INSERT INTO `users` (`displayName`,`username`,`email`,`password`,`emailActivationToken`) VALUES(?,?,?,?,?);");
											$stmt->bind_param("sssss",$displayName,$username,$email,$password,$emailToken);
											if($stmt->execute()){
												$id = $stmt->insert_id;
		
												$mailContent = MailTemplates::readTemplate("verifyEmail",[
													"qpost: Verify your email address",
													"Complete your qpost registration!",
													"Hello, " . $displayName . "!",
													"To complete the creation of your qpost account, please click the button below and verify your email address.",
													"https://qpost.gigadrivegroup.com/account/verify-email?account=" . $id . "&verificationtoken=" . $emailToken,
													"Verify",
													"You did not register for qpost?",
													"Don't worry! Simply ignore this email and the account registered with this email address will be deleted in 2 weeks.",
													"Contact Info",
													"Terms of Service",
													"Privacy Policy",
													"Disclaimer",
													"You don't want to receive this type of emails?",
													"Click here to change your email settings or unsubscribe."
												]);
		
												Util::sendMail($email,"qpost: Verify your email address",$mailContent,"Paste this link into your browser to verify your account on qpost: https://qpost.gigadrivegroup.com/account/verify-email?account=" . $id . "&verificationtoken=" . $emailToken,$displayName);
		
												$user = User::getUserById($id);
		
												$successMsg = "Your account has been created. An activation email has been sent to you. Click the link in that email to verify your account. (Check your spam folder!)";
											} else {
												$errorMsg = "An error occurred. " . $stmt->error;
											}
											$stmt->close();
										} else {
											$errorMsg = "That username is not available anymore.";
										}
									} else {
										$errorMsg = "That email is not available anymore.";
									}
								} else {
									$errorMsg = "Invalid display name.";
								}
							} else {
								$errorMsg = "Your username may only consist of letters and numbers.";
							}
						} else {
							$errorMsg = "Your username may not be longer than 16 characters.";
						}
					} else {
						$errorMsg = "Your username must be at least 3 characters long.";
					}
				} else {
					$errorMsg = "Your name must be between 1 and 25 characters long.";
				}
			} else {
				$errorMsg = "Please enter a valid email address.";
			}
		} else {
			$errorMsg = "Please enter a valid email address.";
		}
	} else {
		$errorMsg = "Please fill all the fields.";
	}
}

?><div class="container container-fluid">
	<?php

		if(isset($_GET["msg"])){
			switch($_GET["msg"]){
				case "gigadriveLoginUsernameNotAvailable":
					echo Util::createAlert($_GET["msg"],"Your Gigadrive username has already been used on qpost. Please change your Gigadrive username or consider registering via the formular below.",ALERT_TYPE_DANGER);
					break;
				case "gigadriveLoginEmailNotAvailable":
					echo Util::createAlert($_GET["msg"],"Your email address has already been used on qpost. Please change your Gigadrive email address or consider registering via the formular below.",ALERT_TYPE_DANGER);
					break;
			}
		}

	?>
	<div class="row">
		<div class="col-lg-4">
			<div class="card">
				<div class="homeLeftBox">
					<img src="/android-chrome-384x384.png" class="rounded" height="90"/>

					<h3>qpost</h3>

					<p class="text-muted">
						Follow the people you are interested in.
					</p>

					<?php if(is_null($successMsg)){ ?>

					<a href="/login/gigadrive" class="btn btn-success clearUnderline btn-block" data-no-instant>Sign in with Gigadrive</a>

					<div class="text-center text-muted font-weight-bold small my-2">OR</div>

					<?= !is_null($errorMsg) ? Util::createAlert("errorMsg",$errorMsg,ALERT_TYPE_DANGER) : "" ?>

					<form action="/" method="post">
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

					<?php } else {
						echo Util::createAlert("successMsg",$successMsg,ALERT_TYPE_SUCCESS);
					} ?>
				</div>
			</div>

			<div class="card mt-3">
				<div class="card-body text-center">
					Already have an account? <a href="/login" data-no-instant>Log in</a>
				</div>
			</div>
		</div>

		<div class="col-lg-8">
			<img src="/assets/img/responsivedemo.png" class="mt-5" style="width: 100%"/>
		</div>
	</div>
</div>
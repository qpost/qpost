<?php

use qpost\Util\Util;

$user = Util::getCurrentUser();

?>
<div class="card mb-3">
	<table class="table my-0">
		<tr>
			<td style="width: 50%"><b>Account ID</b></td>
			<td style="width: 50%">#<?= $user->getID(); ?></td>
		</tr>

		<tr>
			<td style="width: 50%"><b>Email Address</b></td>
			<td style="width: 50%"><?= $user->getEmail(); ?></td>
		</tr>

		<tr>
			<td style="width: 50%"><b>Registration Date</b></td>
			<td style="width: 50%"><?= Util::timeago($user->getTime()); ?></td>
		</tr>

		<?php if($user->isGigadriveLinked()){ ?>
		<tr>
			<td style="width: 50%">&nbsp;</td>
			<td style="width: 50%"><b>To further manage your account, visit the <a href="https://gigadrivegroup.com/account" target="_blank">Gigadrive website</a>.</b></td>
		</tr>
		<?php } ?>
	</table>
</div>

<a href="<?= $app->routeUrl("/delete") ?>" class="btn btn-danger">
	Delete this account
</a>
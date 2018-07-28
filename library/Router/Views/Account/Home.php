<?php

$user = Util::getCurrentUser();

?>
<div class="card">
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

		<tr>
			<td style="width: 50%">&nbsp;</td>
			<td style="width: 50%"><b>To further manage your account, visit the <a href="https://gigadrivegroup.com/account" target="_blank">Gigadrive website</a>.</b></td>
		</tr>
	</table>
</div>
<?php

$app->bind("/:query",function($params){
	$query = $params["query"];

	if(!empty($query)){
		$user = User::getUserByUsername($query);
		if(is_null($user) && is_numeric($query)) $user = @User::getUserById((int)$query);

		if(!is_null($user)){
			if($query !== $user->getUsername())
				$this->reroute("/" . $user->getUsername());

			$data = array(
				"title" => $user->getUsername(),
				"nav" => Util::isLoggedIn() && $user->getId() == $_SESSION["id"] ? NAV_PROFILE : null,
				"user" => $user,
				"socialImage" => $user->getAvatarURL(),
				"showProfile" => true,
				"profileTab"
			);
		
			return $this->render("views:Profile.php with views:Layout.php",$data);
		}
	}
});
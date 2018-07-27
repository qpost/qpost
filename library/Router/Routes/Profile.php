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
				"nav" => $user->getId() == $_SESSION["id"] ? NAV_PROFILE : null,
				"user" => $user,
				"socialImage" => $user->getAvatarURL()
			);
		
			return $this->render("views:Profile.php with views:Layout.php",$data);
		}
	}
});
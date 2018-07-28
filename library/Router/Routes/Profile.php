<?php

$app->bind("/:query/following",function($params){
	$query = $params["query"];
	$page = 1;

	if(!empty($query)){
		$user = User::getUserByUsername($query);
		if(is_null($user) && is_numeric($query)) $user = @User::getUserById((int)$query);

		if(!is_null($user)){
			if($query !== $user->getUsername())
				$this->reroute("/" . $user->getUsername());

			$data = array(
				"title" => $user->getDisplayName() . " (@" . $user->getUsername() . ")",
				"nav" => Util::isLoggedIn() && $user->getId() == $_SESSION["id"] ? NAV_PROFILE : null,
				"user" => $user,
				"socialImage" => $user->getAvatarURL(),
				"showProfile" => true,
				"profileTab" => PROFILE_TAB_FOLLOWING,
				"currentPage" => $page,
				"subtitle" => "People followed by " . $user->getUsername()
			);
		
			return $this->render("views:Profile/Following.php with views:Layout.php",$data);
		}
	}
});

$app->bind("/:query/following/:page",function($params){
	$query = $params["query"];
	$page = is_numeric($params["page"]) && (int)$params["page"] > 0 ? (int)$params["page"] : 1;

	if(!empty($query)){
		$user = User::getUserByUsername($query);
		if(is_null($user) && is_numeric($query)) $user = @User::getUserById((int)$query);

		if(!is_null($user)){
			if($query !== $user->getUsername())
				$this->reroute("/" . $user->getUsername());

			$data = array(
				"title" => $user->getDisplayName() . " (@" . $user->getUsername() . ")",
				"nav" => Util::isLoggedIn() && $user->getId() == $_SESSION["id"] ? NAV_PROFILE : null,
				"user" => $user,
				"socialImage" => $user->getAvatarURL(),
				"showProfile" => true,
				"profileTab" => PROFILE_TAB_FOLLOWING,
				"currentPage" => $page,
				"subtitle" => "People followed by " . $user->getUsername()
			);
		
			return $this->render("views:Profile/Following.php with views:Layout.php",$data);
		}
	}
});

$app->bind("/:query/followers",function($params){
	$query = $params["query"];
	$page = 1;

	if(!empty($query)){
		$user = User::getUserByUsername($query);
		if(is_null($user) && is_numeric($query)) $user = @User::getUserById((int)$query);

		if(!is_null($user)){
			if($query !== $user->getUsername())
				$this->reroute("/" . $user->getUsername());

			$data = array(
				"title" => $user->getDisplayName() . " (@" . $user->getUsername() . ")",
				"nav" => Util::isLoggedIn() && $user->getId() == $_SESSION["id"] ? NAV_PROFILE : null,
				"user" => $user,
				"socialImage" => $user->getAvatarURL(),
				"showProfile" => true,
				"profileTab" => PROFILE_TAB_FOLLOWERS,
				"currentPage" => $page,
				"subtitle" => "People following " . $user->getUsername()
			);
		
			return $this->render("views:Profile/Followers.php with views:Layout.php",$data);
		}
	}
});

$app->bind("/:query/followers/:page",function($params){
	$query = $params["query"];
	$page = is_numeric($params["page"]) && (int)$params["page"] > 0 ? (int)$params["page"] : 1;

	if(!empty($query)){
		$user = User::getUserByUsername($query);
		if(is_null($user) && is_numeric($query)) $user = @User::getUserById((int)$query);

		if(!is_null($user)){
			if($query !== $user->getUsername())
				$this->reroute("/" . $user->getUsername());

			$data = array(
				"title" => $user->getDisplayName() . " (@" . $user->getUsername() . ")",
				"nav" => Util::isLoggedIn() && $user->getId() == $_SESSION["id"] ? NAV_PROFILE : null,
				"user" => $user,
				"socialImage" => $user->getAvatarURL(),
				"showProfile" => true,
				"profileTab" => PROFILE_TAB_FOLLOWERS,
				"currentPage" => $page,
				"subtitle" => "People following " . $user->getUsername()
			);
		
			return $this->render("views:Profile/Followers.php with views:Layout.php",$data);
		}
	}
});

$app->bind("/:query",function($params){
	$query = $params["query"];
	$page = 1;

	if(!empty($query)){
		$user = User::getUserByUsername($query);
		if(is_null($user) && is_numeric($query)) $user = @User::getUserById((int)$query);

		if(!is_null($user)){
			if($query !== $user->getUsername())
				$this->reroute("/" . $user->getUsername());

			$data = array(
				"title" => $user->getDisplayName() . " (@" . $user->getUsername() . ")",
				"nav" => Util::isLoggedIn() && $user->getId() == $_SESSION["id"] ? NAV_PROFILE : null,
				"user" => $user,
				"socialImage" => $user->getAvatarURL(),
				"showProfile" => true,
				"profileTab" => PROFILE_TAB_POSTS,
				"currentPage" => $page,
				"subtitle" => $user->getUsername() . "'s profile"
			);
		
			return $this->render("views:Profile/Posts.php with views:Layout.php",$data);
		}
	}
});

$app->bind("/:query/:page",function($params){
	$query = $params["query"];
	$page = is_numeric($params["page"]) && (int)$params["page"] > 0 ? (int)$params["page"] : 1;

	if(!empty($query)){
		$user = User::getUserByUsername($query);
		if(is_null($user) && is_numeric($query)) $user = @User::getUserById((int)$query);

		if(!is_null($user)){
			if($query !== $user->getUsername())
				$this->reroute("/" . $user->getUsername());

			$data = array(
				"title" => $user->getDisplayName() . " (@" . $user->getUsername() . ")",
				"nav" => Util::isLoggedIn() && $user->getId() == $_SESSION["id"] ? NAV_PROFILE : null,
				"user" => $user,
				"socialImage" => $user->getAvatarURL(),
				"showProfile" => true,
				"profileTab" => PROFILE_TAB_POSTS,
				"currentPage" => $page,
				"subtitle" => $user->getUsername() . "'s profile"
			);
		
			return $this->render("views:Profile/Posts.php with views:Layout.php",$data);
		}
	}
});
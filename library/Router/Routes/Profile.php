<?php

$app->bind("/:query/following",function($params){
	$query = $params["query"];
	$page = 1;

	if(!empty($query)){
		$user = User::getUserByUsername($query);
		if(is_null($user) && is_numeric($query)) $user = @User::getUserById((int)$query);

		if(!is_null($user)){
			if(Util::isLoggedIn() && $user->hasBlocked(Util::getCurrentUser())){
				return $this->reroute("/" . $user->getUsername());
			} else {
				if($user->getPrivacyLevel() == PRIVACY_LEVEL_CLOSED && (!isset($_SESSION["id"]) || $user->getId() != $_SESSION["id"])){
					return $this->reroute("/" . $user->getUsername());
				} else if($user->getPrivacyLevel() == PRIVACY_LEVEL_PRIVATE && (!Util::isLoggedIn() || ($user->getId() != $_SESSION["id"] && !$user->isFollower($_SESSION["id"])))){
					return $this->reroute("/" . $user->getUsername());
				} else {
					if($query !== $user->getUsername())
						$this->reroute("/" . $user->getUsername());
	
					$data = array(
						"title" => "People followed by " . $user->getUsername(),
						"nav" => Util::isLoggedIn() && $user->getId() == $_SESSION["id"] ? NAV_PROFILE : null,
						"user" => $user,
						"socialImage" => $user->getAvatarURL(),
						"showProfile" => true,
						"profileTab" => PROFILE_TAB_FOLLOWING,
						"currentPage" => $page,
						"subtitle" => "People followed by " . $user->getUsername() . (Util::isLoggedIn() && $user->getId() == $_SESSION["id"] ? '<a class="float-right text-white" href="/edit">Edit profile</a>' : "")
					);
				
					return $this->render("views:Profile/Following.php with views:Layout.php",$data);
				}
			}
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
			if(Util::isLoggedIn() && $user->hasBlocked(Util::getCurrentUser())){
				return $this->reroute("/" . $user->getUsername());
			} else {
				if($user->getPrivacyLevel() == PRIVACY_LEVEL_CLOSED && (!isset($_SESSION["id"]) || $user->getId() != $_SESSION["id"])){
					return $this->reroute("/" . $user->getUsername());
				} else if($user->getPrivacyLevel() == PRIVACY_LEVEL_PRIVATE && (!Util::isLoggedIn() || ($user->getId() != $_SESSION["id"] && !$user->isFollower($_SESSION["id"])))){
					return $this->reroute("/" . $user->getUsername());
				} else {
					if($query !== $user->getUsername())
						$this->reroute("/" . $user->getUsername());
	
					$data = array(
						"title" => "People followed by " . $user->getUsername(),
						"nav" => Util::isLoggedIn() && $user->getId() == $_SESSION["id"] ? NAV_PROFILE : null,
						"user" => $user,
						"socialImage" => $user->getAvatarURL(),
						"showProfile" => true,
						"profileTab" => PROFILE_TAB_FOLLOWING,
						"currentPage" => $page,
						"subtitle" => "People followed by " . $user->getUsername() . (Util::isLoggedIn() && $user->getId() == $_SESSION["id"] ? '<a class="float-right text-white" href="/edit">Edit profile</a>' : "")
					);
				
					return $this->render("views:Profile/Following.php with views:Layout.php",$data);
				}
			}
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
			if(Util::isLoggedIn() && $user->hasBlocked(Util::getCurrentUser())){
				return $this->reroute("/" . $user->getUsername());
			} else {
				if($user->getPrivacyLevel() == PRIVACY_LEVEL_CLOSED && (!isset($_SESSION["id"]) || $user->getId() != $_SESSION["id"])){
					return $this->reroute("/" . $user->getUsername());
				} else if($user->getPrivacyLevel() == PRIVACY_LEVEL_PRIVATE && (!Util::isLoggedIn() || ($user->getId() != $_SESSION["id"] && !$user->isFollower($_SESSION["id"])))){
					return $this->reroute("/" . $user->getUsername());
				} else {
					if($query !== $user->getUsername())
						$this->reroute("/" . $user->getUsername());
	
					$data = array(
						"title" => "People following " . $user->getUsername(),
						"nav" => Util::isLoggedIn() && $user->getId() == $_SESSION["id"] ? NAV_PROFILE : null,
						"user" => $user,
						"socialImage" => $user->getAvatarURL(),
						"showProfile" => true,
						"profileTab" => PROFILE_TAB_FOLLOWERS,
						"currentPage" => $page,
						"subtitle" => "People following " . $user->getUsername() . (Util::isLoggedIn() && $user->getId() == $_SESSION["id"] ? '<a class="float-right text-white" href="/edit">Edit profile</a>' : "")
					);
				
					return $this->render("views:Profile/Followers.php with views:Layout.php",$data);
				}
			}
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
			if(Util::isLoggedIn() && $user->hasBlocked(Util::getCurrentUser())){
				return $this->reroute("/" . $user->getUsername());
			} else {
				if($user->getPrivacyLevel() == PRIVACY_LEVEL_CLOSED && (!isset($_SESSION["id"]) || $user->getId() != $_SESSION["id"])){
					return $this->reroute("/" . $user->getUsername());
				} else if($user->getPrivacyLevel() == PRIVACY_LEVEL_PRIVATE && (!Util::isLoggedIn() || ($user->getId() != $_SESSION["id"] && !$user->isFollower($_SESSION["id"])))){
					return $this->reroute("/" . $user->getUsername());
				} else {
					if($query !== $user->getUsername())
						$this->reroute("/" . $user->getUsername());
	
					$data = array(
						"title" => "People following " . $user->getUsername(),
						"nav" => Util::isLoggedIn() && $user->getId() == $_SESSION["id"] ? NAV_PROFILE : null,
						"user" => $user,
						"socialImage" => $user->getAvatarURL(),
						"showProfile" => true,
						"profileTab" => PROFILE_TAB_FOLLOWERS,
						"currentPage" => $page,
						"subtitle" => "People following " . $user->getUsername() . (Util::isLoggedIn() && $user->getId() == $_SESSION["id"] ? '<a class="float-right text-white" href="/edit">Edit profile</a>' : "")
					);
				
					return $this->render("views:Profile/Followers.php with views:Layout.php",$data);
				}
			}
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
			if(Util::isLoggedIn() && $user->hasBlocked(Util::getCurrentUser())){
				$data = array(
					"user" => $user,
					"socialImage" => $user->getAvatarURL(),
					"preventStatusModal" => true
				);
			
				return $this->render("views:Profile/Blocked.php with views:Layout.php",$data);
			} else {
				if($user->getPrivacyLevel() == PRIVACY_LEVEL_CLOSED && (!isset($_SESSION["id"]) || $user->getId() != $_SESSION["id"])){
					$data = array(
						"user" => $user,
						"socialImage" => $user->getAvatarURL(),
						"preventStatusModal" => true
					);
				
					return $this->render("views:Profile/ClosedLevel.php with views:Layout.php",$data);
				} else if($user->getPrivacyLevel() == PRIVACY_LEVEL_PRIVATE && (!Util::isLoggedIn() || ($user->getId() != $_SESSION["id"] && !$user->isFollower($_SESSION["id"])))){
					$data = array(
						"user" => $user,
						"socialImage" => $user->getAvatarURL(),
						"preventStatusModal" => true
					);
				
					return $this->render("views:Profile/PrivateLevel.php with views:Layout.php",$data);
				} else {
					if($query !== $user->getUsername())
						$this->reroute("/" . $user->getUsername());
	
					$data = array(
						"title" => $user->getDisplayName() . " (@" . $user->getUsername() . ")",
						"nav" => Util::isLoggedIn() && $user->getId() == $_SESSION["id"] ? NAV_PROFILE : null,
						"user" => $user,
						"socialImage" => $user->getAvatarURL(),
						"showProfile" => true,
						"profileTab" => PROFILE_TAB_FEED,
						"currentPage" => $page,
						"subtitle" => $user->getUsername() . "'s profile" . (Util::isLoggedIn() && $user->getId() == $_SESSION["id"] ? ' (This is you!)<a class="float-right text-white" href="/edit">Edit profile</a>' : "")
					);
				
					return $this->render("views:Profile/Feed.php with views:Layout.php",$data);
				}
			}
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
			if(Util::isLoggedIn() && $user->hasBlocked(Util::getCurrentUser())){
				return $this->reroute("/" . $user->getUsername());
			} else {
				if($user->getPrivacyLevel() == PRIVACY_LEVEL_CLOSED && (!isset($_SESSION["id"]) || $user->getId() != $_SESSION["id"])){
					return $this->reroute("/" . $user->getUsername());
				} else if($user->getPrivacyLevel() == PRIVACY_LEVEL_PRIVATE && (!Util::isLoggedIn() || ($user->getId() != $_SESSION["id"] && !$user->isFollower($_SESSION["id"])))){
					return $this->reroute("/" . $user->getUsername());
				} else {
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
							"profileTab" => PROFILE_TAB_FEED,
							"currentPage" => $page,
							"subtitle" => $user->getUsername() . "'s profile" . (Util::isLoggedIn() && $user->getId() == $_SESSION["id"] ? ' (This is you!)<a class="float-right text-white" href="/edit">Edit profile</a>' : "")
						);
					
						return $this->render("views:Profile/Feed.php with views:Layout.php",$data);
					}
				}
			}
		}
	}
});
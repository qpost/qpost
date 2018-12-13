<?php

$app->bind("/:query/following",function($params){
	$query = $params["query"];
	$page = 1;

	if(!Util::isEmpty($query)){
		$user = User::getUserByUsername($query);
		if(is_null($user) && is_numeric($query)) $user = @User::getUserById((int)$query);

		if(!is_null($user)){
			if($user->isEmailActivated()){
				if(Util::isLoggedIn() && $user->hasBlocked(Util::getCurrentUser())){
					return $this->reroute("/" . $user->getUsername());
				} else if($user->isSuspended()){
					return $this->reroute("/" . $user->getUsername());
				} else {
					if($user->getPrivacyLevel() == PrivacyLevel::CLOSED && (!is_null(Util::getCurrentUser()) || $user->getId() != Util::getCurrentUser()->getId())){
						return $this->reroute("/" . $user->getUsername());
					} else if($user->getPrivacyLevel() == PrivacyLevel::PRIVATE && (!Util::isLoggedIn() || ($user->getId() != Util::getCurrentUser()->getId() && !$user->isFollower(Util::getCurrentUser()->getId())))){
						return $this->reroute("/" . $user->getUsername());
					} else {
						if($query !== $user->getUsername())
							$this->reroute("/" . $user->getUsername());
		
						$data = array(
							"title" => "People followed by " . $user->getUsername(),
							"nav" => Util::isLoggedIn() && $user->getId() == Util::getCurrentUser()->getId() ? NAV_PROFILE : null,
							"user" => $user,
							"socialImage" => $user->getAvatarURL(),
							"showProfile" => true,
							"profileTab" => PROFILE_TAB_FOLLOWING,
							"currentPage" => $page,
							"description" => $user->getBio()
						);
					
						return $this->render("views:Profile/Following.php with views:Layout.php",$data);
					}
				}
			}
		}
	}
});

$app->bind("/:query/following/:page",function($params){
	$query = $params["query"];
	$page = is_numeric($params["page"]) && (int)$params["page"] > 0 ? (int)$params["page"] : 1;

	if(!Util::isEmpty($query)){
		$user = User::getUserByUsername($query);
		if(is_null($user) && is_numeric($query)) $user = @User::getUserById((int)$query);

		if(!is_null($user)){
			if($user->isEmailActivated()){
				if(Util::isLoggedIn() && $user->hasBlocked(Util::getCurrentUser())){
					return $this->reroute("/" . $user->getUsername());
				} else if($user->isSuspended()){
					return $this->reroute("/" . $user->getUsername());
				} else {
					if($user->getPrivacyLevel() == PrivacyLevel::CLOSED && (!is_null(Util::getCurrentUser()) || $user->getId() != Util::getCurrentUser()->getId())){
						return $this->reroute("/" . $user->getUsername());
					} else if($user->getPrivacyLevel() == PrivacyLevel::PRIVATE && (!Util::isLoggedIn() || ($user->getId() != Util::getCurrentUser()->getId() && !$user->isFollower(Util::getCurrentUser()->getId())))){
						return $this->reroute("/" . $user->getUsername());
					} else {
						if($query !== $user->getUsername())
							$this->reroute("/" . $user->getUsername());
		
						$data = array(
							"title" => "People followed by " . $user->getUsername(),
							"nav" => Util::isLoggedIn() && $user->getId() == Util::getCurrentUser()->getId() ? NAV_PROFILE : null,
							"user" => $user,
							"socialImage" => $user->getAvatarURL(),
							"showProfile" => true,
							"profileTab" => PROFILE_TAB_FOLLOWING,
							"currentPage" => $page,
							"description" => $user->getBio()
						);
					
						return $this->render("views:Profile/Following.php with views:Layout.php",$data);
					}
				}
			}
		}
	}
});

$app->bind("/:query/followers",function($params){
	$query = $params["query"];
	$page = 1;

	if(!Util::isEmpty($query)){
		$user = User::getUserByUsername($query);
		if(is_null($user) && is_numeric($query)) $user = @User::getUserById((int)$query);

		if(!is_null($user)){
			if($user->isEmailActivated()){
				if(Util::isLoggedIn() && $user->hasBlocked(Util::getCurrentUser())){
					return $this->reroute("/" . $user->getUsername());
				} else if($user->isSuspended()){
					return $this->reroute("/" . $user->getUsername());
				} else {
					if($user->getPrivacyLevel() == PrivacyLevel::CLOSED && (!is_null(Util::getCurrentUser()) || $user->getId() != Util::getCurrentUser()->getId())){
						return $this->reroute("/" . $user->getUsername());
					} else if($user->getPrivacyLevel() == PrivacyLevel::PRIVATE && (!Util::isLoggedIn() || ($user->getId() != Util::getCurrentUser()->getId() && !$user->isFollower(Util::getCurrentUser()->getId())))){
						return $this->reroute("/" . $user->getUsername());
					} else {
						if($query !== $user->getUsername())
							$this->reroute("/" . $user->getUsername());
		
						$data = array(
							"title" => "People following " . $user->getUsername(),
							"nav" => Util::isLoggedIn() && $user->getId() == Util::getCurrentUser()->getId() ? NAV_PROFILE : null,
							"user" => $user,
							"socialImage" => $user->getAvatarURL(),
							"showProfile" => true,
							"profileTab" => PROFILE_TAB_FOLLOWERS,
							"currentPage" => $page,
							"description" => $user->getBio()
						);
					
						return $this->render("views:Profile/Followers.php with views:Layout.php",$data);
					}
				}
			}
		}
	}
});

$app->bind("/:query/followers/:page",function($params){
	$query = $params["query"];
	$page = is_numeric($params["page"]) && (int)$params["page"] > 0 ? (int)$params["page"] : 1;

	if(!Util::isEmpty($query)){
		$user = User::getUserByUsername($query);
		if(is_null($user) && is_numeric($query)) $user = @User::getUserById((int)$query);

		if(!is_null($user)){
			if($user->isEmailActivated()){
				if(Util::isLoggedIn() && $user->hasBlocked(Util::getCurrentUser())){
					return $this->reroute("/" . $user->getUsername());
				} else if($user->isSuspended()){
					return $this->reroute("/" . $user->getUsername());
				} else {
					if($user->getPrivacyLevel() == PrivacyLevel::CLOSED && (!is_null(Util::getCurrentUser()) || $user->getId() != Util::getCurrentUser()->getId())){
						return $this->reroute("/" . $user->getUsername());
					} else if($user->getPrivacyLevel() == PrivacyLevel::PRIVATE && (!Util::isLoggedIn() || ($user->getId() != Util::getCurrentUser()->getId() && !$user->isFollower(Util::getCurrentUser()->getId())))){
						return $this->reroute("/" . $user->getUsername());
					} else {
						if($query !== $user->getUsername())
							$this->reroute("/" . $user->getUsername());
		
						$data = array(
							"title" => "People following " . $user->getUsername(),
							"nav" => Util::isLoggedIn() && $user->getId() == Util::getCurrentUser()->getId() ? NAV_PROFILE : null,
							"user" => $user,
							"socialImage" => $user->getAvatarURL(),
							"showProfile" => true,
							"profileTab" => PROFILE_TAB_FOLLOWERS,
							"currentPage" => $page,
							"description" => $user->getBio()
						);
					
						return $this->render("views:Profile/Followers.php with views:Layout.php",$data);
					}
				}
			}
		}
	}
});

$app->bind("/:query",function($params){
	$query = $params["query"];
	$page = 1;

	if(!Util::isEmpty($query)){
		$user = User::getUserByUsername($query);
		if(is_null($user) && is_numeric($query)) $user = @User::getUserById((int)$query);

		if(!is_null($user)){
			if($user->isEmailActivated()){
				if(Util::isLoggedIn() && $user->hasBlocked(Util::getCurrentUser())){
					$data = array(
						"user" => $user,
						"socialImage" => $user->getAvatarURL(),
						"preventStatusModal" => true
					);
				
					return $this->render("views:Profile/Blocked.php with views:Layout.php",$data);
				} else if($user->isSuspended()){
					$data = array(
						"title" => "Account suspended",
						"user" => $user,
						"preventStatusModal" => true
					);
				
					return $this->render("views:Profile/Suspended.php with views:Layout.php",$data);
				} else {
					if($user->getPrivacyLevel() == PrivacyLevel::CLOSED && (!is_null(Util::getCurrentUser()) || $user->getId() != Util::getCurrentUser()->getId())){
						$data = array(
							"user" => $user,
							"socialImage" => $user->getAvatarURL(),
							"preventStatusModal" => true
						);
					
						return $this->render("views:Profile/ClosedLevel.php with views:Layout.php",$data);
					} else if($user->getPrivacyLevel() == PrivacyLevel::PRIVATE && (!Util::isLoggedIn() || ($user->getId() != Util::getCurrentUser()->getId() && !$user->isFollower(Util::getCurrentUser()->getId())))){
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
							"nav" => Util::isLoggedIn() && $user->getId() == Util::getCurrentUser()->getId() ? NAV_PROFILE : null,
							"user" => $user,
							"socialImage" => $user->getAvatarURL(),
							"showProfile" => true,
							"profileTab" => PROFILE_TAB_FEED,
							"currentPage" => $page,
							"description" => $user->getBio()
						);
					
						return $this->render("views:Profile/Feed.php with views:Layout.php",$data);
					}
				}
			}
		}
	}
});

$app->bind("/:query/:page",function($params){
	$query = $params["query"];
	$page = is_numeric($params["page"]) && (int)$params["page"] > 0 ? (int)$params["page"] : 1;

	if(!Util::isEmpty($query)){
		$user = User::getUserByUsername($query);
		if(is_null($user) && is_numeric($query)) $user = @User::getUserById((int)$query);

		if(!is_null($user)){
			if($user->isEmailActivated()){
				if(Util::isLoggedIn() && $user->hasBlocked(Util::getCurrentUser())){
					return $this->reroute("/" . $user->getUsername());
				} else if($user->isSuspended()){
					return $this->reroute("/" . $user->getUsername());
				} else {
					if($user->getPrivacyLevel() == PrivacyLevel::CLOSED && (!is_null(Util::getCurrentUser()) || $user->getId() != Util::getCurrentUser()->getId())){
						return $this->reroute("/" . $user->getUsername());
					} else if($user->getPrivacyLevel() == PrivacyLevel::PRIVATE && (!Util::isLoggedIn() || ($user->getId() != Util::getCurrentUser()->getId() && !$user->isFollower(Util::getCurrentUser()->getId())))){
						return $this->reroute("/" . $user->getUsername());
					} else {
						$user = User::getUserByUsername($query);
						if(is_null($user) && is_numeric($query)) $user = @User::getUserById((int)$query);
			
						if(!is_null($user)){
							if($query !== $user->getUsername())
								$this->reroute("/" . $user->getUsername());
			
							$data = array(
								"title" => $user->getDisplayName() . " (@" . $user->getUsername() . ")",
								"nav" => Util::isLoggedIn() && $user->getId() == Util::getCurrentUser()->getId() ? NAV_PROFILE : null,
								"user" => $user,
								"socialImage" => $user->getAvatarURL(),
								"showProfile" => true,
								"profileTab" => PROFILE_TAB_FEED,
								"currentPage" => $page,
								"description" => $user->getBio()
							);
						
							return $this->render("views:Profile/Feed.php with views:Layout.php",$data);
						}
					}
				}
			}
		}
	}
});
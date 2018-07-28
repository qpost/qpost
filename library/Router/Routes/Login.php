<?php

$app->bind("/login",function(){
	if(isset($_GET["id"])){
		// DEBUG ROUTE FOR FORCING LOGIN

		$_SESSION["id"] = (int)$_GET["id"];
		return $this->reroute("/");
	} else {
		return $this->reroute("https://gigadrivegroup.com/authorize?app=" . GIGADRIVE_APP_ID . "&scopes=user:info,user:email");
	}
});

$app->bind("/loginCallback",function(){
	if(!Util::isLoggedIn()){
		if(isset($_GET["code"])){
			$url = "https://api.gigadrivegroup.com/v3/gettoken?secret=" . GIGADRIVE_API_SECRET . "&code=" . urlencode($_GET["code"]);
			$j = @json_decode(@file_get_contents($url),true);

			if(isset($j["success"]) && !empty($j["success"]) && isset($j["token"]) && !empty($j["token"])){
				$token = $j["token"];

				$url = "https://api.gigadrivegroup.com/v3/userdata?secret=" . GIGADRIVE_API_SECRET . "&token=" . urlencode($token);
				$j = @json_decode(@file_get_contents($url),true);

				if(isset($j["success"]) && !empty($j["success"]) && isset($j["user"])){
					$userData = $j["user"];

					if(isset($userData["id"]) && isset($userData["username"]) && isset($userData["avatar"]) && isset($userData["email"])){
						$id = $userData["id"];
						$username = $userData["username"];
						$avatar = $userData["avatar"];
						$email = $userData["email"];

						User::registerUser($id,$username,$avatar,$email,$token);

						$_SESSION["id"] = $id;

						return $this->reroute("/");
					} else {
						return $this->reroute("/");
					}
				} else {
					return $this->reroute("/");
				}
			} else {
				return $this->reroute("/");
			}
		} else {
			return $this->reroute("/");
		}
	} else {
		return $this->reroute("/");
	}
});
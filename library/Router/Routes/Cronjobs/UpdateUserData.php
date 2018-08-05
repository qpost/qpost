<?php

$app->get("/cronjobs/updateuserdata",function(){
    if(isset($_GET["secret"]) && $_GET["secret"] == CRONJOB_SECRET){
        $this->response->mime = "json";

        $mysqli = Database::Instance()->get();

        $stmt = $mysqli->prepare("SELECT `token` FROM `users` ORDER BY `lastGigadriveUpdate` ASC LIMIT 7");
        if($stmt->execute()){
            $result = $stmt->get_result();

            if($result->num_rows){
                while($row = $result->fetch_assoc()){
                    $token = $row["token"];

                    $url = "https://api.gigadrivegroup.com/v3/userdata?secret=" . GIGADRIVE_API_SECRET . "&token=" . urlencode($token);
                    $j = @json_decode(@file_get_contents($url),true);

                    if(isset($j["success"]) && !empty($j["success"]) && isset($j["user"])){
                        $userData = $j["user"];

                        if(isset($userData["id"]) && isset($userData["username"]) && isset($userData["avatar"]) && isset($userData["email"])){
                            $id = $userData["id"];
                            $username = $userData["username"];
                            $avatar = isset($userData["avatar"]["url"]) ? $userData["avatar"]["url"] : null;
                            $email = $userData["email"];

                            User::registerUser($id,$username,$avatar,$email,$token);
                        }
                    }
                }
            }
        }
        $stmt->close();

        return json_encode(["status" => "done"]);
    }
});
<?php

$app->get("/cronjobs/deleteStaleAccounts",function(){
    if(isset($_GET["secret"]) && $_GET["secret"] == CRONJOB_SECRET){
        $this->response->mime = "json";

        $mysqli = Database::Instance()->get();

        $stmt = $mysqli->prepare("DELETE FROM `users` WHERE `time` < (NOW() - INTERVAL 14 DAY) AND `emailActivated` = 0 AND `token` IS NULL");
        $r =  $stmt->execute();
        $stmt->close();

        if($r){
            return json_encode(["success" => "Done"]);
        } else {
            return json_encode(["error" => $stmt->error]);
        }
    }
});
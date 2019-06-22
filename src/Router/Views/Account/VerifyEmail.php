<?php

use qpost\Account\User;
use qpost\Database\Database;
use qpost\Util\Util;

$successMsg = null;
$errorMsg = null;

if(isset($_GET["account"]) && isset($_GET["verificationtoken"])){
    $user = User::getUserById($_GET["account"]);

    if(!is_null($user)){
        if($user->isEmailActivated() == false && $user->getEmailActivationToken() == $_GET["verificationtoken"]){
            $mysqli = Database::Instance()->get();

            $userID = $user->getId();

            $stmt = $mysqli->prepare("UPDATE `users` SET `emailActivated` = 1 WHERE `id` = ?");
            $stmt->bind_param("i",$userID);
            if($stmt->execute()){
                $user->reload();
                $successMsg = "Your email has been activated. You can now log in.";
            } else {
                $errorMsg = "An error occurred. " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errorMsg = "An error occurred.";
        }
    } else {
        $errorMsg = "An error occurred.";
    }
} else {
    $errorMsg = "An error occurred.";
}

if(!is_null($successMsg))
    echo Util::createAlert("successMsg",$successMsg,ALERT_TYPE_SUCCESS);

if(!is_null($errorMsg))
    echo Util::createAlert("errorMsg",$errorMsg,ALERT_TYPE_DANGER);

?>
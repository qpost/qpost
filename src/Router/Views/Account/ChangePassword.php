<?php

use qpost\Database\Database;
use qpost\Util\Util;

$user = Util::getCurrentUser();

$successMsg = null;
$errorMsg = null;

if(isset($_POST["currentPassword"]) && isset($_POST["newPassword"]) && isset($_POST["newPassword2"])){
    $currentPassword = $_POST["currentPassword"];
    $newPassword = $_POST["newPassword"];
    $newPassword2 = $_POST["newPassword2"];

    if(!Util::isEmpty($currentPassword) && !Util::isEmpty($newPassword) && !Util::isEmpty($newPassword2)){
        if(password_verify($currentPassword,$user->getPassword())){
            if($newPassword == $newPassword2){
                $newHash = password_hash($newPassword,PASSWORD_BCRYPT);

                $mysqli = Database::Instance()->get();
                $userId = $user->getId();

                $stmt = $mysqli->prepare("UPDATE `users` SET `password` = ? WHERE `id` = ?");
                $stmt->bind_param("si",$newHash,$userId);
                if($stmt->execute()){
                    $user->reload();
                    $successMsg = "Your password has been changed.";
                } else {
                    $errorMsg = "An error occurred. " . $stmt->error;
                }
                $stmt->close();
            } else {
                $errorMsg = "The new passwords do not match.";
            }
        } else {
            $errorMsg = "Your current password is not correct.";
        }
    } else {
        $errorMsg = "Please fill all of the fields.";
    }
}

if(!is_null($successMsg))
    echo Util::createAlert("successMsg",$successMsg,ALERT_TYPE_SUCCESS);

if(!is_null($errorMsg))
    echo Util::createAlert("errorMsg",$errorMsg,ALERT_TYPE_DANGER);

?><div class="card">
    <h5 class="card-header">Change your password</h5>

    <div class="card-body">
        <form action="/account/change-password" method="post">
            <?= Util::insertCSRFToken() ?>

            <fieldset>
                <div class="form-group row">
                    <label for="displayName" class="control-label col-sm-3 col-form-label">Current password</label>

                    <div class="col-sm-9 input-group mb-3">
                        <input class="form-control" type="password" name="currentPassword" id="currentPassword"/>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="displayName" class="control-label col-sm-3 col-form-label">New password</label>

                    <div class="col-sm-9 input-group mb-3">
                        <input class="form-control" type="password" name="newPassword" id="newPassword"/>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="displayName" class="control-label col-sm-3 col-form-label">Repeat your new password</label>

                    <div class="col-sm-9 input-group mb-3">
                        <input class="form-control" type="password" name="newPassword2" id="newPassword2"/>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-9 input-group offset-sm-3">
                        <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>
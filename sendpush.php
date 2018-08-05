<?php

require_once "library/Load/Load.php";

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

$subscription = Subscription::create(json_decode(file_get_contents("php://input"),true));

$webPush = new WebPush([
    "VAPID" => [
        "subject" => "https://qpost.gigadrivegroup.com",
        "publicKey" => "vMkBssEsXrcvmzsK8n9uWqBnSE",
        "privateKey" => CRONJOB_SECRET
    ]
]);

$res = $webPush->sendNotification([
    $subscription,
    "Hello!",
    true
]);

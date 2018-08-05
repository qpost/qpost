<?php

require_once "library/Load/Load.php";

$subscription = json_decode(file_get_contents("php://input"),true);

if(!isset($subscription["endpoint"])){
    echo "Error: not a subscription";
    return;
}

$method = $_SERVER["REQUEST_METHOD"];

switch($method){
    case "POST":
        // TODO: Create subscription entry, beware of unique endpoint
        break;
    case "PUT":
        // TODO: Update key and token depending on endpoint
        break;
    case "DELETE":
        // TODO: Delete subscription depending on endpoint
        break;
    default:
        echo "Error: method not handled";
        break;
}
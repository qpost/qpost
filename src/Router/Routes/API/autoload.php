<?php

/**
 * Sets all required headers for an API respones (mime type, CORS, etc.)
 * 
 * @param Lime\App $app
 * @return void
 */
function api_headers($app){
    // mime type json
    $app->response->mime = "json";

    // CORS
    $app->response->headers[] = "Access-Control-Allow-Origin: *";
    $app->response->headers[] = "Access-Control-Allow-Methods: GET,POST,OPTIONS,DELETE,PUT,PATCH";
    $app->response->headers[] = "Access-Control-Allow-Headers: Content-Type,DNT,X-CustomHeader,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control";
}

/**
 * Validates that the proper HTTP method was used
 * 
 * @param Lime\App $app
 * @param string $method The prefered method
 * @return bool True or false, depending on the used method
 */
function api_method_check($app,$method){
    if(isset($_SERVER["REQUEST_METHOD"])){
        $usedMethod = $_SERVER["REQUEST_METHOD"];

        if($usedMethod === "OPTIONS"){
            $app->response->status = "204";
            return false;
        }

        if($usedMethod !== $method){
            $app->response->status = "405";
            $app->response->headers[] = "Allow: " . $method . ", OPTIONS";

            return false;
        } else {
            $app->response->status = "200";
            return false;
        }
    }

    return false;
}

require "Token/Request.php";
require "Token/Verify.php";

require "User/Info.php";
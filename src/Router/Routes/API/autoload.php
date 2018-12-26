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
    $app->response->headers[] = "Access-Control-Allow-Headers: Authorization,Content-Type,DNT,X-CustomHeader,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control";
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

        api_headers($app);

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
            return true;
        }
    }

    return false;
}

/**
 * Gets the data from the current API request
 * 
 * @param Lime\App $app
 * @return array
 */
function api_request_data($app){
    if($_SERVER["REQUEST_METHOD"] === "GET"){
        return $_GET;
    } else {
        $input = file_get_contents("php://input");

        $json = json_decode($input,true);
        if(is_null($json)){
            parse_str($input, $parsed);

            return $parsed ? $parsed : [];
        } else {
            return $json;
        }
    }
}

require "Token/Request.php";
require "Token/Verify.php";

require "User/Info.php";
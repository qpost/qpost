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

require "Token/Request.php";
require "Token/Verify.php";

require "User/Info.php";
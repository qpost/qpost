<?php

$app->bind("/sitemap",function(){
    $this->response->mime = "xml";

    $s = '<?xml version="1.0" encoding="UTF-8" ?>';
    $s .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://sitemaps.org/schemas/sitemap/0.9 http://sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

    $s .= '<url>';
    $s .= '<loc>https://qpost.gigadrivegroup.com/</loc>';
    $s .= '<changefreq>daily</changefreq>';
    $s .= '<priority>1.00</priority>';
    $s .= '</url>';

    $s .= '<url>';
    $s .= '<loc>https://qpost.gigadrivegroup.com/features</loc>';
    $s .= '<changefreq>daily</changefreq>';
    $s .= '<priority>0.60</priority>';
    $s .= '</url>';

    $s .= '<url>';
    $s .= '<loc>https://qpost.gigadrivegroup.com/login</loc>';
    $s .= '<changefreq>daily</changefreq>';
    $s .= '<priority>0.60</priority>';
    $s .= '</url>';

    $s .= '</urlset>';

    return $s;
});

$app->bind("/sitemap-content",function(){
    $this->response->mime = "xml";

    $s = '<?xml version="1.0" encoding="UTF-8" ?>';
    $s .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://sitemaps.org/schemas/sitemap/0.9 http://sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

    $n = "sitemapString" . (isset($_GET["rand"]) ? "_" . $_GET["rand"] : "");

    $d = "";
    
    if(\CacheHandler::existsInCache($n)){
        $d = \CacheHandler::getFromCache($n);
    } else {
        $mysqli = Database::Instance()->get();

        $stmt = $mysqli->prepare("SELECT `id` FROM `feed` WHERE `type` = 'POST' AND `post` IS NULL ORDER BY RAND()");
        if($stmt->execute()){
            $result = $stmt->get_result();

            if($result->num_rows){
                while($row = $result->fetch_assoc()){
                    $d .= '<url>';
                    $d .= '<loc>https://qpost.gigadrivegroup.com/status/' . $row["id"] . '</loc>';
                    $d .= '<changefreq>daily</changefreq>';
                    $d .= '<priority>0.70</priority>';
                    $d .= '</url>';
                }
            }
        }
        $stmt->close();

        $stmt = $mysqli->prepare("SELECT `username` FROM `users` WHERE `emailActivated` = 1 ORDER BY RAND()");
        if($stmt->execute()){
            $result = $stmt->get_result();

            if($result->num_rows){
                while($row = $result->fetch_assoc()){
                    $d .= '<url>';
                    $d .= '<loc>https://qpost.gigadrivegroup.com/' . $row["username"] . '</loc>';
                    $d .= '<changefreq>daily</changefreq>';
                    $d .= '<priority>0.70</priority>';
                    $d .= '</url>';
                }
            }
        }
        $stmt->close();

        \CacheHandler::setToCache($n,$d,20*60);
    }

    $s .= $d;

    $s .= '</urlset>';

    return $s;
});
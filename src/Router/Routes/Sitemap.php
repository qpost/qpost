<?php

namespace qpost\Router;

create_route("/sitemap", function () {
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

<?php
/**
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpostapp.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://gnu.org/licenses/>
 */

namespace qpost\Controller;

use qpost\Constants\MiscConstants;
use qpost\Twig\Twig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function strcmp;
use function strtoupper;

class AboutController extends AbstractController {
	/**
	 * @Route("/about")
	 */
	public function about() {
		$lastUpdate = "04-02-2020";

		$thirdPartySoftware = [
			[
				"name" => "Ant Design",
				"link" => "https://ant.design"
			],
			[
				"name" => "Symfony 4",
				"link" => "https://symfony.com"
			],
			[
				"name" => "Webpack",
				"link" => "https://webpack.js.org"
			],
			[
				"name" => "Doctrine",
				"link" => "https://doctrine-project.org"
			],
			[
				"name" => "php-image-resize",
				"link" => "https://github.com/gumlet/php-image-resize"
			],
			[
				"name" => "Guzzle",
				"link" => "https://github.com/guzzle/guzzle"
			],
			[
				"name" => "JMS Serializer",
				"link" => "https://jmsyst.com/libs/serializer"
			],
			[
				"name" => "Bootstrap 4",
				"link" => "https://getbootstrap.com"
			],
			[
				"name" => "Dropzone.js",
				"link" => "https://dropzonejs.com"
			],
			[
				"name" => "jQuery",
				"link" => "https://jquery.com"
			],
			[
				"name" => "jQuery UI",
				"link" => "https://jqueryui.com"
			],
			[
				"name" => "JavaScript Cookie",
				"link" => "https://github.com/js-cookie/js-cookie"
			],
			[
				"name" => "React",
				"link" => "https://reactjs.org"
			],
			[
				"name" => "react-infinite-scroller",
				"link" => "https://github.com/noopkat/react-infinite-scroller"
			],
			[
				"name" => "react-router",
				"link" => "https://github.com/ReactTraining/react-router"
			],
			[
				"name" => "reactstrap",
				"link" => "https://reactstrap.github.io"
			],
			[
				"name" => "axios",
				"link" => "https://github.com/axios/axios"
			],
			[
				"name" => "flag-icon-css",
				"link" => "https://github.com/lipis/flag-icon-css"
			],
			[
				"name" => "json2typescript",
				"link" => "https://github.com/dhlab-basel/json2typescript"
			],
			[
				"name" => "popper.js",
				"link" => "https://popper.js.org"
			],
			[
				"name" => "react-images-viewer",
				"link" => "https://github.com/guonanci/react-images-viewer"
			],
			[
				"name" => "react-scripts-ts",
				"link" => "https://github.com/jpavon/react-scripts-ts"
			],
			[
				"name" => "ts-loader",
				"link" => "https://github.com/TypeStrong/ts-loader"
			],
			[
				"name" => "TypeScript",
				"link" => "https://www.typescriptlang.org"
			],
			[
				"name" => "ua-parser-js",
				"link" => "https://github.com/faisalman/ua-parser-js"
			],
			[
				"name" => "Font Awesome",
				"link" => "https://fontawesome.com"
			],
			[
				"name" => "react-timeago",
				"link" => "https://github.com/nmn/react-timeago"
			],
			[
				"name" => "css-loader",
				"link" => "https://github.com/webpack-contrib/css-loader"
			],
			[
				"name" => "file-loader",
				"link" => "https://github.com/webpack-contrib/file-loader"
			],
			[
				"name" => "less",
				"link" => "http://lesscss.org"
			],
			[
				"name" => "less-loader",
				"link" => "https://github.com/webpack-contrib/less-loader"
			],
			[
				"name" => "mobx",
				"link" => "https://github.com/mobxjs/mobx"
			],
			[
				"name" => "node-sass",
				"link" => "https://github.com/sass/node-sass"
			],
			[
				"name" => "optimize-css-assets-webpack-plugin",
				"link" => "https://github.com/NMFR/optimize-css-assets-webpack-plugin"
			],
			[
				"name" => "react-window-size-listener",
				"link" => "https://github.com/kunokdev/react-window-size-listener"
			],
			[
				"name" => "run-sequence",
				"link" => "https://github.com/OverZealous/run-sequence"
			],
			[
				"name" => "sass-loader",
				"link" => "https://github.com/webpack-contrib/sass-loader"
			],
			[
				"name" => "style-loader",
				"link" => "https://github.com/webpack-contrib/style-loader"
			],
			[
				"name" => "uglifyjs-webpack-plugin",
				"link" => "https://github.com/webpack-contrib/uglifyjs-webpack-plugin"
			],
			[
				"name" => "url-loader",
				"link" => "https://github.com/webpack-contrib/url-loader"
			],
			[
				"name" => "webpack-merge",
				"link" => "https://github.com/survivejs/webpack-merge"
			],
			[
				"name" => "media-embed",
				"link" => "https://github.com/dereuromark/media-embed"
			],
			[
				"name" => "react-linkify",
				"link" => "https://github.com/tasti/react-linkify"
			],
			[
				"name" => "ts-clipboard",
				"link" => "https://github.com/gforceg/ts-clipboard"
			],
			[
				"name" => "@sentry/browser",
				"link" => "https://www.npmjs.com/package/@sentry/browser"
			],
			[
				"name" => "sentry-symfony",
				"link" => "https://github.com/getsentry/sentry-symfony"
			],
			[
				"name" => "DoctrineExtensions",
				"link" => "https://github.com/beberlei/DoctrineExtensions"
			],
			[
				"name" => "react-router-ga",
				"link" => "https://github.com/fknussel/react-router-ga"
			],
			[
				"name" => "react-gif-player",
				"link" => "https://github.com/benwiley4000/react-gif-player"
			],
			[
				"name" => "react-stickynode",
				"link" => "https://github.com/yahoo/react-stickynode"
			],
			[
				"name" => "webpush-bundle",
				"link" => "https://github.com/bpolaszek/webpush-bundle"
			],
			[
				"name" => "webpush-client",
				"link" => "https://github.com/bpolaszek/webpush-js"
			],
			[
				"name" => "permissions.request",
				"link" => "https://github.com/chromium/permissions.request"
			],
			[
				"name" => "Firebase Admin SDK for PHP",
				"link" => "https://github.com/kreait/firebase-php"
			],
			[
				"name" => "CrawlerDetectBundle",
				"link" => "https://github.com/nicolasmure/CrawlerDetectBundle"
			]
		];

		usort($thirdPartySoftware, function ($a, $b) {
			return strcmp(strtoupper($a["name"]), strtoupper($b["name"]));
		});

		return $this->render("pages/about.html.twig", Twig::param([
			"lastUpdate" => $lastUpdate,
			"thirdPartySoftware" => $thirdPartySoftware,
			"title" => "About",
			"description" => "Basic information about qpost",
			"bigSocialImage" => $this->generateUrl("qpost_home_index", [], UrlGeneratorInterface::ABSOLUTE_URL) . "assets/img/bigSocialImage-default.png",
			"twitterImage" => $this->generateUrl("qpost_home_index", [], UrlGeneratorInterface::ABSOLUTE_URL) . "assets/img/bigSocialImage-default.png",
			MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_about_about", [], UrlGeneratorInterface::ABSOLUTE_URL)
		]));
	}
}
<?php
/*
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

use qpost\Cache\CacheHandler;
use qpost\Twig\Twig;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Yaml\Yaml;
use function file_get_contents;

class DownloadController extends qpostController {
	/**
	 * @Route("/download")
	 * @return Response
	 */
	public function indexAction() {
		return $this->render("pages/download.html.twig", Twig::param([
			"title" => "Download",
			"description" => "Get qpost - the open-source Twitter alternative for your device of choice.",
			"bigSocialImage" => $this->generateUrl("qpost_home_index", [], UrlGeneratorInterface::ABSOLUTE_URL) . "assets/img/bigSocialImage-default.png",
			"twitterImage" => $this->generateUrl("qpost_home_index", [], UrlGeneratorInterface::ABSOLUTE_URL) . "assets/img/favicon-512.png"
		]));
	}

	/**
	 * @Route("/download/android")
	 * @return RedirectResponse
	 */
	public function androidAction(): RedirectResponse {
		return $this->redirect("https://play.google.com/store/apps/details?id=st.qpo.android");
	}

	/**
	 * @Route("/download/windows")
	 * @return RedirectResponse
	 */
	public function windowsAction(): RedirectResponse {
		$n = "electronWindowsDownloadURL";
		if (CacheHandler::existsInCache($n)) {
			$url = CacheHandler::getFromCache($n);

			return $this->redirect($url);
		}

		$baseURL = "https://updates.qpo.st/electron";
		$latestInfo = file_get_contents($baseURL . "/latest.yml");

		if (!$latestInfo) {
			throw $this->createNotFoundException("Failed to fetch update info.");
		}

		$data = Yaml::parse($latestInfo);
		if (!$data) {
			throw $this->createNotFoundException("Failed to parse update info.");
		}

		if (!isset($data["files"]) || !is_array($data["files"]) || count($data["files"]) === 0) {
			throw $this->createNotFoundException("Failed to find file URLs.");
		}

		foreach ($data["files"] as $file) {
			if (isset($file["url"])) {
				$downloadURL = $baseURL . "/" . $file["url"];

				CacheHandler::setToCache($n, $downloadURL, 5 * 60);

				return $this->redirect($downloadURL);
			}
		}

		throw $this->createNotFoundException("Failed to find file URL.");
	}
}
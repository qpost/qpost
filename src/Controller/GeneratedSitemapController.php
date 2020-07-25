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

use Gigadrive\Bundle\SymfonyExtensionsBundle\Controller\GigadriveController;
use qpost\Entity\FeedEntry;
use qpost\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function str_replace;

class GeneratedSitemapController extends GigadriveController {
	private $entryLimit = 5000;

	/**
	 * @Route("/generated-sitemap")
	 *
	 * @param Request $request
	 * @return Response
	 */
	public function indexAction(Request $request) {
		$sitemaps = [];
		$types = ["users", "posts"];
		$limit = $this->entryLimit / 2;

		foreach ($types as $type) {
			for ($i = 1; $i <= $limit; $i++) {
				$sitemaps[] = str_replace("http://", "https://", $this->generateUrl("qpost_generatedsitemap_map", ["type" => $type, "randomizer" => $i], UrlGeneratorInterface::ABSOLUTE_URL));
			}
		}

		return $this->xml($this->render("sitemap/index.xml.twig", [
			"sitemaps" => $sitemaps
		]));
	}

	private function xml(Response $response): Response {
		$response->headers->set("Content-Type", "application/xml; charset=utf-8");

		return $response;
	}

	/**
	 * @Route("/generated-sitemap/map/{type}/{randomizer}")
	 *
	 * @param string $type
	 * @param int $randomizer
	 * @return Response
	 */
	public function mapAction(string $type, int $randomizer) {
		$entityManager = $this->generalService->entityManager;
		$urls = [];

		if ($type === "users") {
			// Users

			foreach ($entityManager->getRepository(User::class)->getSitemapUsers($randomizer, $this->entryLimit) as $username) {
				$urls[] = str_replace("http://", "https://", $this->generateUrl("qpost_page_profile", ["username" => $username], UrlGeneratorInterface::ABSOLUTE_URL));
			}
		} else {
			// Feed Entries

			foreach ($entityManager->getRepository(FeedEntry::class)->getSitemapFeedEntries($randomizer, $this->entryLimit) as $id) {
				$urls[] = str_replace("http://", "https://", $this->generateUrl("qpost_page_status", ["id" => $id], UrlGeneratorInterface::ABSOLUTE_URL));
			}
		}

		return $this->xml($this->render("sitemap/map.xml.twig", [
			"urls" => $urls
		]));
	}
}
<?php
/**
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpo.st
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

namespace qpost\Service;

use Nmure\CrawlerDetectBundle\CrawlerDetect\CrawlerDetect;
use qpost\Cache\CacheHandler;
use qpost\Constants\MiscConstants;
use qpost\Factory\HttpClientFactory;
use qpost\Twig\Twig;
use qpost\Util\Util;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use function is_null;
use function urlencode;

class RenderService {
	/**
	 * @var Environment $twig
	 */
	private $twig;

	/**
	 * @var RequestStack $requestStack
	 */
	private $requestStack;

	/**
	 * @var Request|null $currentRequest
	 */
	private $currentRequest;

	/**
	 * @var string $prerenderKey
	 */
	private $prerenderKey;

	private $crawlerDetect;

	public function __construct(Environment $twig, RequestStack $requestStack, CrawlerDetect $crawlerDetect) {
		$this->twig = $twig;
		$this->requestStack = $requestStack;
		$this->crawlerDetect = $crawlerDetect;

		$this->prerenderKey = $_ENV["PRERENDER_API_KEY"];
		$this->currentRequest = $requestStack->getCurrentRequest();
	}

	public function react(array $parameters = [], bool $ignoreCrawlerCheck = false): Response {
		// Render server-side
		if (!$ignoreCrawlerCheck && isset($parameters[MiscConstants::CANONICAL_URL]) && !Util::isEmpty($this->prerenderKey)) {
			$ignore = false;

			if (!is_null($this->currentRequest)) {
				$clientIP = $this->currentRequest->getClientIp();

				// https://www.prerender.cloud/ips-v4
				if (!is_null($clientIP) && $clientIP === "52.34.196.110") {
					$ignore = true;
				}
			}

			if (!$ignore) {
				$url = $parameters[MiscConstants::CANONICAL_URL];

				$userAgent = null;

				if (!is_null($this->currentRequest) && $this->currentRequest->headers->has("User-Agent")) {
					$userAgent = $this->currentRequest->headers->get("User-Agent");
				} else if (isset($_SERVER["HTTP_USER_AGENT"])) {
					$userAgent = $_SERVER["HTTP_USER_AGENT"];
				}

				if (!is_null($userAgent)) {
					if ($this->crawlerDetect->isCrawler($userAgent)) {
						$ssrHTML = $this->serverSideHTML($url);

						if (!is_null($ssrHTML)) {
							return new Response($ssrHTML);
						}
					}
				}
			}
		}

		return new Response($this->twig->render("react.html.twig", Twig::param($parameters)));
	}

	public function serverSideHTML(string $url, bool $ignoreCache = false): ?string {
		if (Util::isEmpty($this->prerenderKey)) return null;

		$cacheKey = "ssrHTML_" . urlencode($url);
		if (!$ignoreCache && CacheHandler::existsInCache($cacheKey)) {
			return CacheHandler::getFromCache($cacheKey);
		}

		$client = HttpClientFactory::create();

		// https://www.prerender.cloud/docs/api/examples
		$response = $client->request("GET", "https://service.prerender.cloud/" . $url, [
			"headers" => [
				"X-Prerender-Token" => $this->prerenderKey
			]
		]);

		$body = $response->getBody();
		if (!is_null($body)) {
			$content = $body->getContents();
			$body->close();

			if (!is_null($content)) {
				CacheHandler::setToCache($cacheKey, $content, 10 * 60);
				return $content;
			}
		}
	}
}
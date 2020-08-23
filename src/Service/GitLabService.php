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

namespace qpost\Service;

use GuzzleHttp\Client;
use qpost\Cache\CacheHandler;
use qpost\Factory\HttpClientFactory;
use function is_array;
use function is_null;
use function json_decode;
use function sprintf;

class GitLabService {
	/**
	 * @var Client $httpClient
	 */
	private $httpClient;

	/**
	 * @var string $gitLabHost
	 */
	private $gitLabHost;

	/**
	 * @var string $gitLabProjectId
	 */
	private $gitLabProjectId;

	/**
	 * @var string $gitLabToken
	 */
	private $gitLabToken;

	public function __construct() {
		$this->httpClient = HttpClientFactory::create();

		$this->gitLabHost = $_ENV["GITLAB_HOST"];
		$this->gitLabProjectId = $_ENV["GITLAB_PROJECT_ID"];
		$this->gitLabToken = $_ENV["GITLAB_TOKEN"];
	}

	/**
	 * @return string|null
	 * @author Mehdi Baaboura <mbaaboura@gigadrivegroup.com>
	 */
	public function getReleasesListURL(): ?string {
		$n = "gitLabProjectURL";
		$url = null;
		if (CacheHandler::existsInCache($n)) {
			$url = CacheHandler::getFromCache($n);
		} else {
			$url = $this->fetchProjectURL();

			if (!is_null($url)) {
				CacheHandler::setToCache($n, $url, 30 * 60);
			}
		}

		return !is_null($url) ? $url . "/-/releases" : "";
	}

	/**
	 * Fetches the web URL for the configured GitLab repository.
	 * @return string|null
	 * @author Mehdi Baaboura <mbaaboura@gigadrivegroup.com>
	 */
	public function fetchProjectURL(): ?string {
		$url = sprintf("https://%s/api/v4/projects/%s", $this->gitLabHost, $this->gitLabProjectId);

		$response = $this->httpClient->get($url, [
			"headers" => [
				"Authorization" => sprintf("Bearer %s", $this->gitLabToken)
			]
		]);

		$body = $response->getBody();
		if (is_null($body)) return null;

		$content = $body->getContents();
		$body->close();
		if (is_null($content)) return null;

		$data = @json_decode($content, true);
		if (!$data) return null;

		return isset($data["web_url"]) ? $data["web_url"] : null;
	}

	/**
	 * Fetches the latest releases from the configured GitLab repository.
	 * @return array|null
	 * @author Mehdi Baaboura <mbaaboura@gigadrivegroup.com>
	 */
	public function fetchReleases(): ?array {
		$url = sprintf("https://%s/api/v4/projects/%s/repository/tags", $this->gitLabHost, $this->gitLabProjectId);

		$response = $this->httpClient->get($url, [
			"headers" => [
				"Authorization" => sprintf("Bearer %s", $this->gitLabToken)
			]
		]);

		$body = $response->getBody();
		if (is_null($body)) return null;

		$content = $body->getContents();
		$body->close();
		if (is_null($content)) return null;

		$data = @json_decode($content, true);
		if (!$data) return null;

		return is_array($data) ? $data : null;
	}
}
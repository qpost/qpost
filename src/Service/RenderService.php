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

use qpost\Twig\Twig;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

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

	public function __construct(Environment $twig, RequestStack $requestStack) {
		$this->twig = $twig;
		$this->requestStack = $requestStack;
		$this->currentRequest = $requestStack->getCurrentRequest();
	}

	public function react(array $parameters = [], bool $ignoreCrawlerCheck = false): Response {
		if (!$ignoreCrawlerCheck) {
			// TODO: Check if user is crawler, render server-side
		}

		return new Response($this->twig->render("react.html.twig", Twig::param($parameters)));
	}
}
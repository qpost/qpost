<?php
/**
 * Copyright (C) 2018-2019 Gigadrive - All rights reserved.
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

namespace qpost\Controller;

use Exception;
use qpost\Constants\MiscConstants;
use qpost\Service\AuthorizationService;
use qpost\Service\PostRequestService;
use qpost\Twig\Twig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HomeController extends AbstractController {
	/**
	 * @Route("/")
	 *
	 * @param Request $request
	 * @param PostRequestService $postRequestService
	 * @return Response
	 * @throws Exception
	 */
	public function index(Request $request, PostRequestService $postRequestService) {
		$user = $this->getUser();

		if ($user) {
			return $this->render("react.html.twig", Twig::param());
		} else {
			$postRequestService->handleRegistration($this, $request);

			$isMobile = $request->headers->has("Q-User-Agent") && $request->headers->get("Q-User-Agent") === "android";

			return $this->render($isMobile === false ? "pages/home/index.html.twig" : "pages/mobile/register.html.twig", Twig::param([
				"description" => "A social microblogging network that helps you share your thoughts online, protected by freedom of speech.",
				"bigSocialImage" => $this->generateUrl("qpost_home_index", [], UrlGeneratorInterface::ABSOLUTE_URL) . "assets/img/bigSocialImage-default.png",
				"twitterImage" => $this->generateUrl("qpost_home_index", [], UrlGeneratorInterface::ABSOLUTE_URL) . "assets/img/favicon-512.png",
				MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_home_index", [], UrlGeneratorInterface::ABSOLUTE_URL)
			]));
		}
	}

	public function csrf(string $id, ?string $token): bool {
		return $this->isCsrfTokenValid($id, $token);
	}

	public function flash(string $type, string $message) {
		$this->addFlash($type, $message);
	}

	public function view(string $view, array $parameters = []): string {
		return $this->renderView($view, $parameters);
	}

	public function url(string $route, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string {
		return $this->generateUrl($route, $parameters, $referenceType);
	}
}

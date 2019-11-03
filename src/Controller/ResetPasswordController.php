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

use Doctrine\ORM\EntityManagerInterface;
use qpost\Constants\MiscConstants;
use qpost\Service\AuthorizationService;
use qpost\Twig\Twig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResetPasswordController extends AbstractController {
	/**
	 * @Route("/reset-password")
	 *
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @return Response
	 */
	public function index(Request $request, EntityManagerInterface $entityManager) {
		$authService = new AuthorizationService($request, $entityManager);

		if (!$authService->isAuthorized()) {
			return $this->render("pages/resetPassword.html.twig", Twig::param([
				"title" => "Reset your password",
				MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_resetpassword_index", [], UrlGeneratorInterface::ABSOLUTE_URL)
			]));
		} else {
			return $this->redirectToRoute("qpost_home_index");
		}
	}
}
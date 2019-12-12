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

use qpost\Twig\Twig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class RedirectController extends AbstractController {
	/**
	 * @Route("/terms")
	 *
	 * @return RedirectResponse
	 */
	public function terms() {
		return $this->render("pages/terms.html.twig", Twig::param([
			"title" => "Terms of Service"
		]));
//		return $this->redirect("https://go.qpo.st/terms");
	}

	/**
	 * @Route("/privacy")
	 *
	 * @return RedirectResponse
	 */
	public function privacy() {
		return $this->render("pages/privacy.html.twig", Twig::param([
			"title" => "Privacy Policy"
		]));
//		return $this->redirect("https://go.qpo.st/privacy");
	}

	/**
	 * @Route("/disclaimer")
	 *
	 * @return RedirectResponse
	 */
	public function disclaimer() {
		return $this->render("pages/disclaimer.html.twig", Twig::param([
			"title" => "Disclaimer"
		]));
//		return $this->redirect("https://go.qpo.st/disclaimer");
	}

	/**
	 * @Route("/rules")
	 *
	 * @return RedirectResponse
	 */
	public function rules() {
		return $this->render("pages/rules.html.twig", Twig::param([
			"title" => "Rules and Guidelines"
		]));
//		return $this->redirect("https://go.qpo.st/rules");
	}

	/**
	 * @Route("/contact")
	 *
	 * @return RedirectResponse
	 */
	public function contact() {
		return $this->redirect("https://go.qpo.st/contact");
	}

	/**
	 * @Route("/help")
	 *
	 * @return RedirectResponse
	 */
	public function help() {
		return $this->redirect("https://go.qpo.st/help");
	}

	/**
	 * @Route("/advertise")
	 *
	 * @return RedirectResponse
	 */
	public function advertise() {
		return $this->redirect("https://go.qpo.st/advertise");
	}
}
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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class APIDocsController extends AbstractController {
	/**
	 * @Route("/apidocs")
	 * @return Response
	 */
	public function indexAction() {
		return $this->render("apidocs/index.html.twig", Twig::param([
			"title" => "API documentation"
		]));
	}

	/**
	 * @Route("/apidocs/introduction")
	 * @return Response
	 */
	public function introductionAction() {
		return $this->render("apidocs/introduction.html.twig", Twig::param([
			"title" => "Introduction - API documentation"
		]));
	}

	/**
	 * @Route("/apidocs/authentication")
	 * @return Response
	 */
	public function authenticationAction() {
		return $this->render("apidocs/authentication.html.twig", Twig::param([
			"title" => "Authentication - API documentation"
		]));
	}

	/**
	 * @Route("/apidocs/errors")
	 * @return Response
	 */
	public function errorsAction() {
		return $this->render("apidocs/errors.html.twig", Twig::param([
			"title" => "Errors - API documentation"
		]));
	}

	/**
	 * @Route("/apidocs/accountdata")
	 * @return Response
	 */
	public function accountDataAction() {
		return $this->render("apidocs/accountData.html.twig", Twig::param([
			"title" => "Account data - API documentation"
		]));
	}

	/**
	 * @Route("/apidocs/badgestatus")
	 * @return Response
	 */
	public function badgeStatusAction() {
		return $this->render("apidocs/badgeStatus.html.twig", Twig::param([
			"title" => "Badge status - API documentation"
		]));
	}

	/**
	 * @Route("/apidocs/birthday")
	 * @return Response
	 */
	public function birthdayAction() {
		return $this->render("apidocs/birthday.html.twig", Twig::param([
			"title" => "Birthday - API documentation"
		]));
	}

	/**
	 * @Route("/apidocs/block")
	 * @return Response
	 */
	public function blockAction() {
		return $this->render("apidocs/block.html.twig", Twig::param([
			"title" => "Block - API documentation"
		]));
	}

	/**
	 * @Route("/apidocs/favorite")
	 * @return Response
	 */
	public function favoriteAction() {
		return $this->render("apidocs/favorite.html.twig", Twig::param([
			"title" => "Favorite - API documentation"
		]));
	}

	/**
	 * @Route("/apidocs/feed")
	 * @return Response
	 */
	public function feedAction() {
		return $this->render("apidocs/feed.html.twig", Twig::param([
			"title" => "Feed - API documentation"
		]));
	}

	/**
	 * @Route("/apidocs/follow")
	 * @return Response
	 */
	public function followAction() {
		return $this->render("apidocs/follow.html.twig", Twig::param([
			"title" => "Follow - API documentation"
		]));
	}

	/**
	 * @Route("/apidocs/followersyouknow")
	 * @return Response
	 */
	public function followersYouKnowAction() {
		return $this->render("apidocs/followersYouKnow.html.twig", Twig::param([
			"title" => "Followers you know - API documentation"
		]));
	}

	/**
	 * @Route("/apidocs/followRequest")
	 * @return Response
	 */
	public function followRequestAction() {
		return $this->render("apidocs/followRequest.html.twig", Twig::param([
			"title" => "Follow request - API documentation"
		]));
	}

	/**
	 * @Route("/apidocs/notifications")
	 * @return Response
	 */
	public function notificationsAction() {
		return $this->render("apidocs/notifications.html.twig", Twig::param([
			"title" => "Notifications - API documentation"
		]));
	}

	/**
	 * @Route("/apidocs/password")
	 * @return Response
	 */
	public function passwordAction() {
		return $this->render("apidocs/password.html.twig", Twig::param([
			"title" => "Password - API documentation"
		]));
	}

	/**
	 * @Route("/apidocs/privacyLevel")
	 * @return Response
	 */
	public function privacyLevelAction() {
		return $this->render("apidocs/privacyLevel.html.twig", Twig::param([
			"title" => "Privacy level - API documentation"
		]));
	}

	/**
	 * @Route("/apidocs/reply")
	 * @return Response
	 */
	public function replyAction() {
		return $this->render("apidocs/reply.html.twig", Twig::param([
			"title" => "Reply - API documentation"
		]));
	}

	/**
	 * @Route("/apidocs/search")
	 * @return Response
	 */
	public function searchAction() {
		return $this->render("apidocs/search.html.twig", Twig::param([
			"title" => "Search - API documentation"
		]));
	}

	/**
	 * @Route("/apidocs/share")
	 * @return Response
	 */
	public function shareAction() {
		return $this->render("apidocs/share.html.twig", Twig::param([
			"title" => "Share - API documentation"
		]));
	}

	/**
	 * @Route("/apidocs/status")
	 * @return Response
	 */
	public function statusAction() {
		return $this->render("apidocs/status.html.twig", Twig::param([
			"title" => "Status - API documentation"
		]));
	}

	/**
	 * @Route("/apidocs/token")
	 * @return Response
	 */
	public function tokenAction() {
		return $this->render("apidocs/token.html.twig", Twig::param([
			"title" => "Token - API documentation"
		]));
	}

	/**
	 * @Route("/apidocs/trends")
	 * @return Response
	 */
	public function trendsAction() {
		return $this->render("apidocs/trends.html.twig", Twig::param([
			"title" => "Trends - API documentation"
		]));
	}

	/**
	 * @Route("/apidocs/user")
	 * @return Response
	 */
	public function userAction() {
		return $this->render("apidocs/user.html.twig", Twig::param([
			"title" => "User - API documentation"
		]));
	}

	/**
	 * @Route("/apidocs/username")
	 * @return Response
	 */
	public function usernameAction() {
		return $this->render("apidocs/username.html.twig", Twig::param([
			"title" => "Username - API documentation"
		]));
	}
}
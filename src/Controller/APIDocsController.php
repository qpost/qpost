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

use Gigadrive\Bundle\SymfonyExtensionsBundle\DependencyInjection\Util;
use qpost\Twig\Twig;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class APIDocsController extends qpostController {
	/**
	 * @Route("/apidocs")
	 * @return Response
	 */
	public function indexAction() {
		return $this->render("apidocs/index.html.twig", Twig::param([
			"title" => "API documentation",
			"description" => Util::limitString("The documentation for the official qpost REST API.", 160, true)
		]));
	}

	/**
	 * @Route("/apidocs/introduction")
	 * @return Response
	 */
	public function introductionAction() {
		return $this->render("apidocs/introduction.html.twig", Twig::param([
			"title" => "Introduction - API documentation",
			"description" => Util::limitString("An introduction to using the official qpost REST API.", 160, true)
		]));
	}

	/**
	 * @Route("/apidocs/authentication")
	 * @return Response
	 */
	public function authenticationAction() {
		return $this->render("apidocs/authentication.html.twig", Twig::param([
			"title" => "Authentication - API documentation",
			"description" => Util::limitString("An in-depth description about Authentication with the official qpost REST API.", 160, true)
		]));
	}

	/**
	 * @Route("/apidocs/errors")
	 * @return Response
	 */
	public function errorsAction() {
		return $this->render("apidocs/errors.html.twig", Twig::param([
			"title" => "Errors - API documentation",
			"description" => Util::limitString("A list on all possible errors for the official qpost REST API.", 160, true)
		]));
	}

	/**
	 * @Route("/apidocs/badgestatus")
	 * @return Response
	 */
	public function badgeStatusAction() {
		return $this->render("apidocs/badgeStatus.html.twig", Twig::param([
			"title" => "Badge status - API documentation",
			"description" => Util::limitString("An in-depth description about the Badge Status endpoint of the official qpost REST API.", 160, true)
		]));
	}

	/**
	 * @Route("/apidocs/birthday")
	 * @return Response
	 */
	public function birthdayAction() {
		return $this->render("apidocs/birthday.html.twig", Twig::param([
			"title" => "Birthday - API documentation",
			"description" => Util::limitString("", 160, true)
		]));
	}

	/**
	 * @Route("/apidocs/block")
	 * @return Response
	 */
	public function blockAction() {
		return $this->render("apidocs/block.html.twig", Twig::param([
			"title" => "Block - API documentation",
			"description" => Util::limitString("An in-depth description about the Block endpoint of the official qpost REST API.", 160, true)
		]));
	}

	/**
	 * @Route("/apidocs/favorite")
	 * @return Response
	 */
	public function favoriteAction() {
		return $this->render("apidocs/favorite.html.twig", Twig::param([
			"title" => "Favorite - API documentation",
			"description" => Util::limitString("An in-depth description about the Favorite endpoint of the official qpost REST API.", 160, true)
		]));
	}

	/**
	 * @Route("/apidocs/feed")
	 * @return Response
	 */
	public function feedAction() {
		return $this->render("apidocs/feed.html.twig", Twig::param([
			"title" => "Feed - API documentation",
			"description" => Util::limitString("An in-depth description about the Feed endpoint of the official qpost REST API.", 160, true)
		]));
	}

	/**
	 * @Route("/apidocs/follow")
	 * @return Response
	 */
	public function followAction() {
		return $this->render("apidocs/follow.html.twig", Twig::param([
			"title" => "Follow - API documentation",
			"description" => Util::limitString("An in-depth description about the Follow endpoint of the official qpost REST API.", 160, true)
		]));
	}

	/**
	 * @Route("/apidocs/followersyouknow")
	 * @return Response
	 */
	public function followersYouKnowAction() {
		return $this->render("apidocs/followersYouKnow.html.twig", Twig::param([
			"title" => "Followers you know - API documentation",
			"description" => Util::limitString("An in-depth description about the Followers you know endpoint of the official qpost REST API.", 160, true)
		]));
	}

	/**
	 * @Route("/apidocs/followRequest")
	 * @return Response
	 */
	public function followRequestAction() {
		return $this->render("apidocs/followRequest.html.twig", Twig::param([
			"title" => "Follow request - API documentation",
			"description" => Util::limitString("An in-depth description about the Follow Request endpoint of the official qpost REST API.", 160, true)
		]));
	}

	/**
	 * @Route("/apidocs/notifications")
	 * @return Response
	 */
	public function notificationsAction() {
		return $this->render("apidocs/notifications.html.twig", Twig::param([
			"title" => "Notifications - API documentation",
			"description" => Util::limitString("An in-depth description about the Notifications endpoint of the official qpost REST API.", 160, true)
		]));
	}

	/**
	 * @Route("/apidocs/reply")
	 * @return Response
	 */
	public function replyAction() {
		return $this->render("apidocs/reply.html.twig", Twig::param([
			"title" => "Reply - API documentation",
			"description" => Util::limitString("An in-depth description about the Reply endpoint of the official qpost REST API.", 160, true)
		]));
	}

	/**
	 * @Route("/apidocs/search")
	 * @return Response
	 */
	public function searchAction() {
		return $this->render("apidocs/search.html.twig", Twig::param([
			"title" => "Search - API documentation",
			"description" => Util::limitString("An in-depth description about the Search endpoint of the official qpost REST API.", 160, true)
		]));
	}

	/**
	 * @Route("/apidocs/share")
	 * @return Response
	 */
	public function shareAction() {
		return $this->render("apidocs/share.html.twig", Twig::param([
			"title" => "Share - API documentation",
			"description" => Util::limitString("An in-depth description about the Share endpoint of the official qpost REST API.", 160, true)
		]));
	}

	/**
	 * @Route("/apidocs/status")
	 * @return Response
	 */
	public function statusAction() {
		return $this->render("apidocs/status.html.twig", Twig::param([
			"title" => "Status - API documentation",
			"description" => Util::limitString("An in-depth description about the Status endpoint of the official qpost REST API.", 160, true)
		]));
	}

	/**
	 * @Route("/apidocs/token")
	 * @return Response
	 */
	public function tokenAction() {
		return $this->render("apidocs/token.html.twig", Twig::param([
			"title" => "Token - API documentation",
			"description" => Util::limitString("An in-depth description about the Token endpoint of the official qpost REST API.", 160, true)
		]));
	}

	/**
	 * @Route("/apidocs/trends")
	 * @return Response
	 */
	public function trendsAction() {
		return $this->render("apidocs/trends.html.twig", Twig::param([
			"title" => "Trends - API documentation",
			"description" => Util::limitString("An in-depth description about the Trends endpoint of the official qpost REST API.", 160, true)
		]));
	}

	/**
	 * @Route("/apidocs/user")
	 * @return Response
	 */
	public function userAction() {
		return $this->render("apidocs/user.html.twig", Twig::param([
			"title" => "User - API documentation",
			"description" => Util::limitString("An in-depth description about the User endpoint of the official qpost REST API.", 160, true)
		]));
	}
}
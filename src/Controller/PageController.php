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
use qpost\Entity\FeedEntry;
use qpost\Entity\MediaFile;
use qpost\Entity\User;
use qpost\Service\AuthorizationService;
use qpost\Twig\Twig;
use qpost\Util\Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PageController extends AbstractController {
	/**
	 * @Route("/status/{id}")
	 *
	 * @param int $id
	 * @param EntityManagerInterface $entityManager
	 * @return Response
	 */
	public function status(int $id, EntityManagerInterface $entityManager) {
		/**
		 * @var FeedEntry $feedEntry
		 */
		$feedEntry = $entityManager->getRepository(FeedEntry::class)->findOneBy([
			"id" => $id
		]);

		if (!is_null($feedEntry)) {
			$user = $feedEntry->getUser();

			$title = $user->getDisplayName() . " on qpost";

			$text = $feedEntry->getText();
			if ($text) {
				$title .= ": \"" . Util::limitString($text, 40, true) . "\"";
			}

			$bigSocialImage = $this->generateUrl("qpost_home_index", [], UrlGeneratorInterface::ABSOLUTE_URL) . "assets/img/bigSocialImage-default.png";

			/**
			 * @var MediaFile $mediaFile
			 */
			$mediaFile = $feedEntry->getAttachments()->first();
			if ($mediaFile) {
				$bigSocialImage = $mediaFile->getURL();
			}

			return $this->render("react.html.twig", Twig::param([
				"title" => $title,
				"twitterImage" => $user->getAvatarURL(),
				"bigSocialImage" => $bigSocialImage,
				"description" => $feedEntry->getText(),
				MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_status", ["id" => $id], UrlGeneratorInterface::ABSOLUTE_URL)
			]));
		}

		throw $this->createNotFoundException("Invalid status ID.");
	}

	/**
	 * @Route("/about")
	 *
	 * @return Response
	 */
	public function about() {
		return $this->render("react.html.twig", Twig::param([
			"title" => "About",
			"description" => "Basic information about qpost",
			"bigSocialImage" => $this->generateUrl("qpost_home_index", [], UrlGeneratorInterface::ABSOLUTE_URL) . "assets/img/bigSocialImage-default.png",
			"twitterImage" => $this->generateUrl("qpost_home_index", [], UrlGeneratorInterface::ABSOLUTE_URL) . "assets/img/bigSocialImage-default.png",
			MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_about", [], UrlGeneratorInterface::ABSOLUTE_URL)
		]));
	}

	/**
	 * @Route("/goodbye")
	 *
	 * @return Response
	 */
	public function goodbye() {
		return $this->render("react.html.twig", Twig::param([
			"title" => "Goodbye",
			"bigSocialImage" => $this->generateUrl("qpost_home_index", [], UrlGeneratorInterface::ABSOLUTE_URL) . "assets/img/bigSocialImage-default.png",
			"twitterImage" => $this->generateUrl("qpost_home_index", [], UrlGeneratorInterface::ABSOLUTE_URL) . "assets/img/favicon-512.png",
			MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_goodbye", [], UrlGeneratorInterface::ABSOLUTE_URL)
		]));
	}

	/**
	 * @Route("/search")
	 *
	 * @return Response
	 */
	public function search() {
		return $this->render("react.html.twig", Twig::param([
			"title" => "Search",
			"bigSocialImage" => $this->generateUrl("qpost_home_index", [], UrlGeneratorInterface::ABSOLUTE_URL) . "assets/img/bigSocialImage-default.png",
			"twitterImage" => $this->generateUrl("qpost_home_index", [], UrlGeneratorInterface::ABSOLUTE_URL) . "assets/img/favicon-512.png",
			MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_search", [], UrlGeneratorInterface::ABSOLUTE_URL)
		]));
	}

	/**
	 * @Route("/offline.html")
	 *
	 * @return Response
	 */
	public function offline() {
		return $this->render("pages/offline.html.twig", Twig::param([
			"title" => "Offline",
			MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_offline", [], UrlGeneratorInterface::ABSOLUTE_URL)
		]));
	}

	/**
	 * @Route("/hashtag/{tag}")
	 *
	 * @param string $tag
	 * @return Response
	 */
	public function hashtag(string $tag) {
		return $this->forward("qpost\Controller\PageController::search");
	}

	/**
	 * @Route("/edit")
	 *
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @return RedirectResponse|Response
	 */
	public function edit(Request $request, EntityManagerInterface $entityManager) {
		$user = $this->getUser();

		if ($user) {
			return $this->render("react.html.twig", Twig::param([
				MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_edit", [], UrlGeneratorInterface::ABSOLUTE_URL)
			]));
		} else {
			return $this->redirect($this->generateUrl("qpost_login_index"));
		}
	}

	/**
	 * @Route("/notifications")
	 *
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @return RedirectResponse|Response
	 */
	public function notifications(Request $request, EntityManagerInterface $entityManager) {
		$user = $this->getUser();

		if ($user) {
			return $this->render("react.html.twig", Twig::param([
				MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_notifications", [], UrlGeneratorInterface::ABSOLUTE_URL)
			]));
		} else {
			return $this->redirect($this->generateUrl("qpost_login_index"));
		}
	}

	/**
	 * @Route("/messages")
	 *
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @return RedirectResponse|Response
	 */
	public function messages(Request $request, EntityManagerInterface $entityManager) {
		$user = $this->getUser();

		if ($user) {
			return $this->render("react.html.twig", Twig::param([
				MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_messages", [], UrlGeneratorInterface::ABSOLUTE_URL)
			]));
		} else {
			return $this->redirect($this->generateUrl("qpost_login_index"));
		}
	}

	/**
	 * @Route("/account")
	 *
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @return RedirectResponse|Response
	 */
	public function account(Request $request, EntityManagerInterface $entityManager) {
		$user = $this->getUser();

		if ($user) {
			return $this->render("react.html.twig", Twig::param([
				MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_account", [], UrlGeneratorInterface::ABSOLUTE_URL)
			]));
		} else {
			return $this->redirect($this->generateUrl("qpost_login_index"));
		}
	}

	/**
	 * @Route("/account/privacy")
	 *
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @return RedirectResponse|Response
	 */
	public function accountPrivacy(Request $request, EntityManagerInterface $entityManager) {
		$user = $this->getUser();

		if ($user) {
			return $this->render("react.html.twig", Twig::param([
				MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_accountprivacy", [], UrlGeneratorInterface::ABSOLUTE_URL)
			]));
		} else {
			return $this->redirect($this->generateUrl("qpost_login_index"));
		}
	}

	/**
	 * @Route("/account/privacy/blocked")
	 *
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @return RedirectResponse|Response
	 */
	public function accountPrivacyBlocked(Request $request, EntityManagerInterface $entityManager) {
		$user = $this->getUser();

		if ($user) {
			return $this->render("react.html.twig", Twig::param([
				MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_accountprivacyblocked", [], UrlGeneratorInterface::ABSOLUTE_URL)
			]));
		} else {
			return $this->redirect($this->generateUrl("qpost_login_index"));
		}
	}

	/**
	 * @Route("/account/privacy/level")
	 *
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @return RedirectResponse|Response
	 */
	public function accountPrivacyLevel(Request $request, EntityManagerInterface $entityManager) {
		$user = $this->getUser();

		if ($user) {
			return $this->render("react.html.twig", Twig::param([
				MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_accountprivacylevel", [], UrlGeneratorInterface::ABSOLUTE_URL)
			]));
		} else {
			return $this->redirect($this->generateUrl("qpost_login_index"));
		}
	}

	/**
	 * @Route("/account/privacy/requests")
	 *
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @return RedirectResponse|Response
	 */
	public function accountPrivacyRequests(Request $request, EntityManagerInterface $entityManager) {
		$user = $this->getUser();

		if ($user) {
			return $this->render("react.html.twig", Twig::param([
				MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_accountprivacyrequests", [], UrlGeneratorInterface::ABSOLUTE_URL)
			]));
		} else {
			return $this->redirect($this->generateUrl("qpost_login_index"));
		}
	}

	/**
	 * @Route("/account/sessions")
	 *
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @return RedirectResponse|Response
	 */
	public function accountSessions(Request $request, EntityManagerInterface $entityManager) {
		$user = $this->getUser();

		if ($user) {
			return $this->render("react.html.twig", Twig::param([
				MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_accountsessions", [], UrlGeneratorInterface::ABSOLUTE_URL)
			]));
		} else {
			return $this->redirect($this->generateUrl("qpost_login_index"));
		}
	}

	/**
	 * @Route("/account/username")
	 *
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @return RedirectResponse|Response
	 */
	public function accountUsername(Request $request, EntityManagerInterface $entityManager) {
		$user = $this->getUser();

		if ($user) {
			return $this->render("react.html.twig", Twig::param([
				MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_accountusername", [], UrlGeneratorInterface::ABSOLUTE_URL)
			]));
		} else {
			return $this->redirect($this->generateUrl("qpost_login_index"));
		}
	}

	/**
	 * @Route("/account/password")
	 *
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @return RedirectResponse|Response
	 */
	public function accountPassword(Request $request, EntityManagerInterface $entityManager) {
		$user = $this->getUser();

		if ($user) {
			if (!$user->getGigadriveData()) {
				return $this->render("react.html.twig", Twig::param([
					MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_accountpassword", [], UrlGeneratorInterface::ABSOLUTE_URL)
				]));
			} else {
				return $this->redirect($this->generateUrl("qpost_page_account"));
			}
		} else {
			return $this->redirect($this->generateUrl("qpost_login_index"));
		}
	}

	/**
	 * @Route("/account/delete")
	 *
	 * @param Request $request
	 * @param EntityManagerInterface $entityManager
	 * @return RedirectResponse|Response
	 */
	public function accountDelete(Request $request, EntityManagerInterface $entityManager) {
		$user = $this->getUser();

		if ($user) {
			return $this->render("react.html.twig", Twig::param([
				MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_accountdelete", [], UrlGeneratorInterface::ABSOLUTE_URL)
			]));
		} else {
			return $this->redirect($this->generateUrl("qpost_login_index"));
		}
	}

	public function profile(string $username, EntityManagerInterface $entityManager) {
		$user = $entityManager->getRepository(User::class)->getUserByUsername($username);

		if (!is_null($user)) {
			return $this->render("react.html.twig", Twig::param([
				"title" => $user->getDisplayName() . " (@" . $user->getUsername() . ")",
				"description" => $user->getBio(),
				"twitterImage" => $user->getAvatarURL(),
				"bigSocialImage" => $user->getAvatarURL(),
				MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_profile", ["username" => $username], UrlGeneratorInterface::ABSOLUTE_URL)
			]));
		} else {
			throw $this->createNotFoundException("Invalid username.");
		}
	}
}

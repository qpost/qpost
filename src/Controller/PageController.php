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

namespace qpost\Controller;

use Doctrine\ORM\EntityManagerInterface;
use qpost\Constants\MiscConstants;
use qpost\Entity\FeedEntry;
use qpost\Entity\MediaFile;
use qpost\Entity\User;
use qpost\Service\APIService;
use qpost\Service\AuthorizationService;
use qpost\Service\RenderService;
use qpost\Twig\Twig;
use qpost\Util\Util;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function is_null;
use function trim;

class PageController extends AbstractController {
	/**
	 * @Route("/status/{id}")
	 *
	 * @param int $id
	 * @param EntityManagerInterface $entityManager
	 * @param RenderService $renderService
	 * @param APIService $apiService
	 * @return Response
	 */
	public function status(int $id, EntityManagerInterface $entityManager, RenderService $renderService, APIService $apiService) {
		/**
		 * @var FeedEntry $feedEntry
		 */
		$feedEntry = $entityManager->getRepository(FeedEntry::class)->findOneBy([
			"id" => $id
		]);

		if (!is_null($feedEntry) && $apiService->mayView($feedEntry)) {
			$user = $feedEntry->getUser();

			$title = $user->getDisplayName() . " on qpost";

			$text = $feedEntry->getText();
			if ($text) {
				$title .= ": \"" . Util::limitString($text, 40, true) . "\"";
			}

			$bigSocialImage = $this->generateUrl("qpost_home_index", [], UrlGeneratorInterface::ABSOLUTE_URL) . "assets/img/bigSocialImage-default.png";

			$twitterCardType = "summary";

			/**
			 * @var MediaFile $mediaFile
			 */
			$mediaFile = $feedEntry->getAttachments()->first();
			if ($mediaFile) {
				$twitterCardType = "summary_large_image";
				$bigSocialImage = $mediaFile->getURL();
			}

			$emptyText = is_null($text) || empty($text) || trim($text) === "";

			$replies = $feedEntry->getReplyCount();
			$shares = $feedEntry->getShareCount();
			$favorites = $feedEntry->getFavoriteCount();

			return $renderService->react([
				"title" => $title,
				"twitterImage" => $user->getAvatarURL(),
				"bigSocialImage" => $bigSocialImage,
				"description" => Util::limitString(($emptyText ? "" : ($text . ". ")) . " Post by " . $user->getDisplayName() . "(@" . $user->getUsername() . "). " . $replies . " repl" . ($replies === 1 ? "y" : "ies") . ", " . $shares . " share" . ($shares === 1 ? "" : "s") . " and " . $favorites . " favorite" . ($favorites === 1 ? "" : "s") . ".", MiscConstants::META_DESCRIPTION_LENGTH, true),
				"twitterCardType" => $twitterCardType,
				MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_status", ["id" => $id], UrlGeneratorInterface::ABSOLUTE_URL)
			]);
		}

		throw $this->createNotFoundException("Invalid status ID.");
	}

	/**
	 * @Route("/goodbye")
	 *
	 * @param RenderService $renderService
	 * @return Response
	 */
	public function goodbye(RenderService $renderService) {
		return $renderService->react([
			"title" => "Goodbye",
			"bigSocialImage" => $this->generateUrl("qpost_home_index", [], UrlGeneratorInterface::ABSOLUTE_URL) . "assets/img/bigSocialImage-default.png",
			"twitterImage" => $this->generateUrl("qpost_home_index", [], UrlGeneratorInterface::ABSOLUTE_URL) . "assets/img/favicon-512.png",
			MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_goodbye", [], UrlGeneratorInterface::ABSOLUTE_URL)
		]);
	}

	/**
	 * @Route("/search")
	 *
	 * @param RenderService $renderService
	 * @return Response
	 */
	public function search(RenderService $renderService) {
		return $renderService->react([
			"title" => "Search",
			"bigSocialImage" => $this->generateUrl("qpost_home_index", [], UrlGeneratorInterface::ABSOLUTE_URL) . "assets/img/bigSocialImage-default.png",
			"twitterImage" => $this->generateUrl("qpost_home_index", [], UrlGeneratorInterface::ABSOLUTE_URL) . "assets/img/favicon-512.png",
			MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_search", [], UrlGeneratorInterface::ABSOLUTE_URL)
		]);
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
	 * @Route("/notifications")
	 *
	 * @param RenderService $renderService
	 * @return RedirectResponse|Response
	 */
	public function notifications(RenderService $renderService) {
		$user = $this->getUser();

		if ($user) {
			return $renderService->react([
				MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_notifications", [], UrlGeneratorInterface::ABSOLUTE_URL)
			]);
		} else {
			return $this->redirect($this->generateUrl("qpost_login_index"));
		}
	}

	/**
	 * @Route("/messages")
	 *
	 * @param RenderService $renderService
	 * @return RedirectResponse|Response
	 */
	public function messages(RenderService $renderService) {
		$user = $this->getUser();

		if ($user) {
			return $renderService->react([
				MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_messages", [], UrlGeneratorInterface::ABSOLUTE_URL)
			]);
		} else {
			return $this->redirect($this->generateUrl("qpost_login_index"));
		}
	}

	public function profile(string $username, EntityManagerInterface $entityManager, RenderService $renderService) {
		$user = $entityManager->getRepository(User::class)->getUserByUsername($username);

		if (!is_null($user)) {
			return $renderService->react([
				"title" => $user->getDisplayName() . " (@" . $user->getUsername() . ")",
				"description" => Util::limitString("Check the latest posts from " . $user->getDisplayName() . " (@" . $user->getUsername() . "). " . $user->getBio(), MiscConstants::META_DESCRIPTION_LENGTH, true),
				"twitterImage" => $user->getAvatarURL(),
				"bigSocialImage" => $user->getAvatarURL(),
				MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_page_profile", ["username" => $username], UrlGeneratorInterface::ABSOLUTE_URL)
			]);
		} else {
			throw $this->createNotFoundException("Invalid username.");
		}
	}
}

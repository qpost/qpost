<?php

namespace qpost\Router;

use qpost\Database\EntityManager;
use qpost\Feed\FeedEntry;
use qpost\Navigation\NavPoint;
use qpost\Util\Util;

create_route("/status/:id", function ($params) {
	$id = $params["id"];

	if(!Util::isEmpty($id) && is_numeric($id)){
		/**
		 * @var FeedEntry $post
		 */
		$post = EntityManager::instance()->getRepository(FeedEntry::class)->findOneBy([
			"id" => $id
		]);

		if(!is_null($post) && !is_null($post->getUser())){
			if($post->getType() == "POST" && $post->mayView()){
				$user = $post->getUser();
				$_SESSION["profileLoadPost"] = $post->getId();

				if(!is_null($post->getAttachments()) && is_array($post->getAttachments()) && count($post->getAttachments()) > 0){
					foreach ($post->getAttachments() as $attachment) {
						$mediaFile = $attachment->getMediaFile();

						if($mediaFile->getType() == "IMAGE"){
							$bigSocialImage = $mediaFile->getURL();
							break;
						}
					}
				}

				return twig_render("pages/profile/feed.html.twig", [
					"title" => $post->getUser()->getDisplayName() . " on qpost: \"" . Util::limitString($post->getText(),34,true) . "\"",
					"originalTitle" => $user->getDisplayName() . " (@" . $user->getUsername() . ")",
					"nav" => Util::isLoggedIn() && $user->getId() == Util::getCurrentUser()->getId() ? NavPoint::PROFILE : null,
					"user" => $user,
					"socialImage" => $user->getAvatarURL(),
					"profileTab" => "FEED",
					"currentPage" => 1,
					"description" => Util::limitString($post->getText(),150,true),
					"bigSocialImage" => isset($bigSocialImage) ? $bigSocialImage : null
				]);

				//return $this->reroute("/" . $post->getUser()->getUsername());
			}
		}
	}
});
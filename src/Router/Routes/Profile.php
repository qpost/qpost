<?php

namespace qpost\Router;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\QueryBuilder;
use Lime\App;
use qpost\Account\Follower;
use qpost\Account\PrivacyLevel;
use qpost\Account\ProfileViewStatus;
use qpost\Account\User;
use qpost\Block\Block;
use qpost\Database\EntityManager;
use qpost\Feed\FeedEntry;
use qpost\Feed\FeedEntryType;
use qpost\Navigation\NavPoint;
use qpost\Util\Util;

function find_user($query): ?User {
	if (!Util::isEmpty($query)) {
		$user = EntityManager::instance()->getRepository(User::class)->findOneBy([
			"username" => $query
		]);

		# TODO: Also check for IDs

		if ($user instanceof User) {
			return $user;
		}
	}

	return null;
}

function profile_view_status(User $user): int {
	$currentUser = Util::getCurrentUser();

	if ($user->isSuspended()) {
		return ProfileViewStatus::SUSPENDED;
	}

	if (!$user->isEmailActivated()) {
		return ProfileViewStatus::EMAIL_NOT_ACTIVATED;
	}

	if (Util::isLoggedIn() && Block::hasBlocked($user, $currentUser)) {
		return ProfileViewStatus::BLOCKED;
	}

	if ($user->getPrivacyLevel() == PrivacyLevel::CLOSED && (!Util::isLoggedIn() || $currentUser->getId() != $user->getId())) {
		return ProfileViewStatus::CLOSED;
	}

	if ($user->getPrivacyLevel() == PrivacyLevel::PRIVATE && (!Util::isLoggedIn() || ($currentUser->getId() != $user->getId() && !$currentUser->isFollowing($user)))) {
		return ProfileViewStatus::PRIVATE;
	}

	return ProfileViewStatus::OK;
}

function profile_handle_redirect(App $app, User $user): bool {
	if (profile_view_status($user) != ProfileViewStatus::OK) {
		$app->reroute("/" . $user->getUsername());
		return false;
	}

	return true;
}

function profile_fetch_feed_query(User $user): QueryBuilder {
	return EntityManager::instance()->getRepository(FeedEntry::class)->createQueryBuilder("f")
		->where("(f.post is null and f.type = :post) or (f.post is not null and f.type = :share) or (f.type = :newFollowing)")
		->setParameter("post", FeedEntryType::POST, Type::STRING)
		->setParameter("share", FeedEntryType::SHARE, Type::STRING)
		->setParameter("newFollowing", FeedEntryType::NEW_FOLLOWING, Type::STRING)
		->andWhere("f.user = :user")
		->setParameter("user", $user)
		->orderBy("f.time", "DESC");
}

function profile_fetch_feed_num(User $user): int {
	return profile_fetch_feed_query($user)
		->select("count(f.id)")
		->getQuery()
		->getResult()[0][1];
}

/**
 * @param User $user
 * @param int $currentPage
 * @return FeedEntry[]
 */
function profile_fetch_feed(User $user, int $currentPage) {
	$itemsPerPage = 40;
	$num = profile_fetch_feed_num($user);

	/**
	 * @var FeedEntry[] $feedEntries
	 */
	$feedEntries = [];

	if ($num > 0) {
		$feedEntries = profile_fetch_feed_query($user)
			->setFirstResult(($currentPage - 1) * $itemsPerPage)
			->setMaxResults($itemsPerPage)
			->getQuery()
			->getResult();
	}

	return $feedEntries;
}

/**
 * @param User $user
 * @param int $currentPage
 * @return User[]
 */
function profile_fetch_followers(User $user, int $currentPage) {
	$entityManager = EntityManager::instance();
	$itemsPerPage = 40;

	$num = $user->getFollowerCount();

	$users = [];

	if ($num > 0) {
		$users = [];

		/**
		 * @var Follower[] $followers
		 */
		$followers = $entityManager->getRepository(Follower::class)->createQueryBuilder("f")
			->where("f.to = :user")
			->setParameter("user", $user)
			->setFirstResult(($currentPage - 1) * $itemsPerPage)
			->setMaxResults($itemsPerPage)
			->getQuery()
			->getResult();

		foreach ($followers as $follower) {
			array_push($users, $follower->getFrom());
		}
	}

	return $users;
}

/**
 * @param User $user
 * @param int $currentPage
 * @return User[]
 */
function profile_fetch_following(User $user, int $currentPage) {
	$entityManager = EntityManager::instance();
	$itemsPerPage = 40;

	$num = $user->getFollowingCount();

	$users = [];

	if ($num > 0) {
		$users = [];

		/**
		 * @var Follower[] $followers
		 */
		$followers = $entityManager->getRepository(Follower::class)->createQueryBuilder("f")
			->where("f.from = :user")
			->setParameter("user", $user)
			->setFirstResult(($currentPage - 1) * $itemsPerPage)
			->setMaxResults($itemsPerPage)
			->getQuery()
			->getResult();

		foreach ($followers as $follower) {
			array_push($users, $follower->getTo());
		}
	}

	return $users;
}

function profile_follows_you(User $user): bool {
	$currentUser = Util::getCurrentUser();

	return !is_null($currentUser) && Follower::isFollowing($user, $currentUser);
}

create_route("/:query/following", function ($params) {
	$query = $params["query"];
	$page = 1;
	$user = find_user($query);

	if (!is_null($user)) {
		if ($query !== $user->getUsername()) {
			return $this->reroute("/" . $user->getUsername() . "/following");
		}

		if (profile_handle_redirect($this, $user)) {
			return twig_render("pages/profile/following.html.twig", [
				"title" => "People followed by " . $user->getUsername(),
				"nav" => Util::isLoggedIn() && $user->getId() == Util::getCurrentUser()->getId() ? NavPoint::PROFILE : null,
				"user" => $user,
				"socialImage" => $user->getAvatarURL(),
				"showProfile" => true,
				"profileTab" => "FOLLOWING",
				"currentPage" => $page,
				"description" => $user->getBio(),
				"users" => profile_fetch_following($user, $page),
				"followsYou" => profile_follows_you($user)
			]);
		}
	}
});

create_route("/:query/following/:page", function ($params) {
	$query = $params["query"];
	$page = is_numeric($params["page"]) && (int)$params["page"] > 0 ? (int)$params["page"] : 1;
	$user = find_user($query);

	if (!is_null($user)) {
		if ($query !== $user->getUsername()) {
			return $this->reroute("/" . $user->getUsername() . "/following/" . $page);
		}

		if (profile_handle_redirect($this, $user)) {
			return twig_render("pages/profile/following.html.twig", [
				"title" => "People followed by " . $user->getUsername(),
				"nav" => Util::isLoggedIn() && $user->getId() == Util::getCurrentUser()->getId() ? NavPoint::PROFILE : null,
				"user" => $user,
				"socialImage" => $user->getAvatarURL(),
				"showProfile" => true,
				"profileTab" => "FOLLOWING",
				"currentPage" => $page,
				"description" => $user->getBio(),
				"users" => profile_fetch_following($user, $page),
				"followsYou" => profile_follows_you($user)
			]);
		}
	}
});

create_route("/:query/followers", function ($params) {
	$query = $params["query"];
	$page = 1;
	$user = find_user($query);

	if (!is_null($user)) {
		if ($query !== $user->getUsername()) {
			return $this->reroute("/" . $user->getUsername() . "/followers");
		}

		if (profile_handle_redirect($this, $user)) {
			return twig_render("pages/profile/followers.html.twig", [
				"title" => "People following " . $user->getUsername(),
				"nav" => Util::isLoggedIn() && $user->getId() == Util::getCurrentUser()->getId() ? NavPoint::PROFILE : null,
				"user" => $user,
				"socialImage" => $user->getAvatarURL(),
				"showProfile" => true,
				"profileTab" => "FOLLOWERS",
				"currentPage" => $page,
				"description" => $user->getBio(),
				"users" => profile_fetch_followers($user, $page),
				"followsYou" => profile_follows_you($user)
			]);
		}
	}
});

create_route("/:query/followers/:page", function ($params) {
	$query = $params["query"];
	$page = is_numeric($params["page"]) && (int)$params["page"] > 0 ? (int)$params["page"] : 1;
	$user = find_user($query);

	if (!is_null($user)) {
		if ($query !== $user->getUsername()) {
			return $this->reroute("/" . $user->getUsername() . "/followers/" . $page);
		}

		if (profile_handle_redirect($this, $user)) {
			return twig_render("pages/profile/followers.html.twig", [
				"title" => "People following " . $user->getUsername(),
				"nav" => Util::isLoggedIn() && $user->getId() == Util::getCurrentUser()->getId() ? NavPoint::PROFILE : null,
				"user" => $user,
				"socialImage" => $user->getAvatarURL(),
				"showProfile" => true,
				"profileTab" => "FOLLOWERS",
				"currentPage" => $page,
				"description" => $user->getBio(),
				"users" => profile_fetch_followers($user, $page),
				"followsYou" => profile_follows_you($user)
			]);
		}
	}
});

create_route("/:query", function ($params) {
	$query = $params["query"];
	$page = 1;
	$user = find_user($query);

	if (!is_null($user)) {
		$viewStatus = profile_view_status($user);

		if ($viewStatus != ProfileViewStatus::EMAIL_NOT_ACTIVATED) {
			if ($query !== $user->getUsername()) {
				return $this->reroute("/" . $user->getUsername());
			}

			switch ($viewStatus) {
				case ProfileViewStatus::BLOCKED:
					return twig_render("pages/profile/blocked.html.twig", [
						"user" => $user,
						"socialImage" => $user->getAvatarURL(),
						"preventStatusModal" => true
					]);
				case ProfileViewStatus::PRIVATE:
					return twig_render("pages/profile/private.html.twig", [
						"user" => $user,
						"socialImage" => $user->getAvatarURL(),
						"preventStatusModal" => true
					]);
				case ProfileViewStatus::CLOSED:
					return twig_render("pages/profile/closed.html.twig", [
						"user" => $user,
						"socialImage" => $user->getAvatarURL(),
						"preventStatusModal" => true
					]);
				case ProfileViewStatus::SUSPENDED:
					return twig_render("pages/profile/suspended.html.twig", [
						"title" => "Account suspended",
						"user" => $user,
						"preventStatusModal" => true
					]);
				default:
					return twig_render("pages/profile/feed.html.twig", [
						"title" => $user->getDisplayName() . " (@" . $user->getUsername() . ")",
						"nav" => Util::isLoggedIn() && $user->getId() == Util::getCurrentUser()->getId() ? NavPoint::PROFILE : null,
						"user" => $user,
						"socialImage" => $user->getAvatarURL(),
						"showProfile" => true,
						"profileTab" => "FEED",
						"currentPage" => $page,
						"description" => $user->getBio(),
						"posts" => profile_fetch_feed($user, $page),
						"num" => profile_fetch_feed_num($user),
						"followsYou" => profile_follows_you($user)
					]);
			}
		}
	}
});

create_route("/:query/:page", function ($params) {
	$query = $params["query"];
	$page = is_numeric($params["page"]) && (int)$params["page"] > 0 ? (int)$params["page"] : 1;
	$user = find_user($query);

	if (!is_null($user)) {
		if ($query !== $user->getUsername()) {
			return $this->reroute("/" . $user->getUsername() . "/" . $page);
		}

		if (profile_handle_redirect($this, $user)) {
			return twig_render("pages/profile/feed.html.twig", [
				"title" => $user->getDisplayName() . " (@" . $user->getUsername() . ")",
				"nav" => Util::isLoggedIn() && $user->getId() == Util::getCurrentUser()->getId() ? NavPoint::PROFILE : null,
				"user" => $user,
				"socialImage" => $user->getAvatarURL(),
				"showProfile" => true,
				"profileTab" => "FEED",
				"currentPage" => $page,
				"description" => $user->getBio(),
				"posts" => profile_fetch_feed($user, $page),
				"num" => profile_fetch_feed_num($user),
				"followsYou" => profile_follows_you($user)
			]);
		}
	}
});
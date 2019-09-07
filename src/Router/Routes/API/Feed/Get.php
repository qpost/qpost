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

namespace qpost\Router\API\Feed;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\QueryBuilder;
use qpost\Account\PrivacyLevel;
use qpost\Account\User;
use qpost\Database\EntityManager;
use qpost\Feed\FeedEntry;
use qpost\Feed\FeedEntryType;
use qpost\Util\Method;
use function qpost\Router\API\api_create_route;
use function qpost\Router\API\api_get_token;
use function qpost\Router\API\api_prepare_object;
use function qpost\Router\API\api_request_data;

function home_feed_query(User $currentUser): QueryBuilder {
	return EntityManager::instance()->getRepository(FeedEntry::class)->createQueryBuilder("f")
		->innerJoin("f.user", "u")
		->where("u.privacyLevel != :closed")
		->setParameter("closed", PrivacyLevel::CLOSED, Type::STRING)
		->andWhere("f.post is null")
		->andWhere("f.type = :post or f.type = :share")
		->setParameter("post", FeedEntryType::POST, Type::STRING)
		->setParameter("share", FeedEntryType::SHARE, Type::STRING)
		->andWhere("exists (select 1 from qpost\Account\Follower ff where ff.to = :to) or f.user = :to")
		->setParameter("to", $currentUser)
		->orderBy("f.time", "DESC")
		->setMaxResults(30)
		->setCacheable(false);
}

function profile_feed_query(User $user): QueryBuilder {
	return EntityManager::instance()->getRepository(FeedEntry::class)->createQueryBuilder("f")
		->where("(f.post is null and f.type = :post) or (f.post is not null and f.type = :share) or (f.type = :newFollowing)")
		->setParameter("post", FeedEntryType::POST, Type::STRING)
		->setParameter("share", FeedEntryType::SHARE, Type::STRING)
		->setParameter("newFollowing", FeedEntryType::NEW_FOLLOWING, Type::STRING)
		->andWhere("f.user = :user")
		->setParameter("user", $user)
		->orderBy("f.time", "DESC")
		->setMaxResults(30)
		->setCacheable(false);
}

api_create_route(Method::GET, "/feed", function () {
	$token = api_get_token();
	$currentUser = !is_null($token) ? $token->getUser() : null;
	$requestData = api_request_data($this);

	if (isset($requestData["max"]) && !isset($requestData["user"])) {
		// Load older posts on home feed
		if (!is_null($currentUser)) {
			if (is_numeric($requestData["max"])) {
				$results = [];

				/**
				 * @var FeedEntry[] $feedEntries
				 */
				$feedEntries = home_feed_query($currentUser)
					->andWhere("f.id < :id")
					->setParameter("id", $requestData["max"], Type::INTEGER)
					->getQuery()
					->getResult();

				foreach ($feedEntries as $feedEntry) {
					if (!is_null($currentUser) && !$feedEntry->mayView($currentUser)) continue;
					array_push($results, api_prepare_object($feedEntry));
				}

				return json_encode(["results" => $results]);
			} else {
				$this->response->status = "400";
				return json_encode(["error" => "'max' has to be an integer."]);
			}
		} else {
			// Return error if there is no user as we need it to get the home feed entries
			$this->response->status = "401";
			return json_encode(["error" => "Invalid token"]);
		}
	} else if (!isset($requestData["max"]) && !isset($requestData["user"])) {
		// Load first posts on home feed
		if (!is_null($currentUser)) {
			$results = [];

			/**
			 * @var FeedEntry[] $feedEntries
			 */
			$feedEntries = home_feed_query($currentUser)
				->getQuery()
				->getResult();

			foreach ($feedEntries as $feedEntry) {
				if (!is_null($currentUser) && !$feedEntry->mayView($currentUser)) continue;
				array_push($results, api_prepare_object($feedEntry));
			}

			return json_encode(["results" => $results]);
		} else {
			// Return error if there is no user as we need it to get the home feed entries
			$this->response->status = "401";
			return json_encode(["error" => "Invalid token"]);
		}
	} else if (isset($requestData["max"]) && isset($requestData["user"])) {
		// Load older posts on profile page
		$user = User::getUserByID($requestData["user"]);

		if (!is_null($user) && $user->mayView($currentUser)) {
			if (is_numeric($requestData["max"])) {
				$results = [];

				/**
				 * @var FeedEntry[] $feedEntries
				 */
				$feedEntries = profile_feed_query($user)
					->andWhere("f.id < :id")
					->setParameter("id", $requestData["max"], Type::INTEGER)
					->getQuery()
					->getResult();

				foreach ($feedEntries as $feedEntry) {
					if (!is_null($currentUser) && !$feedEntry->mayView($currentUser)) continue;
					array_push($results, api_prepare_object($feedEntry));
				}

				return json_encode(["results" => $results]);
			} else {
				$this->response->status = "400";
				return json_encode(["error" => "'max' has to be an integer."]);
			}
		} else {
			$this->response->status = "404";
			return json_encode(["error" => "The requested user could not be found."]);
		}
	} else if (!isset($requestData["max"]) && isset($requestData["user"])) {
		// Load first posts on profile page
		$user = User::getUserByID($requestData["user"]);

		if (!is_null($user) && $user->mayView($currentUser)) {
			$results = [];

			/**
			 * @var FeedEntry[] $feedEntries
			 */
			$feedEntries = profile_feed_query($user)
				->getQuery()
				->getResult();

			foreach ($feedEntries as $feedEntry) {
				if (!is_null($currentUser) && !$feedEntry->mayView($currentUser)) continue;
				array_push($results, api_prepare_object($feedEntry));
			}

			return json_encode(["results" => $results]);
		} else {
			$this->response->status = "404";
			return json_encode(["error" => "The requested user could not be found."]);
		}
	} else {
		$this->response->status = "400";
		return json_encode(["error" => "Bad request"]);
	}
});
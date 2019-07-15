<?php

namespace qpost\Router\API\SuggestedUsers;

use Doctrine\DBAL\Types\Type;
use qpost\Account\User;
use qpost\Database\EntityManager;
use qpost\Util\Method;
use function qpost\Router\API\api_auth_check;
use function qpost\Router\API\api_create_route;
use function qpost\Router\API\api_get_token;
use function qpost\Router\API\api_prepare_object;

api_create_route(Method::GET, "/suggestedUsers", function () {
	if (api_auth_check($this)) {
		$token = api_get_token();
		$currentUser = $token->getUser();

		// query is a combination of https://stackoverflow.com/a/12915720 and https://stackoverflow.com/a/24165699
		/**
		 * @var User[] $suggestedUsers
		 */
		$suggestedUsers = EntityManager::instance()->getRepository(User::class)->createQueryBuilder("u")
			->innerJoin("u.followers", "t")
			->innerJoin("t.from", "their_friends")
			->innerJoin("their_friends.followers", "m")
			->innerJoin("m.from", "me")
			->where("u.id != :id")
			->setParameter("id", $currentUser->getId(), Type::INTEGER)
			->andWhere("u.emailActivated = :activated")
			->setParameter("activated", true, Type::BOOLEAN)
			->andWhere("me.id = :id")
			->setParameter("id", $currentUser->getId(), Type::INTEGER)
			->andWhere("their_friends.id != :id")
			->setParameter("id", $currentUser->getId(), Type::INTEGER)
			->andWhere("not exists (select 1 from qpost\Account\Follower f where f.from = :id and f.to = t.to)")
			->setParameter("id", $currentUser->getId(), Type::INTEGER)
			->groupBy("me.id, t.to")
			->setMaxResults(10)
			->getQuery()
			->getResult();

		$results = [];

		for ($i = 0; $i < count($suggestedUsers); $i++) {
			$user = $suggestedUsers[$i];
			if (!$user->mayView($currentUser)) {
				unset($suggestedUsers[$i]);
			}

			array_push($results, api_prepare_object($user));
		}

		return json_encode(["results" => $results]);
	} else {
		return "";
	}
});
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

namespace qpost\Repository;

use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Types\Type;
use qpost\Constants\MiscConstants;
use qpost\Constants\PrivacyLevel;
use qpost\Entity\User;
use function strtolower;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, User::class);
	}

	public function getUpcomingBirthdays(User $user, string $dateString): array {
		$date = new DateTime($dateString);

		$limit = new DateTime($dateString);
		$limit->add(DateInterval::createFromDateString("30 day"));

		return $this->createQueryBuilder("u")
			->where("u != :user")
			->innerJoin("u.followers", "f")
			->where("f.sender = :user")
			->setParameter("user", $user)
			->andWhere("u.birthday is not null")
			->andWhere("DAYOFYEAR(u.birthday) BETWEEN DAYOFYEAR(:date) AND DAYOFYEAR(:limit)")
			->setParameter("date", $date, Type::DATETIME)
			->setParameter("limit", $limit, Type::DATETIME)
			->setMaxResults(5)
			->setCacheable(true)
			->getQuery()
			->useQueryCache(true)
			->setResultCacheLifetime(MiscConstants::RESULT_CACHE_LIFETIME)
			->useResultCache(true)
			->getResult();
	}

	public function getSuggestedUsers(User $user): array {
		// query is a combination of https://stackoverflow.com/a/12915720 and https://stackoverflow.com/a/24165699
		return $this->createQueryBuilder("u")
			->innerJoin("u.followers", "t")
			->innerJoin("t.sender", "their_friends")
			->innerJoin("their_friends.followers", "m")
			->innerJoin("m.sender", "me")
			->where("u.id != :id")
			->setParameter("id", $user->getId(), Type::INTEGER)
			->andWhere("u.emailActivated = :activated")
			->setParameter("activated", true, Type::BOOLEAN)
			->andWhere("u.privacyLevel = :public")
			->setParameter("public", PrivacyLevel::PUBLIC, Type::STRING)
			->andWhere("me.id = :id")
			->setParameter("id", $user->getId(), Type::INTEGER)
			->andWhere("their_friends.id != :id")
			->setParameter("id", $user->getId(), Type::INTEGER)
			->andWhere("not exists (select 1 from qpost\Entity\Follower f where f.sender = :id and f.receiver = t.receiver)")
			->setParameter("id", $user->getId(), Type::INTEGER)
			->groupBy("me.id, t.receiver")
			->setMaxResults(10)
			->getQuery()
			->useQueryCache(true)
			->setResultCacheLifetime(MiscConstants::RESULT_CACHE_LIFETIME)
			->useResultCache(true)
			->getResult();
	}

	/**
	 * Checks whether a specific username is still available.
	 *
	 * @param string $username The username to look for.
	 * @return bool
	 */
	public function isUsernameAvailable(string $username): bool {
		$blacklist = ["about", "login", "logout", "nightmode", "account", "notifications", "messages", "profile", "terms", "tos", "privacy", "policy", "disclaimer", "edit", "search", "goodbye", "status", "api", "mehdi", "baaboura", "guidelines", "rules", "contact", "help", "support", "advertise", "download", "apidocs", "register", "reset-password", "verify-email", "reset-password-response"];
		if (in_array(strtolower($username), $blacklist)) return false;

		return $this->count(["username" => $username]) === 0;
	}

	/**
	 * Checks whether a specific email address is still available.
	 *
	 * @param string $email The email address to look for.
	 * @return bool
	 */
	public function isEmailAvailable(string $email): bool {
		return $this->count(["email" => $email]) === 0;
	}

	/**
	 * Gets a user by it's username (case-insenstive).
	 *
	 * @param string $username
	 * @return User|null
	 */
	public function getUserByUsername(string $username): ?User {
		return $this->createQueryBuilder("u")
			->where("upper(u.username) = upper(:username)")
			->setParameter("username", $username, Type::STRING)
			->setCacheable(true)
			->getQuery()
			->getOneOrNullResult();
	}

	/**
	 * Gets a user by it's id.
	 *
	 * @param int $id
	 * @return User|null
	 */
	public function getUserById(int $id): ?User {
		return $this->findOneBy([
			"id" => $id
		]);
	}

	public function getRecentCreatedAccounts(string $ip): int {
		$limit = new DateTime("-2 days");

		return $this->createQueryBuilder("u")
			->select("count(u.id)")
			->where("u.creationIP = :ip")
			->setParameter("ip", $ip, Type::STRING)
			->andWhere("u.time > :limit")
			->setParameter("limit", $limit, Type::DATETIME)
			->getQuery()
			->useQueryCache(true)
			->getSingleScalarResult();
	}
}

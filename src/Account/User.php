<?php

namespace qpost\Account;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping as ORM;
use qpost\Block\Block;
use qpost\Database\Database;
use qpost\Database\EntityManager;
use qpost\Feed\FeedEntry;
use qpost\Feed\Notification;
use qpost\Util\Util;

/**
 * Represents a user
 *
 * @package Account
 * @author Gigadrive (support@gigadrivegroup.com)
 * @copyright 2016-2018 Gigadrive
 * @link https://gigadrivegroup.com/dev/technologies
 *
 * @ORM\Entity
 */
class User {
	/**
	 * @param string $username
	 * @return bool
	 */
	public static function isUsernameAvailable(string $username): bool {
		return EntityManager::instance()->getRepository(User::class)->createQueryBuilder("u")
				->select("count(u.id)")
				->where("upper(u.username) = upper(:username)")
				->setParameter("username", $username, Type::STRING)
				->getQuery()
				->getResult()[0][1] == 0;
	}

	/**
	 * @param string $email
	 * @return bool
	 */
	public static function isEmailAvailable(string $email): bool {
		return EntityManager::instance()->getRepository(User::class)->createQueryBuilder("u")
				->select("count(u.id)")
				->where("upper(u.email) = upper(:email)")
				->setParameter("email", $email, Type::STRING)
				->getQuery()
				->getResult()[0][1] == 0;
	}

	/**
	 * @param mixed $query
	 * @return User|null
	 */
	public static function getUser($query): ?User {
		$entityManager = EntityManager::instance();

		return $entityManager->getRepository(User::class)->createQueryBuilder("u")
			->where("upper(u.username) = upper(:query)")
			->setParameter("query", $query, Type::STRING)
			->getQuery()
			->execute();
	}

	/**
	 * @access private
	 * @var int $id
	 *
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	private $id;

	/**
	 * @access private
	 * @var string $displayName
	 *
	 * @ORM\Column(type="string", length=25)
	 */
	private $displayName;

	/**
	 * @access private
	 * @var string $username
	 *
	 * @ORM\Column(type="string", unique=true, length=16)
	 */
	private $username;

	/**
	 * @access private
	 * @var string $password
	 *
	 * @ORM\Column(type="string", length=60, nullable=true)
	 */
	private $password;

	/**
	 * @access private
	 * @var string $email
	 *
	 * @ORM\Column(type="string", unique=true, length=50)
	 */
	private $email;

	/**
	 * @access private
	 * @var string $avatar
	 *
	 * @ORM\Column(type="string", nullable=true, length=255)
	 */
	private $avatar;

	/**
	 * @access private
	 * @var string $bio
	 *
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $bio;

	/**
	 * @access private
	 * @var DateTime $birthday
	 *
	 * @ORM\Column(type="date", nullable=true)
	 */
	private $birthday;

	/**
	 * @access private
	 * @var string $privacyLevel
	 *
	 * @ORM\Column(type="string")
	 */
	private $privacyLevel = PrivacyLevel::PUBLIC;

	/**
	 * @access private
	 * @var string $featuredBoxTitle
	 *
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $featuredBoxTitle;

	/**
	 * @access private
	 * @var User[] $featuredBoxContent
	 *
	 * @ORM\ManyToMany(targetEntity="User", mappedBy="featuringUsers")
	 */
	private $featuredBoxContent;

	/**
	 * @var User[] $featuringUsers
	 *
	 * @ORM\ManyToMany(targetEntity="User", inversedBy="featuredBoxContent")
	 * @ORM\JoinTable(name="featuredBoxes", joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
	 *                                        inverseJoinColumns={@ORM\JoinColumn(name="featured_user_id", referencedColumnName="id")})
	 */
	private $featuringUsers;

	/**
	 * @access private
	 * @var DateTime $time
	 *
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * @access private
	 * @var bool $emailActivated
	 *
	 * @ORM\Column(type="boolean")
	 */
	private $emailActivated = false;

	/**
	 * @access private
	 * @var string $emailActivationToken
	 *
	 * @ORM\Column(type="string", nullable=true, length=7)
	 */
	private $emailActivationToken;

	/**
	 * @access private
	 * @var bool $verified
	 *
	 * @ORM\Column(type="boolean")
	 */
	private $verified = false;

	/**
	 * @access private
	 * @var DateTime|null $lastUsernameChange
	 *
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	private $lastUsernameChange;

	/**
	 * @access private
	 * @var UserGigadriveData|null $gigadriveData
	 *
	 * @ORM\OneToOne(targetEntity="UserGigadriveData")
	 */
	private $gigadriveData;

	/**
	 * @var
	 *
	 * @ORM\OneToMany(targetEntity="Follower", mappedBy="from")
	 */
	private $following;

	/**
	 * @var
	 *
	 * @ORM\OneToMany(targetEntity="Follower", mappedBy="to")
	 */
	private $followers;

	public function __construct() {
		$this->featuredBoxContent = new ArrayCollection();
		$this->featuringUsers = new ArrayCollection();
		$this->following = new ArrayCollection();
		$this->followers = new ArrayCollection();
	}

	/**
	 * Returns the user's account ID
	 *
	 * @access public
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @param int $id
	 * @return User
	 */
	public function setId(int $id): self {
		$this->id = $id;
		return $this;
	}

	/**
	 * Returns the user's display name
	 *
	 * @access public
	 * @return string
	 */
	public function getDisplayName(): string {
		return Util::fixString($this->displayName);
	}

	/**
	 * @param string $displayName
	 * @return User
	 */
	public function setDisplayName(string $displayName): self {
		$this->displayName = $displayName;
		return $this;
	}

	/**
	 * Returns the user's username
	 *
	 * @access public
	 * @return string
	 */
	public function getUsername(): string {
		return $this->username;
	}

	/**
	 * @param string $username
	 * @return User
	 */
	public function setUsername(string $username): self {
		$this->username = $username;
		return $this;
	}

	/**
	 * Returns the user's password hash
	 *
	 * @access public
	 * @return string
	 */
	public function getPassword(): string {
		return $this->password;
	}

	/**
	 * @param string $password
	 * @return User
	 */
	public function setPassword(string $password): self {
		$this->password = $password;
		return $this;
	}

	/**
	 * Returns the user's email address
	 *
	 * @access public
	 * @return string
	 */
	public function getEmail(): string {
		return $this->email;
	}

	/**
	 * @param string $email
	 * @return User
	 */
	public function setEmail(string $email): self {
		$this->email = $email;
		return $this;
	}

	/**
	 * Returns the user's avatar URL
	 *
	 * @access public
	 * @return string
	 */
	public function getAvatarURL(): string {
		return is_null($this->avatar) ? sprintf(GIGADRIVE_CDN_UPLOAD_FINAL_URL, "defaultAvatar.png") : $this->avatar;
	}

	/**
	 * @param string|null $avatar
	 * @return User
	 */
	public function setAvatarURL(?string $avatar): self {
		$this->avatar = $avatar;
		return $this;
	}

	/**
	 * Returns the user's bio
	 *
	 * @access public
	 * @return string
	 */
	public function getBio(): ?string {
		return $this->bio;
	}

	/**
	 * @param string|null $bio
	 * @return User
	 */
	public function setBio(?string $bio): self {
		$this->bio = $bio;
		return $this;
	}

	/**
	 * Returns the user's birthday
	 *
	 * @access public
	 * @return DateTime|null
	 */
	public function getBirthday(): ?DateTime {
		return $this->birthday;
	}

	/**
	 * @param DateTime|null $birthday
	 * @return User
	 */
	public function setBirthday(?DateTime $birthday): self {
		$this->birthday = $birthday;
		return $this;
	}

	/**
	 * Returns the user's privacy level
	 *
	 * @access public
	 * @return string
	 */
	public function getPrivacyLevel(): string {
		return $this->privacyLevel;
	}

	/**
	 * @param string $privacyLevel
	 * @return User
	 */
	public function setPrivacyLevel(string $privacyLevel): self {
		if (PrivacyLevel::isValid($privacyLevel)) {
			$this->privacyLevel = $privacyLevel;
		}

		return $this;
	}

	/**
	 * Returns the title of the user's Featured box
	 *
	 * @access public
	 * @return string|null
	 */
	public function getFeaturedBoxTitle(): ?string {
		return $this->featuredBoxTitle;
	}

	/**
	 * @param string|null $featuredBoxTitle
	 * @return User
	 */
	public function setFeaturedBoxTitle(?string $featuredBoxTitle): self {
		$this->featuredBoxTitle = $featuredBoxTitle;
		return $this;
	}

	/**
	 * Returns an array of user IDs that are featured in the user's Featured box
	 *
	 * @access public
	 * @return Collection|User[]
	 */
	public function getFeaturedBoxContent(): Collection {
		return $this->featuredBoxContent;
	}

	/**
	 * @param User $user
	 * @return User
	 */
	public function addFeaturedBoxContent(User $user): self {
		if (!$this->featuredBoxContent->contains($user)) {
			$this->featuredBoxContent[] = $user;
			$user->featuringUsers[] = $this;
		}

		return $this;
	}

	/**
	 * @param User $user
	 * @return User
	 */
	public function removeFeaturedBoxContent(User $user): self {
		if ($this->featuredBoxContent->contains($user)) {
			$this->featuredBoxContent->removeElement($user);

			if ($user->featuringUsers->contains($this)) {
				$user->featuringUsers->removeElement($this);
			}
		}

		return $this;
	}

	/**
	 * @access public
	 * @return Collection|User[]
	 */
	public function getFeaturingUsers(): Collection {
		return $this->featuringUsers;
	}

	/**
	 * Returns the registration time for the user
	 *
	 * @access public
	 * @return DateTime
	 */
	public function getTime(): DateTime {
		return $this->time;
	}

	/**
	 * @param DateTime $time
	 * @return User
	 */
	public function setTime(DateTime $time): self {
		$this->time = $time;
		return $this;
	}

	/**
	 * Returns whether the user has activated their email
	 *
	 * @access public
	 * @return bool
	 */
	public function isEmailActivated(): bool {
		return $this->emailActivated;
	}

	/**
	 * @param bool $emailActivated
	 * @return User
	 */
	public function setEmailActivated(bool $emailActivated): self {
		$this->emailActivated = $emailActivated;
		return $this;
	}

	/**
	 * Returns the user's email activation token
	 *
	 * @access public
	 * @return string
	 */
	public function getEmailActivationToken(): string {
		return $this->emailActivationToken;
	}

	/**
	 * Returns whether the user is verified
	 *
	 * @access public
	 * @return bool
	 */
	public function isVerified(): bool {
		return $this->verified;
	}

	/**
	 * @param bool $verified
	 * @return User
	 */
	public function setVerified(bool $verified): self {
		$this->verified = $verified;
		return $this;
	}

	/**
	 * @param string $emailActivationToken
	 * @return User
	 */
	public function setEmailActivationToken(string $emailActivationToken): self {
		$this->emailActivationToken = $emailActivationToken;
		return $this;
	}

	/**
	 * Returns the timestamp of the last username change
	 *
	 * @access public
	 * @return DateTime|null
	 */
	public function getLastUsernameChange(): ?DateTime {
		return $this->lastUsernameChange;
	}

	/**
	 * @param DateTime|null $lastUsernameChange
	 * @return User
	 */
	public function setLastUsernameChange(?DateTime $lastUsernameChange): self {
		$this->lastUsernameChange = $lastUsernameChange;
		return $this;
	}

	/**
	 * @return UserGigadriveData|null
	 */
	public function getGigadriveData(): ?UserGigadriveData {
		return $this->gigadriveData;
	}

	/**
	 * @param UserGigadriveData $gigadriveData
	 * @return User
	 */
	public function setGigadriveData(UserGigadriveData $gigadriveData): self {
		$this->gigadriveData = $gigadriveData;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isSuspended(): bool {
		return EntityManager::instance()->getRepository(Suspension::class)->count([
				"target" => $this,
				"active" => true
			]) > 0;
	}

	/**
	 * @return int
	 */
	public function getPostCount(): int {
		$expr = Criteria::expr();
		$criteria = Criteria::create();

		return EntityManager::instance()->getRepository(FeedEntry::class)->matching(
			$criteria->where($expr->eq("user", $this))
				->andWhere($expr->eq("type", "POST"))
				->andWhere($expr->isNull("post"))
		)->count();
	}

	/**
	 * @return int
	 */
	public function getReplyCount(): int {
		$expr = Criteria::expr();
		$criteria = Criteria::create();

		return EntityManager::instance()->getRepository(FeedEntry::class)->matching(
			$criteria->where($expr->eq("user", $this))
				->andWhere($expr->eq("type", "POST"))
				->andWhere($expr->neq("post", null))
		)->count();
	}

	/**
	 * @return int
	 */
	public function getShareCount(): int {
		$expr = Criteria::expr();
		$criteria = Criteria::create();

		return EntityManager::instance()->getRepository(FeedEntry::class)->matching(
			$criteria->where($expr->eq("user", $this))
				->andWhere($expr->eq("type", "SHARE"))
				->andWhere($expr->neq("post", null))
		)->count();
	}

	/**
	 * @return int
	 */
	public function getFollowingPostCount(): int {
		$expr = Criteria::expr();
		$criteria = Criteria::create();

		return EntityManager::instance()->getRepository(FeedEntry::class)->matching(
			$criteria->where($expr->eq("user", $this))
				->andWhere($expr->eq("type", "NEW_FOLLOWING"))
				->andWhere($expr->neq("following", null))
		)->count();
	}

	/**
	 * @return int
	 */
	public function getTotalPostCount(): int {
		return $this->getPostCount() + $this->getReplyCount() + $this->getShareCount() + $this->getFollowingPostCount();
	}

	/**
	 * @return int
	 */
	public function getFollowingCount(): int {
		return EntityManager::instance()->getRepository(Follower::class)->count([
			"from" => $this
		]);
	}

	/**
	 * @return int
	 */
	public function getFollowerCount(): int {
		return EntityManager::instance()->getRepository(Follower::class)->count([
			"to" => $this
		]);
	}

	/**
	 * @return int
	 */
	public function getOpenRequestsCount(): int {
		return EntityManager::instance()->getRepository(FollowRequest::class)->count([
			"to" => $this
		]);
	}

	/**
	 * Returns this object as json object to be used in the API
	 *
	 * @access public
	 * @param User $view User to use as "current"
	 * @param bool $encode If true, will return a json string, else an associative array
	 * @param bool $includeFeaturedBox If true, the featured box will be included
	 * @return string|array
	 */
	public function toAPIJson($view, $encode = true, $includeFeaturedBox = true) {
		$a = [
			"id" => $this->id,
			"displayName" => $this->displayName,
			"username" => $this->username,
			"bio" => $this->bio,
			"avatar" => $this->getAvatarURL(),
			"verified" => $this->verified,
			"birthday" => $this->birthday,
			"privacyLevel" => $this->privacyLevel,
			"joinDate" => $this->time,
			"gigadriveJoinDate" => $this->gigadriveJoinDate,
			"suspended" => $this->isSuspended() ? true : false,
			"emailActivated" => $this->emailActivated ? true : false,
			"posts" => $this->getPosts(),
			"feedEntries" => $this->getFeedEntries(),
			"following" => $this->getFollowing(),
			"followers" => $this->getFollowers(),
			"followStatus" => $view->isFollowing($this) ? 1 : ($view->hasSentFollowRequest($this) ? 2 : 0),
			"followedStatus" => $this->isFollowing($view) ? 1 : ($this->hasSentFollowRequest($view) ? 2 : 0),
		];

		if ($includeFeaturedBox) {
			$featuredBox = [];
			foreach ($this->featuredBoxContent as $uID) {
				$u = User::getUserById($uID);
				if (is_null($u)) continue;

				array_push($featuredBox, $u->toAPIJson($view, false, false));
			}

			$a["featuredBox"] = [
				"title" => !is_null($this->featuredBoxTitle) ? $this->featuredBoxTitle : "Featured",
				"content" => $featuredBox
			];
		}

		return $encode == true ? json_encode($a) : $a;
	}

	/**
	 * Returns the number of unread messages the user has
	 *
	 * @access public
	 * @return int
	 */
	public function getUnreadMessages() {
		return 0;
	}

	/**
	 * Returns the number of unread notifications the user has
	 *
	 * @access public
	 * @return int
	 */
	public function getUnreadNotifications() {
		return EntityManager::instance()->getRepository(Notification::class)->count([
			"user" => $this,
			"seen" => false
		]);
	}

	/**
	 * Returns whether the current user may view this user
	 *
	 * @access public
	 * @return bool
	 */
	public function mayView() {
		$user = Util::getCurrentUser();
		if (Util::isLoggedIn() && $this->getId() === $user->getId()) {
			return true;
		}

		if ($this->isSuspended()) {
			return false;
		} else {
			if (!is_null($user)) {
				if (Block::hasBlocked($user, $this) || Block::hasBlocked($this, $user)) {
					return false;
				}
			}

			if ($this->getPrivacyLevel() == PrivacyLevel::PUBLIC) {
				return true;
			} else if ($this->getPrivacyLevel() == PrivacyLevel::PRIVATE) {
				if (!is_null($user)) {
					if (!Follower::isFollowing($user, $this)) {
						return false;
					}

					return true;
				}
			} else if ($this->getPrivacyLevel() == PrivacyLevel::CLOSED) {
				if (Util::isLoggedIn()) {
					if (!is_null($user)) {
						return $user->getId() == $this->getId();
					}
				}
			}
		}

		return false;
	}

	/**
	 * Returns an array of user objects that follow the user and are followed by the current user
	 *
	 * @access public
	 * @param User $user
	 * @return User[]
	 */
	public function followersYouFollow(User $user = null): array {
		if (is_null($user) && Util::isLoggedIn() && !is_null(Util::getCurrentUser())) $user = Util::getCurrentUser();

		if (!is_null($user)) {
			$entityManager = EntityManager::instance();

			/**
			 * @var User[] $users
			 */
			$users = $entityManager->getRepository(User::class)->createQueryBuilder("u")
				->where("exists (select 1 from qpost\Account\Follower f where f.to = :to and f.from = :from)")
				->setParameter("to", $this)
				->setParameter("from", $user)
				->andWhere("exists (select 1 from qpost\Account\Follower f2 where f2.to = :to and f2.from = :from)")
				->setParameter("to", $user)
				->setParameter("from", $this)
				->getQuery()
				->getResult();

			shuffle($users);

			return $users;
		}

		return [];
	}

	/**
	 * Deletes this account from the database and the cache, as well as all of their posts, favorites, followings etc.
	 *
	 * @access public
	 */
	public function deleteAccount(): void {
		$mysqli = Database::Instance()->get();

		// remove user data
		$stmt = $mysqli->prepare("DELETE FROM `users` WHERE `id` = ?");
		$stmt->bind_param("i", $this->id);
		$stmt->execute();
		$stmt->close();

		// remove all session tokens
		$stmt = $mysqli->prepare("DELETE FROM `tokens` WHERE `user` = ?");
		$stmt->bind_param("i", $this->id);
		$stmt->execute();
		$stmt->close();

		// remove all suspensions
		$stmt = $mysqli->prepare("DELETE FROM `suspensions` WHERE `target` = ?");
		$stmt->bind_param("i", $this->id);
		$stmt->execute();
		$stmt->close();

		// remove all notifications
		$stmt = $mysqli->prepare("DELETE FROM `notifications` WHERE `user` = ? OR `follower` = ?");
		$stmt->bind_param("i", $this->id, $this->id);
		$stmt->execute();
		$stmt->close();

		// remove all messages
		$stmt = $mysqli->prepare("DELETE FROM `messages` WHERE `sender` = ? OR `receiver` = ?");
		$stmt->bind_param("i", $this->id, $this->id);
		$stmt->execute();
		$stmt->close();

		// remove all follower requests
		$stmt = $mysqli->prepare("DELETE FROM `follow_requests` WHERE `follower` = ? OR `following` = ?");
		$stmt->bind_param("i", $this->id, $this->id);
		$stmt->execute();
		$stmt->close();

		// remove all followers and followings
		$stmt = $mysqli->prepare("DELETE FROM `follows` WHERE `follower` = ? OR `following` = ?");
		$stmt->bind_param("i", $this->id, $this->id);
		$stmt->execute();
		$stmt->close();

		// remove all feed entries
		$stmt = $mysqli->prepare("DELETE FROM `feed` WHERE `user` = ? OR `following` = ?");
		$stmt->bind_param("i", $this->id, $this->id);
		$stmt->execute();
		$stmt->close();

		// remove all favorites
		$stmt = $mysqli->prepare("DELETE FROM `favorites` WHERE `user` = ?");
		$stmt->bind_param("i", $this->id);
		$stmt->execute();
		$stmt->close();

		// remove all blocks
		$stmt = $mysqli->prepare("DELETE FROM `blocks` WHERE `user` = ? OR `target` = ?");
		$stmt->bind_param("i", $this->id, $this->id);
		$stmt->execute();
		$stmt->close();
	}
}
<?php

/**
 * Represents a user
 * 
 * @package Account
 * @author Gigadrive (support@gigadrivegroup.com)
 * @copyright 2016-2018 Gigadrive
 * @link https://gigadrivegroup.com/dev/technologies
 */
class User {
	/**
	 * Gets a user object by the user's ID
	 * 
	 * @access public
	 * @param int $id
	 * @return User
	 */
	public static function getUserById($id){
		$n = "user_id_" . $id;

		if(CacheHandler::existsInCache($n)){
			return CacheHandler::getFromCache($n);
		} else {
			$user = new User($id);
			$user->reload();

			if($user->exists == true){
				return $user;
			} else {
				return null;
			}
		}
	}

	/**
	 * Gets a user object by the user's username
	 * 
	 * @access public
	 * @param string $username
	 * @return User
	 */
	public static function getUserByUsername($username){
		$n = "user_name_" . strtolower($username);

		if(CacheHandler::existsInCache($n)){
			return CacheHandler::getFromCache($n);
		} else {
			$id = null;

			$mysqli = Database::Instance()->get();
			$stmt = $mysqli->prepare("SELECT `id` FROM `users` WHERE `username` = ?");
			$stmt->bind_param("s",$username);
			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					$row = $result->fetch_assoc();

					$id = $row["id"];
				}
			}
			$stmt->close();

			if(!is_null($id)){
				$user = new User($id);
				$user->reload();
	
				if($user->exists == true){
					return $user;
				} else {
					return null;
				}
			} else {
				return null;
			}
		}
	}

	public static function registerUser($id,$username,$avatar,$email,$token){
		$mysqli = Database::Instance()->get();
		$user = self::getUserById($id);

		if($account == null){
			$stmt = $mysqli->prepare("INSERT IGNORE INTO `users` (`id`,`username`,`email`,`avatar`,`token`) VALUES(?,?,?,?);");
			$stmt->bind_param("issss",$id,$username,$email,$avatar,$token);
			$stmt->execute();
			$stmt->close();

			self::getUserById($id); // cache data after registering
		} else {
			$stmt = $mysqli->prepare("UPDATE `users` SET `username` = ?, `email` = ?, `avatar` = ?, `token` = ? WHERE `id` = ?");
			$stmt->bind_param("ssssi",$username,$email,$avatar,$token,$id);
			$stmt->execute();
			$stmt->close();

			$user->username = $username;
			$user->email = $email;
			$user->avatar = $avatar;
			$user->saveToCache();
		}
	}

	/**
	 * Gets a user object by data
	 * 
	 * @access public
	 * @param int $id
	 * @param string $displayName
	 * @param string $username
	 * @param string $email
	 * @param string $avatar
	 * @param string $bio
	 * @param string $time
	 * @return User
	 */
	public static function getUserByData($id,$displayName,$username,$email,$avatar,$bio,$token,$time){
		$user = new User($id);

		$user->id = $id;
		$user->displayName = $displayName;
		$user->username = $username;
		$user->email = $email;
		$user->avatar = $avatar;
		$user->bio = $bio;
		$user->token = $token;
		$user->time = $time;

		$user->saveToCache();

		return $user;
	}

	/**
	 * @access private
	 * @var int $id
	 */
	private $id;

	/**
	 * @access private
	 * @var string $displayName
	 */
	private $displayName;

	/**
	 * @access private
	 * @var string $username
	 */
	private $username;

	/**
	 * @access private
	 * @var string $email
	 */
	private $email;

	/**
	 * @access private
	 * @var string $avatar
	 */
	private $avatar;

	/**
	 * @access private
	 * @var string $bio
	 */
	private $bio;

	/**
	 * @access private
	 * @var string $token
	 */
	private $token;

	/**
	 * @access private
	 * @var string $time
	 */
	private $time;

	/**
	 * @access private
	 * @var bool $exists
	 */
	private $exists;

	/**
	 * @access private
	 * @var int $posts
	 */
	private $posts;

	/**
	 * @access private
	 * @var int $followers
	 */
	private $followers;

	/**
	 * @access private
	 * @var int $following
	 */
	private $following;

	/**
	 * @access protected
	 * @var array $cachedFollowers
	 */
	protected $cachedFollowers = [];

	/**
	 * @access private
	 * @var int $unreadMessages
	 */
	private $unreadMessages;

	/**
	 * Constructor
	 * 
	 * @access private
	 * @param int $id
	 */
	protected function __construct($id){
		$this->id = $id;
	}

	/**
	 * Reloads the user's data
	 * 
	 * @access public
	 */
	public function reload(){
		$mysqli = Database::Instance()->get();

		$stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `id` = ?");
		$stmt->bind_param("i",$this->id);
		if($stmt->execute()){
			$result = $stmt->get_result();

			if($result->num_rows){
				$row = $result->fetch_assoc();

				$this->id = $row["id"];
				$this->displayName = $row["displayName"];
				$this->username = $row["username"];
				$this->email = $row["email"];
				$this->avatar = $row["avatar"];
				$this->bio = $row["bio"];
				$this->token = $row["token"];
				$this->time = $row["time"];

				$this->exists = true;

				if(!is_null($this->posts))
					$this->reloadPostsCount();

				if(!is_null($this->following))
					$this->reloadFollowingCount();

				if(!is_null($this->followers))
					$this->reloadFollowerCount();
			}

			$this->saveToCache();
		}
		$stmt->close();
	}

	/**
	 * Returns the user's account ID
	 * 
	 * @access public
	 * @return int
	 */
	public function getId(){
		return $this->id;
	}

	/**
	 * Returns the user's display name
	 * 
	 * @access public
	 * @return string
	 */
	public function getDisplayName(){
		return $this->displayName;
	}

	/**
	 * Returns the user's username
	 * 
	 * @access public
	 * @return string
	 */
	public function getUsername(){
		return $this->username;
	}

	/**
	 * Returns the user's email address
	 * 
	 * @access public
	 * @return string
	 */
	public function getEmail(){
		return $this->email;
	}

	/**
	 * Returns the user's avatar URL
	 * 
	 * @access public
	 * @return string
	 */
	public function getAvatarURL(){
		return is_null($this->avatar) ? sprintf(GIGADRIVE_CDN_UPLOAD_FINAL_URL,"defaultAvatar.png") : $this->avatar;
	}

	/**
	 * Returns the user's bio
	 * 
	 * @access public
	 * @return string
	 */
	public function getBio(){
		return $this->bio;
	}

	/**
	 * Returns the Gigadrive API token
	 * 
	 * @access public
	 * @return string
	 */
	public function getToken(){
		return $this->token;
	}

	/**
	 * Returns the registration time for the user
	 * 
	 * @access public
	 * @return string
	 */
	public function getTime(){
		return $this->time;
	}

	/**
	 * Gets the user's posts count
	 * 
	 * @access public
	 * @return int
	 */
	public function getPosts(){
		if(is_null($this->posts)){
			$mysqli = Database::Instance()->get();

			$stmt = $mysqli->prepare("SELECT COUNT(`id`) AS `count` FROM `posts` WHERE `user` = ?");
			$stmt->bind_param("i",$this->id);
			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					$row = $result->fetch_assoc();
					$this->posts = $row["count"];

					$this->saveToCache();
				}
			}
			$stmt->close();
		}

		return $this->posts;
	}

	/**
	 * Gets the user's followers count
	 * 
	 * @access public
	 * @return int
	 */
	public function getFollowers(){
		if(is_null($this->followers)){
			$mysqli = Database::Instance()->get();

			$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `follows` WHERE `following` = ?");
			$stmt->bind_param("i",$this->id);
			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					$row = $result->fetch_assoc();
					$this->followers = $row["count"];

					$this->saveToCache();
				}
			}
			$stmt->close();
		}

		return $this->followers;
	}

	/**
	 * Gets the user's following count
	 * 
	 * @access public
	 * @return int
	 */
	public function getFollowing(){
		if(is_null($this->following)){
			$mysqli = Database::Instance()->get();

			$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `follows` WHERE `follower` = ?");
			$stmt->bind_param("i",$this->id);
			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					$row = $result->fetch_assoc();
					$this->following = $row["count"];

					$this->saveToCache();
				}
			}
			$stmt->close();
		}

		return $this->following;
	}

	/**
	 * Reloads the posts count
	 * 
	 * @access public
	 */
	public function reloadPostsCount(){
		$this->posts = null;
		$this->getPosts();
	}

	/**
	 * Reloads the following count
	 * 
	 * @access public
	 */
	public function reloadFollowingCount(){
		$this->following = null;
		$this->getFollowing();
	}

	/**
	 * Reloads the follower count
	 * 
	 * @access public
	 */
	public function reloadFollowerCount(){
		$this->followers = null;
		$this->getFollowers();
	}

	/**
	 * Returns whether or not the user followers $user
	 * 
	 * @access public
	 * @param int|User $user The user object or ID
	 * @return bool
	 */
	public function isFollowing($user){
		if(is_object($user))
			$user = $user->getId();

		if(in_array($this->id,User::getUserById($user)->cachedFollowers)){
			return true;
		} else {
			$mysqli = Database::Instance()->get();

			$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `follows` WHERE `follower` = ? AND `following` = ?");
			$stmt->bind_param("ii",$this->id,$user);
			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					$row = $result->fetch_assoc();

					if($row["count"] > 0){
						User::getUserById($user)->cacheFollower($this->id);
						$this->saveToCache();
					}
				}
			}
			$stmt->close();

			return in_array($this->id,User::getUserById($user)->cachedFollowers);
		}
	}

	/**
	 * Returns whether or not the user is followed by $user
	 * 
	 * @access public
	 * @param int|User $user The user object or ID
	 * @return bool
	 */
	public function isFollower($user){
		return is_object($user) ? $user->isFollowing($this) : self::getUserById($user)->isFollowing($this);
		/*if(!is_object($user))
			$user = self::getUserById($user);

		if(in_array($this->id,$user->cachedFollowers)){
			return true;
		} else {
			$mysqli = Database::Instance()->get();

			$uID = $user->getId();

			$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `follows` WHERE `follower` = ? AND `following` = ?");
			$stmt->bind_param("ii",$uID,$this->id);
			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					$row = $result->fetch_assoc();

					if($row["count"] > 0){
						array_push($user->cachedFollowers,$this->getId());
						$user->saveToCache();
					}
				}
			}
			$stmt->close();

			return in_array($this->id,$user->cachedFollowers);
		}*/
	}

	/**
	 * Adds a user ID to the follower cache
	 * 
	 * @access public
	 * @param int $user
	 */
	public function cacheFollower($user){
		if(!in_array($user,$this->cachedFollowers))
			array_push($this->cachedFollowers,$user);
		
		$this->saveToCache();
	}

	/**
	 * Removes a user ID to the follower cache
	 * 
	 * @access public
	 * @param int $user
	 */
	public function uncacheFollower($user){
		if(in_array($user,$this->cachedFollowers))
			$this->cachedFollowers = Util::removeFromArray($this->cachedFollowers,$user);
		
		$this->saveToCache();
	}

	/**
	 * Follows a user
	 * 
	 * @access public
	 * @param int|User $user
	 */
	public function follow($user){
		if(is_object($user))
			$user = $user->getId();

		if(!$this->isFollowing($user)){
			$mysqli = Database::Instance()->get();

			$stmt = $mysqli->prepare("INSERT INTO `follows` (`follower`,`following`) VALUES(?,?);");
			$stmt->bind_param("ii",$this->id,$user);
			if($stmt->execute()){
				$u = self::getUserById($user);
				if(!is_null($u)){
					$u->cacheFollower($this->id);
					$u->reloadFollowerCount();
				}
			}
			$stmt->close();
		}
	}

	/**
	 * Unfollows a user
	 * 
	 * @access public
	 * @param int|User $user
	 */
	public function unfollow($user){
		if(is_object($user))
			$user = $user->getId();

		if($this->isFollowing($user)){
			$mysqli = Database::Instance()->get();

			$stmt = $mysqli->prepare("DELETE FROM `follows` WHERE `follower` = ? AND `following` = ?");
			$stmt->bind_param("ii",$this->id,$user);
			if($stmt->execute()){
				$u = self::getUserById($user);
				if(!is_null($u)){
					$u->uncacheFollower($this->id);
					$u->reloadFollowerCount();
				}
			}
			$stmt->close();
		}
	}

	/**
	 * Returns the number of unread messages the user has
	 * 
	 * @access public
	 * @return int
	 */
	public function getUnreadMessages(){
		if(is_null($this->unreadMessages)){
			$this->reloadUnreadMessages();
		}

		return $this->unreadMessages;
	}

	/**
	 * Updates the number of unread messages the user has
	 * 
	 * @access public
	 */
	public function reloadUnreadMessages(){
		$mysqli = Database::Instance()->get();

		$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `messages` WHERE `receiver` = ? AND `is_read` = 0");
		$stmt->bind_param("i",$this->id);
		if($stmt->execute()){
			$result = $stmt->get_result();

			if($result->num_rows){
				$row = $result->fetch_assoc();

				$this->unreadMessages = $row["count"];

				$this->saveToCache();
			}
		}
		$stmt->close();
	}

	/**
	 * Saves the user object to the cache
	 * 
	 * @access public
	 */
	public function saveToCache(){
		CacheHandler::setToCache("user_id_" . $this->id,$this,20*60);
		CacheHandler::setToCache("user_name_" . strtolower($this->username),$this,20*60);
	}
}
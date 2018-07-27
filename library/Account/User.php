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
		return $this->avatar;
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
	 * Saves the user object to the cache
	 * 
	 * @access public
	 */
	public function saveToCache(){
		CacheHandler::setToCache("user_id_" . $this->id,$this,20*60);
		CacheHandler::setToCache("user_name_" . strtolower($this->username),$this,20*60);
	}
}
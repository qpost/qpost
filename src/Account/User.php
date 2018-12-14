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
	* Gets a user object by the user's gigadrive ID
	* 
	* @access public
	* @param int $id
	* @return User
	*/
	public static function getUserByGigadriveId($gigadriveId){
		$n = "user_gigadriveId_" . $gigadriveId;
		
		if(CacheHandler::existsInCache($n)){
			return CacheHandler::getFromCache($n);
		} else {
			$id = null;
			
			$mysqli = Database::Instance()->get();
			$stmt = $mysqli->prepare("SELECT `id` FROM `users` WHERE `gigadriveId` = ?");
			$stmt->bind_param("i",$gigadriveId);
			if($stmt->execute()){
				$result = $stmt->get_result();
				
				if($result->num_rows){
					$row = $result->fetch_assoc();
					
					$id = $row["id"];
				}
			}
			$stmt->close();
			
			if(!is_null($id)){
				return self::getUserById($id);
			} else {
				return null;
			}
		}
	}
	
	/**
	* Gets a user object by the user's email
	* 
	* @access public
	* @param string $email
	* @return User
	*/
	public static function getUserByEmail($email){
		$id = null;
		
		$mysqli = Database::Instance()->get();
		$stmt = $mysqli->prepare("SELECT `id` FROM `users` WHERE `email` = ?");
		$stmt->bind_param("s",$email);
		if($stmt->execute()){
			$result = $stmt->get_result();
			
			if($result->num_rows){
				$row = $result->fetch_assoc();
				
				$id = $row["id"];
			}
		}
		$stmt->close();
		
		if(!is_null($id)){
			return self::getUserById($id);
		} else {
			return null;
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
	* Returns whether a user object is cached by ID
	* 
	* @access public
	* @param int $id
	* @return bool
	*/
	public static function isCached($id){
		return \CacheHandler::existsInCache("user_id_" . $id);
	}
	
	/**
	* Updates a Gigadrive user's data in the database
	* 
	* @access public
	* @param int $id
	* @param string $username
	* @param string $avatar
	* @param string $email
	* @param string $token
	* @param string $registerDate
	* @return User
	*/
	public static function registerUser($id,$username,$avatar,$email,$token,$registerDate){
		$mysqli = Database::Instance()->get();
		$user = self::getUserByGigadriveId($id);
		
		if($user == null){
			$stmt = $mysqli->prepare("INSERT IGNORE INTO `users` (`gigadriveId`,`displayName`,`username`,`email`,`avatar`,`token`,`gigadriveJoinDate`,`emailActivated`) VALUES(?,?,?,?,?,?,?,1);");
			$stmt->bind_param("issssss",$id,$username,$username,$email,$avatar,$token,$registerDate);
			$stmt->execute();
			$stmt->close();
			
			self::getUserById($id)->removeFromCache();
			
			$user = self::getUserByGigadriveId($id);
		} else {
			$stmt = $mysqli->prepare("UPDATE `users` SET `username` = ?, `email` = ?, `token` = ?, `gigadriveJoinDate` = ? WHERE `gigadriveId` = ?");
			$stmt->bind_param("sssssi",$username,$email,$avatar,$token,$registerDate,$id);
			$stmt->execute();
			$stmt->close();
			
			$user->username = $username;
			$user->email = $email;
			
			$user->saveToCache();
		}
		
		return $user;
	}
	
	/**
	* Gets a user object by data
	* 
	* @access public
	* @param int $id
	* @param int $gigadriveId
	* @param string $displayName
	* @param string $username
	* @param string $password
	* @param string $email
	* @param string $avatar
	* @param string $bio
	* @param string $time
	* @param bool $emailActivated
	* @param string $emailActivationToken
	* @param string $lastUsernameChange
	* @return User
	*/
	public static function getUserByData($id,$gigadriveId,$displayName,$username,$password,$email,$avatar,$bio,$token,$birthday,$privacyLevel,$featuredBoxTitle,$featuredBoxContent,$lastGigadriveUpdate,$gigadriveJoinDate,$time,$emailActivated,$emailActivationToken,$lastUsernameChange,$verified){
		$user = self::isCached($id) ? self::getUserById($id) : new User($id);
		
		$user->id = $id;
		$user->gigadriveId = $gigadriveId;
		$user->displayName = $displayName;
		$user->username = $username;
		$user->password = $password;
		$user->email = $email;
		$user->avatar = $avatar;
		$user->bio = $bio;
		$user->token = $token;
		$user->birthday = $birthday;
		$user->privacyLevel = $privacyLevel;
		$user->featuredBoxTitle = $featuredBoxTitle;
		$user->featuredBoxContent = is_null($featuredBoxContent) ? [] : (is_string($featuredBoxContent) ? json_decode($featuredBoxContent,true) : $featuredBoxContent);
		$user->lastGigadriveUpdate = $lastGigadriveUpdate;
		$user->gigadriveJoinDate = $gigadriveJoinDate;
		$user->time = $time;
		$user->emailActivated = $emailActivated;
		$user->emailActivationToken = $emailActivationToken;
		$user->lastUsernameChange = $lastUsernameChange;
		$user->verified = $verified ? true : false;
		
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
	* @var int $gigadriveId
	*/
	private $gigadriveId;
	
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
	* @var string $password
	*/
	private $password;
	
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
	* @var string $birthday
	*/
	private $birthday;
	
	/**
	* @access private
	* @var string $privacyLevel
	*/
	private $privacyLevel;
	
	/**
	* @access private
	* @var string $featuredBoxTitle
	*/
	private $featuredBoxTitle;
	
	/**
	* @access private
	* @var int[] $featuredBoxContent
	*/
	private $featuredBoxContent;
	
	/**
	* @access private
	* @var string $lastGigadriveUpdate
	*/
	private $lastGigadriveUpdate;
	
	/**
	* @access private
	* @var string $gigadriveJoinDate
	*/
	private $gigadriveJoinDate;
	
	/**
	* @access private
	* @var string $time
	*/
	private $time;
	
	/**
	* @access private
	* @var bool $emailActivated;
	*/
	private $emailActivated;
	
	/**
	* @access private
	* @var string $emailActivationToken
	*/
	private $emailActivationToken;
	
	/**
	* @access private
	* @var bool $verified
	*/
	private $verified;
	
	/**
	* @access private
	* @var string $lastUsernameChange
	*/
	private $lastUsernameChange;
	
	/**
	* @access private
	* @var bool $exists
	*/
	private $exists;
	
	/**
	* @access private
	* @var int $feedEntries
	*/
	private $feedEntries;
	
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
	* @access public
	* @var array $cachedFollowers
	*/
	public $cachedFollowers = [];
	
	/**
	* @access public
	* @var array $cachedBlocks
	*/
	public $cachedBlocks = [];
	
	/**
	* @access private
	* @var int $unreadMessages
	*/
	private $unreadMessages;
	
	/**
	* @access private
	* @var int $unreadNotifications
	*/
	private $unreadNotifications;
	
	/**
	* @access private
	* @var int $favorites
	*/
	private $favorites;
	
	/**
	* @access private
	* @var array $followingArray
	*/
	private $followingArray;
	
	/**
	* @access private
	* @var int $followRequests
	*/
	private $followRequests;
	
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
		
		$this->removeFromCache();
		
		$stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `id` = ?");
		$stmt->bind_param("i",$this->id);
		if($stmt->execute()){
			$result = $stmt->get_result();
			
			if($result->num_rows){
				$row = $result->fetch_assoc();
				
				$this->id = $row["id"];
				$this->gigadriveId = $row["gigadriveId"];
				$this->displayName = $row["displayName"];
				$this->username = $row["username"];
				$this->password = $row["password"];
				$this->email = $row["email"];
				$this->avatar = $row["avatar"];
				$this->bio = $row["bio"];
				$this->token = $row["token"];
				$this->birthday = $row["birthday"];
				$this->privacyLevel = $row["privacy.level"];
				$this->featuredBoxTitle = $row["featuredBox.title"];
				$this->featuredBoxContent = is_null($row["featuredBox.content"]) ? [] : json_decode($row["featuredBox.content"],true);
				$this->lastGigadriveUpdate = $row["lastGigadriveUpdate"];
				$this->gigadriveJoinDate = $row["gigadriveJoinDate"];
				$this->time = $row["time"];
				$this->emailActivated = $row["emailActivated"];
				$this->emailActivationToken = $row["emailActivationToken"];
				$this->lastUsernameChange = $row["lastUsernameChange"];
				$this->verified = $row["verified"] ? true : false;
				
				$this->exists = true;
				
				if(!is_null($this->feedEntries))
				$this->reloadFeedEntriesCount();
				
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
	* Returns the user's gigadrive ID
	* 
	* @access public
	* @return int
	*/
	public function getGigadriveId(){
		return $this->gigadriveId;
	}
	
	/**
	* Returns whether this account was created with the "Sign in with Gigadrive" button
	* 
	* @access public
	* @return bool
	*/
	public function isGigadriveLinked(){
		return !is_null($this->gigadriveId) && !is_null($this->token);
	}
	
	/**
	* Returns whether the user is verified
	* 
	* @access public
	* @return bool
	*/
	public function isVerified(){
		return $this->verified;
	}
	
	/**
	* Returns HTML code for check icon for verified users
	* 
	* @access public
	* @return string
	*/
	public function renderCheckMark(){
		return $this->verified ? '<span class="ml-1 small" data-placement="right" data-toggle="tooltip" data-html="true" title="<b>Verified account</b><br/>This account has has been confirmed as an authentic page for this public figure, media company or brand"><i class="fas fa-check-circle"' . (Util::isUsingNightMode() ? "" : ' style="color: #007bff"') . '></i></span>' : "";
	}
	
	/**
	* Returns the user's display name
	* 
	* @access public
	* @return string
	*/
	public function getDisplayName(){
		return Util::fixString($this->displayName);
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
	* Returns the user's password hash
	* 
	* @access public
	* @return string
	*/
	public function getPassword(){
		return $this->password;
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
	* Resets the user's avatar
	* 
	* @access public
	*/
	public function resetAvatar(){
		$this->avatar = null;
		
		$mysqli = Database::Instance()->get();
		$stmt = $mysqli->prepare("UPDATE `users` SET `avatar` = NULL WHERE `id` = ?");
		$stmt->bind_param("i",$this->id);
		$stmt->execute();
		$stmt->close();
		
		$this->saveToCache();
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
	* Returns the user's birthday
	* 
	* @access public
	* @return string
	*/
	public function getBirthday(){
		return $this->birthday;
	}
	
	/**
	* Returns the user's privacy level
	* 
	* @access public
	* @return string
	*/
	public function getPrivacyLevel(){
		return $this->privacyLevel;
	}
	
	/**
	* Returns the timestamp of the last time the user's data was updated with the Gigadrive API
	* 
	* @access public
	* @return string
	*/
	public function getLastGigadriveUpdate(){
		return $this->lastGigadriveUpdate;
	}
	
	/**
	* Returns the timestamp of when the Gigadrive account was created
	* 
	* @access public
	* @return string
	*/
	public function getGigadriveRegistrationDate(){
		return $this->gigadriveJoinDate;
	}
	
	/**
	* Updates the last gigadrive update date to right now
	* 
	* @access public
	*/
	public function updateLastGigadriveUpdate(){
		$mysqli = Database::Instance()->get();
		
		$stmt = $mysqli->prepare("UPDATE `users` SET `lastGigadriveUpdate` = CURRENT_TIMESTAMP WHERE `id` = ?");
		$stmt->bind_param("i",$this->id);
		if($stmt->execute()){
			$s = $mysqli->prepare("SELECT `lastGigadriveUpdate` FROM `users` WHERE `id` = ?");
			$s->bind_param("i",$this->id);
			if($s->execute()){
				$result = $s->get_result();
				
				if($result->num_rows){
					$this->lastGigadriveUpdate = $result->fetch_assoc()["lastGigadriveUpdate"];
					$this->saveToCache();
				}
			}
			$s->close();
		}
		$stmt->close();
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
	* Returns whether the user has activated their email
	* 
	* @access public
	* @return bool
	*/
	public function isEmailActivated(){
		return $this->emailActivated;
	}
	
	/**
	* Returns the user's email activation token
	* 
	* @access public
	* @return string
	*/
	public function getEmailActivationToken(){
		return $this->emailActivationToken;
	}
	
	/**
	* Returns the timestamp of the last username change
	* 
	* @access public
	* @return string
	*/
	public function getLastUsernameChange(){
		return $this->lastUsernameChange;
	}
	
	/**
	* Updates the user's last username change
	* 
	* @access public
	* @return string
	*/
	public function updateLastUsernameChange(){
		$date = date("Y-m-d H:i:s");
		
		$mysqli = Database::Instance()->get();
		
		$stmt = $mysqli->prepare("UPDATE `users` SET `lastUsernameChange` = ? WHERE `id` = ?");
		$stmt->bind_param("si",$date,$this->id);
		if($stmt->execute()){
			$this->lastUsernameChange = $date;
			$this->saveToCache();
		}
		$stmt->close();
	}
	
	/**
	* Returns the currently active suspension of the user, if available
	* 
	* @access public
	* @return Suspension
	*/
	public function getActiveSuspension(){
		return Suspension::getSuspensionByUser($this->id);
	}
	
	/**
	* Returns whether the user is currently suspended
	* 
	* @access public
	* @return bool;
	*/
	public function isSuspended(){
		return !is_null($this->getActiveSuspension());
	}
	
	/**
	* Gets the user's feed entries count
	* 
	* @access public
	* @return int
	*/
	public function getFeedEntries(){
		if(is_null($this->feedEntries)){
			$mysqli = Database::Instance()->get();
			
			$stmt = $mysqli->prepare("SELECT COUNT(`id`) AS `count` FROM `feed` WHERE `user` = ?");
			$stmt->bind_param("i",$this->id);
			if($stmt->execute()){
				$result = $stmt->get_result();
				
				if($result->num_rows){
					$row = $result->fetch_assoc();
					$this->feedEntries = $row["count"];
					
					$this->saveToCache();
				}
			}
			$stmt->close();
		}
		
		return $this->feedEntries;
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
			
			$stmt = $mysqli->prepare("SELECT COUNT(`id`) AS `count` FROM `feed` WHERE `user` = ? AND `type` = 'POST' AND `post` IS NULL");
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
	* Returns the title of the user's Featured box
	* 
	* @access public
	* @return string
	*/
	public function getFeaturedBoxTitle(){
		return $this->featuredBoxTitle;
	}
	
	/**
	* Returns an array of user IDs that are featured in the user's Featured box
	* 
	* @access public
	* @return int[]
	*/
	public function getFeaturedBoxContent(){
		return $this->featuredBoxContent;
	}
	
	/**
	* Gets the user's favorites count
	* 
	* @access public
	* @return int
	*/
	public function getFavorites(){
		if(is_null($this->favorites)){
			$mysqli = Database::Instance()->get();
			
			$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `favorites` WHERE `user` = ?");
			$stmt->bind_param("i",$this->id);
			if($stmt->execute()){
				$result = $stmt->get_result();
				
				if($result->num_rows){
					$row = $result->fetch_assoc();
					
					$this->favorites = $row["count"];
					
					$this->saveToCache();
				}
			}
			$stmt->close();
		}
		
		return $this->favorites;
	}
	
	/**
	* Reloads the favorites count
	* 
	* @access public
	*/
	public function reloadFavoritesCount(){
		$this->favorites = null;
		$this->getFavorites();
	}
	
	/**
	* Adds a specific post to the user's favorites
	* 
	* @access public
	* @param int $postId
	*/
	public function favorite($postId){
		if(!$this->hasFavorited($postId)){
			$post = FeedEntry::getEntryById($postId);
			
			if(!is_null($post) && $post->getType() == FEED_ENTRY_TYPE_POST){
				if(!$this->isBlocked($post->getUserId())){
					if(($this->id != $post->getUserId()) && (($post->getUser()->getPrivacyLevel() == PrivacyLevel::PUBLIC && !$this->isFollowing($post->getUserId())) || ($post->getUser()->getPrivacyLevel() == PrivacyLevel::CLOSED))){
						return;
					}
					
					$mysqli = Database::Instance()->get();
					
					$stmt = $mysqli->prepare("INSERT INTO `favorites` (`user`,`post`) VALUES(?,?);");
					$stmt->bind_param("ii",$this->id,$postId);
					if($stmt->execute()){
						\CacheHandler::setToCache("favoriteStatus_" . $this->id . "_" . $postId,true,5*60);
						
						if(!is_null($post))
						$post->reloadFavorites();
						
						if($post->getUser()->getId() != $this->id && $post->getUser()->canPostNotification(NOTIFICATION_TYPE_FAVORITE,$this->id,$postId)){
							$puid = $post->getUser()->getId();
							$pid = $post->getId();
							
							$s = $mysqli->prepare("INSERT INTO `notifications` (`user`,`type`,`follower`,`post`) VALUES(?,'FAVORITE',?,?);");
							$s->bind_param("iii",$puid,$this->id,$pid);
							$s->execute();
							$s->close();
							
							$post->getUser()->reloadUnreadNotifications();
						}
					}
					$stmt->close();
				}
			}
		}
	}
	
	/**
	* Removes a specific post from the user's favorites
	* 
	* @access public
	* @param int $postId
	*/
	public function unfavorite($postId){
		if($this->hasFavorited($postId)){
			$mysqli = Database::Instance()->get();
			
			$stmt = $mysqli->prepare("DELETE FROM `favorites` WHERE `user` = ? AND `post` = ?");
			$stmt->bind_param("ii",$this->id,$postId);
			if($stmt->execute()){
				\CacheHandler::setToCache("favoriteStatus_" . $this->id . "_" . $postId,false,5*60);
				
				$feedEntry = FeedEntry::getEntryById($postId);
				if(!is_null($feedEntry)){
					$feedEntry->reloadFavorites();
					$feedEntry->getUser()->removeNotification("FAVORITE",$this->id,$postId);
				}
			}
			$stmt->close();
		}
	}
	
	/**
	* Returns whether the user has marked a specific post as their favorite
	* 
	* @access public
	* @param int $postId
	* @return bool
	*/
	public function hasFavorited($postId){
		if(\CacheHandler::existsInCache("favoriteStatus_" . $this->id . "_" . $postId)){
			return \CacheHandler::getFromCache("favoriteStatus_" . $this->id . "_" . $postId);
		} else {
			$mysqli = Database::Instance()->get();
			
			$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `favorites` WHERE `user` = ? AND `post` = ?");
			$stmt->bind_param("ii",$this->id,$postId);
			if($stmt->execute()){
				$result = $stmt->get_result();
				
				if($result->num_rows){
					$row = $result->fetch_assoc();
					
					if($row["count"] > 0){
						\CacheHandler::setToCache("favoriteStatus_" . $this->id . "_" . $postId,true,5*60);
					} else {
						\CacheHandler::setToCache("favoriteStatus_" . $this->id . "_" . $postId,false,5*60);
					}
				}
			}
			$stmt->close();
			
			return \CacheHandler::getFromCache("favoriteStatus_" . $this->id . "_" . $postId);
		}
	}
	
	/**
	* Removes notification matching specified parameters
	* 
	* @access public
	* @param string $type
	* @param int $follower
	* @param int $post
	*/
	public function removeNotification($type,$follower,$post){
		$mysqli = Database::Instance()->get();
		$follower = is_null($follower) ? "IS NULL" : "= '" . $mysqli->real_escape_string($follower) . "'";
		$post = is_null($post) ? "IS NULL" : "= '" . $mysqli->real_escape_string($post) . "'";
		
		$stmt = $mysqli->prepare("DELETE FROM `notifications` WHERE `type` = ? AND `follower` " . $follower . " AND `post` " . $post . " AND `user` = ?");
		$stmt->bind_param("si",$type,$this->id);
		$stmt->execute();
		$stmt->close();
		
		$this->reloadUnreadNotifications();
	}
	
	/**
	* Removes notification matching specified parameters
	* 
	* @access public
	* @param string $type
	* @param int $following
	* @param int $post
	*/
	public function removeFeedEntry($type,$following,$post){
		$mysqli = Database::Instance()->get();
		$following = is_null($following) ? "IS NULL" : "= '" . $mysqli->real_escape_string($following) . "'";
		$post = is_null($post) ? "IS NULL" : "= '" . $mysqli->real_escape_string($post) . "'";
		
		$stmt = $mysqli->prepare("DELETE FROM `feed` WHERE `type` = ? AND `following` " . $following . " AND `post` " . $post . " AND `user` = ?");
		$stmt->bind_param("si",$type,$this->id);
		$stmt->execute();
		$stmt->close();
		
		$this->reloadFeedEntriesCount();
		$this->reloadPostsCount();
	}
	
	/**
	* Shares a post with the own profile and news feed
	* 
	* @access public
	* @param int $postId
	*/
	public function share($postId){
		if(!$this->hasShared($postId)){
			$mysqli = Database::Instance()->get();
			$sessionId = session_id();
			
			$stmt = $mysqli->prepare("INSERT INTO `feed` (`user`,`post`,`sessionId`,`type`) VALUES(?,?,?,'SHARE');");
			$stmt->bind_param("iis",$this->id,$postId,$sessionId);
			if($stmt->execute()){
				\CacheHandler::setToCache("shareStatus_" . $this->id . "_" . $postId,true,5*60);
				
				$feedEntry = FeedEntry::getEntryById($postId);
				if(!is_null($feedEntry))
				$feedEntry->reloadShares();
				
				if($feedEntry->getUser()->getId() != $this->id && $feedEntry->getUser()->canPostNotification(NOTIFICATION_TYPE_SHARE,$this->id,$postId)){
					$puid = $feedEntry->getUser()->getId();
					$pid = $feedEntry->getId();
					
					$s = $mysqli->prepare("INSERT INTO `notifications` (`user`,`type`,`follower`,`post`) VALUES(?,'SHARE',?,?);");
					$s->bind_param("iii",$puid,$this->id,$pid);
					$s->execute();
					$s->close();
					
					$feedEntry->getUser()->reloadUnreadNotifications();
				}
			}
			$stmt->close();
		}
	}
	
	/**
	* Removes a share made on a post
	* 
	* @access public
	* @param int $postId
	*/
	public function unshare($postId){
		if($this->hasShared($postId)){
			$mysqli = Database::Instance()->get();
			
			$stmt = $mysqli->prepare("DELETE FROM `feed` WHERE `user` = ? AND `type` = 'SHARE' AND `post` = ?");
			$stmt->bind_param("ii",$this->id,$postId);
			if($stmt->execute()){
				\CacheHandler::setToCache("shareStatus_" . $this->id . "_" . $postId,false,5*60);
				
				$feedEntry = FeedEntry::getEntryById($postId);
				if(!is_null($feedEntry)){
					$feedEntry->reloadShares();
					$feedEntry->getUser()->removeNotification("SHARE",$this->id,$postId);
				}
			}
			$stmt->close();
		}
	}
	
	/**
	* Returns whether the user has shared a post
	* 
	* @access public
	* @param int $postId
	*/
	public function hasShared($postId){
		if(\CacheHandler::existsInCache("shareStatus_" . $this->id . "_" . $postId)){
			return \CacheHandler::getFromCache("shareStatus_" . $this->id . "_" . $postId);
		} else {
			$mysqli = Database::Instance()->get();
			
			$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `feed` WHERE `user` = ? AND `type` = 'SHARE' AND `post` = ?");
			$stmt->bind_param("ii",$this->id,$postId);
			if($stmt->execute()){
				$result = $stmt->get_result();
				
				if($result->num_rows){
					$row = $result->fetch_assoc();
					
					if($row["count"] > 0){
						\CacheHandler::setToCache("shareStatus_" . $this->id . "_" . $postId,true,5*60);
					} else {
						\CacheHandler::setToCache("shareStatus_" . $this->id . "_" . $postId,false,5*60);
					}
				}
			}
			$stmt->close();
			
			return \CacheHandler::getFromCache("shareStatus_" . $this->id . "_" . $postId);
		}
	}
	
	/**
	* Reloads the feed entries count
	* 
	* @access public
	*/
	public function reloadFeedEntriesCount(){
		$this->feedEntries = null;
		$this->getFeedEntries();
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
		
		if($user == $this->id) return false;
		
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
	* Returns whether or not the user has sent a follow request to $user
	* 
	* @access public
	* @param int|User $user
	* @return bool
	*/
	public function hasSentFollowRequest($user){
		$userId = is_object($user) ? $user->getId() : $user;
		
		$b = false;
		
		$mysqli = Database::Instance()->get();
		
		$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `follow_requests` WHERE `follower` = ? AND `following` = ?");
		$stmt->bind_param("ii",$this->id,$userId);
		if($stmt->execute()){
			$result = $stmt->get_result();
			
			if($result->num_rows){
				$row = $result->fetch_assoc();
				
				if($row["count"] > 0)
				$b = true;
			}
		}
		$stmt->close();
		
		return $b;
	}
	
	/**
	* Returns the amount of open follow requests the user has
	*/
	public function getOpenFollowRequests(){
		if($this->getPrivacyLevel() == PrivacyLevel::PRIVATE){
			$mysqli = Database::Instance()->get();
			
			$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `follow_requests` WHERE `following` = ?");
			$stmt->bind_param("i",$this->id);
			if($stmt->execute()){
				$result = $stmt->get_result();
				
				if($result->num_rows){
					$row = $result->fetch_assoc();
					
					$this->followRequests = $row["count"];
					$this->saveToCache();
				}
			}
			$stmt->close();
			
			return $this->followRequests;
		} else {
			return 0;
		}
	}
	
	/**
	* Reloads the amount of open follow requests the user has
	* 
	* @access public
	*/
	public function reloadOpenFollowRequests(){
		if($this->getPrivacyLevel() == PrivacyLevel::PRIVATE){
			$this->followRequests = null;
			$this->getOpenFollowRequests();
		}
	}
	
	/**
	* Returns whether or not the user has received a follow request from $user
	* 
	* @access public
	* @param int|User $user
	* @return bool
	*/
	public function hasReceivedFollowRequest($user){
		if(is_object($user)){
			return $user->hasSentFollowRequest($this);
		} else {
			return self::getUserById($user)->hasSentFollowRequest($this);
		}
	}
	
	/**
	* Returns whether or not the user has blocked $user
	* 
	* @access public
	* @param int|User $user The user object or ID
	* @return bool
	*/
	public function hasBlocked($user){
		if(is_object($user))
		$user = $user->getId();
		
		if($user == $this->id) return false;
		
		if(in_array($user,$this->cachedBlocks)){
			return true;
		} else {
			$mysqli = Database::Instance()->get();
			
			$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `blocks` WHERE `user` = ? AND `target` = ?");
			$stmt->bind_param("ii",$this->id,$user);
			if($stmt->execute()){
				$result = $stmt->get_result();
				
				if($result->num_rows){
					$row = $result->fetch_assoc();
					
					if($row["count"] > 0){
						array_push($this->cachedBlocks,$user);
						$this->saveToCache();
					}
				}
			}
			$stmt->close();
			
			return in_array($user,$this->cachedBlocks);
		}
	}
	
	/**
	* Returns whether the user was blocked by $user
	* 
	* @access public
	* @param int|User The user object or ID
	* @return bool
	*/
	public function isBlocked($user){
		if(!is_object($user))
		$user = self::getUserById($user);
		
		return !Is_null($user) ? $user->hasBlocked($this) : false;
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
		if($this->getFollowers() >= FOLLOW_LIMIT) return;
		
		if(is_object($user))
		$user = $user->getId();
		
		if(!$this->isFollowing($user)){
			$mysqli = Database::Instance()->get();
			
			$u = self::getUserById($user);
			if(!is_null($u)){
				if($u->getPrivacyLevel() == PrivacyLevel::CLOSED){
					return;
				} else if($u->getPrivacyLevel() == PrivacyLevel::PRIVATE){
					if(!$this->hasSentFollowRequest($user)){
						$stmt = $mysqli->prepare("INSERT INTO `follow_requests` (`follower`,`following`) VALUES(?,?);");
						$stmt->bind_param("ii",$this->id,$user);
						$stmt->execute();
						$stmt->close();
						
						self::getUserById($user)->reloadOpenFollowRequests();
						
						return;
					} else {
						$stmt = $mysqli->prepare("DELETE FROM `follow_requests` WHERE `follower` = ? AND `following` = ?");
						$stmt->bind_param("ii",$this->id,$user);
						$stmt->execute();
						$stmt->close();
						
						self::getUserById($user)->reloadOpenFollowRequests();
					}
				}
			}
			
			$b = false;
			
			$stmt = $mysqli->prepare("INSERT INTO `follows` (`follower`,`following`) VALUES(?,?);");
			$stmt->bind_param("ii",$this->id,$user);
			if($stmt->execute()){
				if(!is_null($u)){
					$u->cacheFollower($this->id);
					$u->reloadFollowerCount();
					$this->reloadFollowingCount();
					
					$b = true;
				}
			}
			$stmt->close();
			
			if($b){
				$sID = session_id();
				
				if($this->canPostFeedEntry(FEED_ENTRY_TYPE_NEW_FOLLOWING,$user)){
					$stmt = $mysqli->prepare("INSERT INTO `feed` (`user`,`following`,`type`,`sessionId`) VALUES(?,?,'NEW_FOLLOWING',?);");
					$stmt->bind_param("iis",$this->id,$user,$sID);
					$stmt->execute();
					$stmt->close();
				}
				
				if(self::getUserById($user)->canPostNotification(NOTIFICATION_TYPE_NEW_FOLLOWER,$this->id,null)){
					$stmt = $mysqli->prepare("INSERT INTO `notifications` (`user`,`type`,`follower`) VALUES(?,'NEW_FOLLOWER',?);");
					$stmt->bind_param("ii",$user,$this->id);
					$stmt->execute();
					$stmt->close();
					
					self::getUserById($user)->reloadUnreadNotifications();
				}
			}
			
			$this->followingArray = null;
			$this->saveToCache();
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
					$u->removeNotification("NEW_FOLLOWER",$this->id,null);
					$this->removeFeedEntry("NEW_FOLLOWING",$u->getId(),null);
					$this->reloadFollowingCount();
				}
			}
			$stmt->close();
		}
	}
	
	/**
	* Blocks a user
	* 
	* @access public
	* @param int|User $user
	*/
	public function block($user){
		if(is_object($user))
		$user = $user->getId();
		
		if($user == $this->id) return;
		
		if(!$this->hasBlocked($user)){
			$mysqli = Database::Instance()->get();
			
			$stmt = $mysqli->prepare("INSERT INTO `blocks` (`user`,`target`) VALUES(?,?);");
			$stmt->bind_param("ii",$this->id,$user);
			if($stmt->execute()){
				array_push($this->cachedBlocks,$user);
				$this->saveToCache();
			}
			$stmt->close();
			
			if($this->isFollowing($user))
			$this->unfollow($user);
			
			if(self::getUserById($user)->isFollowing($this))
			self::getUserById($user)->unfollow($this);
		}
	}
	
	/**
	* Unblocks a user
	* 
	* @access public
	* @param int|User $user
	*/
	public function unblock($user){
		if(is_object($user))
		$user = $user->getId();
		
		if($user == $this->id) return;
		
		if($this->hasBlocked($user)){
			$mysqli = Database::Instance()->get();
			
			$stmt = $mysqli->prepare("DELETE FROM `blocks` WHERE `user` = ? AND `target` = ?");
			$stmt->bind_param("ii",$this->id,$user);
			if($stmt->execute()){
				$this->cachedBlocks = Util::removeFromArray($this->cachedBlocks,$user);
				$this->saveToCache();
			}
			$stmt->close();
		}
	}
	
	/**
	* Returns an array with the IDs of all users that are followed by this one
	* 
	* @access public
	* @return array
	*/
	public function getFollowingAsArray(){
		if(is_null($this->followingArray)){
			$this->followingArray = [];
			
			$mysqli = Database::Instance()->get();
			
			$stmt = $mysqli->prepare("SELECT `following` FROM `follows` WHERE `follower` = ?");
			$stmt->bind_param("i",$this->id);
			if($stmt->execute()){
				$result = $stmt->get_result();
				
				if($result->num_rows){
					while($row = $result->fetch_assoc()){
						array_push($this->followingArray,$row["following"]);
					}
				}
			}
			$stmt->close();
			
			$this->saveToCache();
		}
		
		return $this->followingArray;
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
	public function toAPIJson($view,$encode = true,$includeFeaturedBox = true){
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

		if($includeFeaturedBox){
			$featuredBox = [];
			foreach($this->featuredBoxContent as $uID){
				$u = User::getUserById($uID);
				if(is_null($u)) continue;

				array_push($featuredBox,$u->toAPIJson($view,false,false));
			}

			$a["featuredBox"] = [
				"title" => !is_null($this->featuredBoxTitle) ? $this->featuredBoxTitle : "Featured",
				"content" => $featuredBox
			];
		}
		
		return $encode == true ? json_encode($a) : $a;
	}
	
	/**
	* Returns true if a feed entry may be posted with the given parameters
	* 
	* @access public
	* @param string $type
	* @param int $following
	* @return bool
	*/
	public function canPostFeedEntry($type,$following){
		$b = true;
		
		$mysqli = Database::Instance()->get();
		
		$stmt = $mysqli->prepare("SELECT COUNT(`id`) AS `count` FROM `feed` WHERE `user` = ? AND `type` = ? AND `following` = ? AND `time` > (NOW() - INTERVAL 4 DAY)");
		$stmt->bind_param("isi",$this->id,$type,$following);
		if($stmt->execute()){
			$result = $stmt->get_result();
			
			if($result->num_rows){
				$row = $result->fetch_assoc();
				
				if($row["count"] > 0){
					$b = false;
				} else {
					$s = $mysqli->prepare("SELECT `type`,`following` FROM `feed` WHERE `user` = ? ORDER BY `time` DESC LIMIT 10");
					$s->bind_param("i",$this->id);
					if($s->execute()){
						$result = $s->get_result();
						
						if($result->num_rows){
							while($row = $result->fetch_assoc()){
								if($row["type"] == $type && $row["following"] == $following){
									$b = false;
									break;
								}
							}
						}
					}
					$s->close();
				}
			}
		}
		$stmt->close();
		
		return $b;
	}
	
	/**
	* Returns true if a notification may be posted with the given parameters
	* 
	* @access public
	* @param string $type
	* @param int $follower
	* @param int $post
	* @return bool
	*/
	public function canPostNotification($type,$follower,$post){
		$b = true;
		
		$mysqli = Database::Instance()->get();
		
		$stmt = $mysqli->prepare("SELECT COUNT(`id`) AS `count` FROM `notifications` WHERE `user` = ? AND `type` = ? AND `follower` = ? AND `post` " . (is_null($post) ? " IS NULL" : "= " . $post) . " AND `time` > (NOW() - INTERVAL 4 DAY)");
		$stmt->bind_param("isi",$this->id,$type,$follower);
		if($stmt->execute()){
			$result = $stmt->get_result();
			
			if($result->num_rows){
				$row = $result->fetch_assoc();
				
				if($row["count"] > 0){
					$b = false;
				} else {
					$s = $mysqli->prepare("SELECT `type`,`follower`,`post` FROM `notifications` WHERE `user` = ? ORDER BY `time` DESC LIMIT 10");
					$s->bind_param("i",$this->id);
					if($s->execute()){
						$result = $s->get_result();
						
						if($result->num_rows){
							while($row = $result->fetch_assoc()){
								if($row["type"] == $type && $row["follower"] == $follower && $row["post"] == $post){
									$b = false;
									break;
								}
							}
						}
					}
					$s->close();
				}
			}
		}
		$stmt->close();
		
		return $b;
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
	* Returns the number of unread notifications the user has
	* 
	* @access public
	* @return int
	*/
	public function getUnreadNotifications(){
		if(is_null($this->unreadNotifications)){
			$this->reloadUnreadNotifications();
		}
		
		return $this->unreadNotifications;
	}
	
	/**
	* Updates the number of unread messages the user has
	* 
	* @access public
	*/
	public function reloadUnreadNotifications(){
		$mysqli = Database::Instance()->get();
		
		$stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `notifications` WHERE `user` = ? AND `seen` = 0");
		$stmt->bind_param("i",$this->id);
		if($stmt->execute()){
			$result = $stmt->get_result();
			
			if($result->num_rows){
				$row = $result->fetch_assoc();
				
				$this->unreadNotifications = $row["count"];
				
				$this->saveToCache();
			}
		}
		$stmt->close();
	}
	
	/**
	* Marks all unread notifications as read
	* 
	* @access public
	*/
	public function markNotificationsAsRead(){
		$this->unreadNotifications = 0;
		
		$mysqli = Database::Instance()->get();
		
		$stmt = $mysqli->prepare("UPDATE `notifications` SET `seen` = 1 WHERE `user` = ? AND `seen` = 0");
		$stmt->bind_param("i",$this->id);
		$stmt->execute();
		$stmt->close();
		
		$this->saveToCache();
	}
	
	/**
	* Returns whether the current user may view this user
	* 
	* @access public
	* @return bool
	*/
	public function mayView(){
		if($this->isSuspended()){
			return false;
		} else {
			if($this->getPrivacyLevel() == PrivacyLevel::PUBLIC){
				if(Util::isLoggedIn()){
					$user = Util::getCurrentUser();
					
					if(!is_null($user)){
						if($user->hasBlocked($this) || $user->isBlocked($this)){
							return false;
						}
					}
				}
				
				return true;
			} else if($this->getPrivacyLevel() == PrivacyLevel::PRIVATE){
				if(Util::isLoggedIn()){
					if(!is_null($user)){
						if($user->hasBlocked($this) || $user->isBlocked($this)){
							return false;
						}
						
						if(!$user->isFollowing($this)){
							return false;
						}
						
						return true;
					}
				}
			} else if($this->getPrivacyLevel() == PrivacyLevel::CLOSED){
				if(Util::isLoggedIn()){
					if(!is_null($user)){
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
	* @return User[]
	*/
	public function followersYouFollow($user = null){
		if(is_null($user) && Util::isLoggedIn() && !is_null(Util::getCurrentUser())) $user = Util::getCurrentUser();
		
		if(!is_null($user)){
			$n = "followersYouFollow_" . $user->getId() . "_" . $this->id;
			
			if(CacheHandler::existsInCache($n)){
				return CacheHandler::getFromCache($n);
			} else {
				$mysqli = Database::Instance()->get();
				
				$thisID = $this->id;
				$uID = $user->getId();
				
				$a = [];
				
				$stmt = $mysqli->prepare("SELECT u.* FROM `users` AS u WHERE EXISTS (SELECT 1 FROM follows f WHERE f.following = ? AND f.follower = u.id) AND EXISTS (SELECT 1 FROM follows f WHERE f.following = u.id AND f.follower = ?) ORDER BY RAND()");
				$stmt->bind_param("ii",$thisID,$uID);
				if($stmt->execute()){
					$result = $stmt->get_result();
					
					if($result->num_rows){
						while($row = $result->fetch_assoc()){
							array_push($a,User::getUserByData($row["id"],$row["gigadriveId"],$row["displayName"],$row["username"],$row["password"],$row["email"],$row["avatar"],$row["bio"],$row["token"],$row["birthday"],$row["privacy.level"],$row["featuredBox.title"],$row["featuredBox.content"],$row["lastGigadriveUpdate"],$row["gigadriveJoinDate"],$row["time"],$row["emailActivated"],$row["emailActivationToken"],$row["lastUsernameChange"],$row["verified"]));
						}
						
						CacheHandler::setToCache($n,$a,3*60);
					}
				}
				$stmt->close();
				
				return $a;
			}
		}
		
		return [];
	}
	
	/**
	* Returns HTML code to use in an user list
	* 
	* @access public
	* @return string
	*/
	public function renderForUserList(){
		if(!$this->mayView()) return "";
		
		$s = "";
		
		// V1
		/*$s .= '<div class="col-md-4 px-1 py-1">';
		$s .= '<div class="card userCard" data-user-id="' . $this->id . '" style="height: 327px">';
		$s .= '<div class="px-2 py-2 text-center">';
		$s .= '<a href="/' . $this->username . '" class="clearUnderline"><img src="' . $this->getAvatarURL() . '" width="60" height="60" class="rounded mb-1"/>';
		
		$s .= '<h5 class="mb-0 convertEmoji">' . $this->getDisplayName() . '</a></h5>';
		$s .= '<p class="text-muted my-0" style="font-size: 16px">@' . $this->username . '</p>';
		
		if(Util::isLoggedIn()){
			$s .= '</div>';
			
			$s .= '<div class="text-center px-2 py-2" style="background: #212529">';
			$s .= Util::followButton($this->id,true,["btn-block"]);
			$s .= '</div>';
			
			$s .= '<div class="px-2 py-2 text-center">';
		}
		
		$s .= !is_null($this->bio) ? '<p class="mb-0 mt-2 convertEmoji">' . Util::convertPost($this->bio) . '</p>' : "";
		$s .= '</div>';
		$s .= '</div>';
		$s .= '</div>';*/
		
		// V2
		$s .= '<div class="col-md-4 px-1 py-1">';
		$s .= '<div class="card userCard" data-user-id="' . $this->id . '" style="height: 100%; min-height: 200px;">';
		$s .= '<div class="px-4 pt-2">';
		$s .= '<div class="row">';
		$s .= '<div class="float-left">';
		$s .= '<a href="/' . $this->getUsername() . '" class="clearUnderline ignoreParentClick">';
		$s .= '<img class="rounded mx-1 my-1" src="' . $this->getAvatarURL() . '" width="40" height="40"/>';
		$s .= '</a>';
		$s .= '</div>';
		
		$s .= '<div class="float-left ml-1">';
		$s .= '<p class="mb-0" style="overflow: hidden !important; text-overflow: ellipsis !important; white-space: nowrap !important; word-wrap: normal !important; width: 200px !important;">';
		$s .= '<a href="/' . $this->getUsername() . '" class="clearUnderline ignoreParentClick">';
		$s .= '<span class="font-weight-bold convertEmoji">' . $this->getDisplayName() . $this->renderCheckMark() . '</span>';
		$s .= '</a>';
		$s .= '<div class="text-muted font-weight-normal" style="overflow: hidden !important; text-overflow: ellipsis !important; white-space: nowrap !important; word-wrap: normal !important; width: 200px !important;"> @' . $this->getUsername() . ' </div>';
		$s .= '</p>';
		$s .= '</div>';
		$s .= '</div>';
		if(Util::isLoggedIn()){
			$s .= '</div>';
			
			$s .= '<div class="text-center px-2 py-2" style="background: #212529">';
			$s .= Util::followButton($this->id,true,["btn-block"]);
			$s .= '</div>';
			
			$s .= '<div class="px-4 py-2 text-center">';
		}
		$s .= !is_null($this->bio) ? '<p class="mb-0 mt-1 convertEmoji">' . Util::convertPost($this->bio) . '</p>' : "<em>No bio set.</em>";
		$s .= '</div>';
		$s .= '</div>';
		$s .= '</div>';
		
		return $s;
	}
	
	/**
	* Saves the user object to the cache
	* 
	* @access public
	*/
	public function saveToCache(){
		\CacheHandler::setToCache("user_id_" . $this->id,$this,20*60);
		\CacheHandler::setToCache("user_name_" . strtolower($this->username),$this,20*60);
		\CacheHandler::setToCache("user_gigadriveId_" . $this->gigadriveId,$this,20*60);
	}
	
	/**
	* Removes the user object from the cache
	* 
	* @access public
	*/
	public function removeFromCache(){
		\CacheHandler::deleteFromCache("user_id_" . $this->id);
		\CacheHandler::deleteFromCache("user_name_" . strtolower($this->username));
		\CacheHandler::deleteFromCache("user_gigadriveId_" . $this->gigadriveId);
	}
}
<?php

/**
 * Represents a token to be used with the API for a user
 * 
 * @package Account
 * @author Gigadrive (support@gigadrivegroup.com)
 * @copyright 2016-2018 Gigadrive
 * @link https://gigadrivegroup.com/dev/technologies
 */
class Token {
	/**
	 * Returns a token by it's id
	 * 
	 * @access public
	 * @param string $id
	 * @return Token
	 */
	public static function getTokenById($id){
		$n = "apiToken_" . $id;

		if(CacheHandler::existsInCache($n)){
			return CacheHandler::getFromCache($n);
		} else {
			$token = new Token($id);
			$token->reload();

			return $token->exists == true ? $token : null;
		}
	}

	/**
	 * @access private
	 * @var string $id
	 */
	private $id;

	/**
	 * @access private
	 * @var int $user
	 */
	private $user;

	/**
	 * @access private
	 * @var string $lastIP
	 */
	private $lastIP;

	/**
	 * @access private
	 * @var string $userAgent;
	 */
	private $userAgent;

	/**
	 * @access private
	 * @var string $time
	 */
	private $time;

	/**
	 * @access private
	 * @var string $expiry
	 */
	private $expiry;

	/**
	 * @access private
	 * @var bool $exists
	 */
	private $exists = false;

	protected function __construct($id){
		$this->id = $id;
	}
	
	/**
	 * Returns the ID of the token
	 * 
	 * @access public
	 * @return string
	 */
	public function getId(){
		return $this->id;
	}

	/**
	 * Returns the ID of the token user
	 * 
	 * @access public
	 * @return int
	 */
	public function getUserId(){
		return $this->user;
	}

	/**
	 * Returns the object of the token user
	 * 
	 * @access public
	 * @return User
	 */
	public function getUser(){
		return User::getUserById($this->user);
	}

	/**
	 * Returns the last IP that the token was used from
	 * 
	 * @access public
	 * @return string
	 */
	public function getIP(){
		return $this->lastIP;
	}

	/**
	 * Returns information of the last IP that the token was used from
	 * 
	 * @access public
	 * @return Gigadrive\Account\IPInformation
	 */
	public function getIPInformation(){
		return Gigadrive\Account\IPInformation::getInformationFromIP($this->lastIP);
	}

	/**
	 * Returns the last user agent this token was used with
	 * 
	 * @access public
	 * @return string
	 */
	public function getUserAgent(){
		return $this->userAgent;
	}

	/**
	 * Returns the time this token was created
	 * 
	 * @access public
	 * @return string
	 */
	public function getTime(){
		return $this->time;
	}

	/**
	 * Returns the time this token will expire
	 * 
	 * @access public
	 * @return string
	 */
	public function getExpiryTime(){
		return $this->expiry;
	}

	/**
	 * Saves the token data to the cache
	 * 
	 * @access public
	 */
	public function saveToCache(){
		CacheHandler::setToCache("apiToken_" . $this->id,$this,20*60);
	}

	/**
	 * Removes the token data from the cache
	 * 
	 * @access public
	 */
	public function removeFromCache(){
		CacheHandler::deleteFromCache("apiToken_" . $this->id);
	}

	/**
	 * Reloads the token data
	 * 
	 * @access public
	 */
	public function reload(){
		$mysqli = Database::Instance()->get();

		$stmt = $mysqli->prepare("SELECT * FROM `tokens` WHERE `id` = ?");
		$stmt->bind_param("s",$this->id);

		if($stmt->execute()){
			$result = $stmt->get_result();

			if($result->num_rows){
				$row = $result->fetch_assoc();

				$this->id = $row["id"];
				$this->user = $row["user"];
				$this->lastIP = $row["lastIP"];
				$this->userAgent = $row["userAgent"];
				$this->time = $row["time"];
				$this->expiry = $row["expiry"];

				$this->exists = true;

				$this->saveToCache();
			} else {
				$this->removeFromCache();
			}
		} else {
			$this->removeFromCache();
		}

		$stmt->close();
	}
}
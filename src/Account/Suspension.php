<?php

namespace qpost\Account;

use qpost\Cache\CacheHandler;
use qpost\Database\Database;

/**
 * Represents a suspension of an account
 *
 * @package Account
 * @author Gigadrive (support@gigadrivegroup.com)
 * @copyright 2016-2018 Gigadrive
 * @link https://gigadrivegroup.com/dev/technologies
 */
class Suspension {
	/**
	 * Returns a suspension found by it's id
	 *
	 * @access public
	 * @param int $id
	 * @return Suspension
	 */
	public static function getSuspensionById($id){
		$n = "suspension_id_" . $id;

		if (CacheHandler::existsInCache($n)) {
			return CacheHandler::getFromCache($n);
		} else {
			$suspension = new Suspension($id);
			$suspension->reload();

			if($suspension->exists == true){
				return $suspension;
			} else {
				return null;
			}
		}
	}

	/**
	 * Returns an active suspension found by it's user
	 *
	 * @access public
	 * @param int $user
	 * @return Suspension
	 */
	public static function getSuspensionByUser($user){
		$n = "suspension_user_" . $user;

		if (CacheHandler::existsInCache($n)) {
			return CacheHandler::getFromCache($n);
		} else {
			$id = null;

			$mysqli = Database::Instance()->get();
			$stmt = $mysqli->prepare("SELECT `id` FROM `suspensions` WHERE `target` = ? AND `active` = 1");
			$stmt->bind_param("i",$user);
			if($stmt->execute()){
				$result = $stmt->get_result();

				if($result->num_rows){
					$row = $result->fetch_assoc();

					$id = $row["id"];
				}
			}
			$stmt->close();

			return !is_null($id) ? self::getSuspensionById($id) : null;
		}
	}

	/**
	 * @access private
	 * @var int $id
	 */
	private $id;

	/**
	 * @access private
	 * @var int $target
	 */
	private $target;

	/**
	 * @access private
	 * @var string $reason
	 */
	private $reason;

	/**
	 * @access private
	 * @var int $staff
	 */
	private $staff;

	/**
	 * @access private
	 * @var bool $active
	 */
	private $active;

	/**
	 * @access private
	 * @var string $time
	 */
	private $time;

	/**
	 * @access private
	 * @var bool $exists
	 */
	private $exists = false;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param int $id
	 */
	protected function __construct($id){
		$this->id = $id;
	}

	/**
	 * Reloads the suspension data
	 *
	 * @access public
	 */
	public function reload(){
		$mysqli = Database::Instance()->get();

		$stmt = $mysqli->prepare("SELECT * FROM `suspensions` WHERE `id` = ?");
		$stmt->bind_param("i",$this->id);
		if($stmt->execute()){
			$result = $stmt->get_result();

			if($result->num_rows){
				$row = $result->fetch_assoc();

				$this->id = $row["id"];
				$this->target = $row["target"];
				$this->reason = $row["reason"];
				$this->staff = $row["staff"];
				$this->active = $row["active"];
				$this->time = $row["time"];

				$this->exists = true;

				$this->saveToCache();
			}
		}
		$stmt->close();
	}

	/**
	 * Saves the suspension data to the cache
	 *
	 * @access public
	 */
	public function saveToCache(){
		CacheHandler::setToCache("suspension_id_" . $this->id, $this, CacheHandler::OBJECT_CACHE_TIME);
		if ($this->active == true) CacheHandler::setToCache("suspension_user_" . $this->target, $this, CacheHandler::OBJECT_CACHE_TIME);
	}

	/**
	 * Returns the ID of this suspension
	 *
	 * @access public
	 * @return int
	 */
	public function getId(){
		return $this->id;
	}

	/**
	 * Returns the ID of this suspension's target
	 *
	 * @access public
	 * @return int
	 */
	public function getTargetId(){
		return $this->target;
	}

	/**
	 * Returns the user object of this suspension's target
	 *
	 * @access public
	 * @return User
	 */
	public function getTarget(){
		return User::getUserById($this->target);
	}

	/**
	 * Returns the reason of this suspension
	 *
	 * @access public
	 * @return string
	 */
	public function getReason(){
		return $this->reason;
	}

	/**
	 * Returns the ID of this suspension's staff member
	 *
	 * @access public
	 * @return int
	 */
	public function getStaffId(){
		return $this->staff;
	}

	/**
	 * Returns the user object of this suspension's staff member
	 *
	 * @access public
	 * @return User
	 */
	public function getStaff(){
		return !is_null($this->staff) ? User::getUserById($this->staff) : null;
	}

	/**
	 * Returns whether this suspension is active
	 *
	 * @access public
	 * @return bool
	 */
	public function isActive(){
		return $this->active;
	}

	/**
	 * Returns the timestamp of when this suspension was created
	 *
	 * @access public
	 * @return string
	 */
	public function getTime(){
		return $this->time;
	}
}
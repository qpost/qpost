<?php

namespace qpost\Account;

use qpost\Cache\CacheHandler;
use qpost\Database\Database;
use qpost\Util\Util;

/**
 * Represents information about an IP address
 * 
 * @package Account
 * @author Gigadrive (support@gigadrivegroup.com)
 * @copyright 2016-2018 Gigadrive
 * @link https://gigadrivegroup.com/dev/technologies
 */
class IPInformation {
	/**
	 * Gets information about an IP address
	 * 
	 * @access public
	 * @param string $ip The IP address
	 * @return IPInformation
	 */
	public static function getInformationFromIP($ip){
		if (is_null($ip) || Util::isEmpty($ip) || $ip == "127.0.0.1" || $ip == "localhost" || explode(":", $ip)[0] == "127.0.0.1" || explode(":", $ip)[0] == "localhost")
			return null;

		$n = "ipInformation_" . $ip;

		if (CacheHandler::existsInCache($n)) {
			return CacheHandler::getFromCache($n);
		} else {
			$info = new IPInformation($ip);
			
			if($info->exists){
				return $info;
			} else {
				CacheHandler::setToCache($n, null, 30 * 60);
				return null;
			}
		}
	}
	
	/**
	 * @access private
	 * @var string $ip
	 */
	private $ip;

	/**
	 * @access private
	 * @var array $data
	 */
	private $data;

	/**
	 * @access private
	 * @var double $vpnCheckResult
	 */
	private $vpnCheckResult;

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
	 * Constructor
	 * 
	 * @access public
	 * @param string $ip
	 */
	public function __construct($ip){
		$mysqli = Database::Instance()->get();
		$this->ip = $ip;

		$stmt = $mysqli->prepare("SELECT * FROM `db_297066_12`.`gigadrive_ipinfo` WHERE `ip` = ?");
		$stmt->bind_param("s",$ip);
		if($stmt->execute()){
			$result = $stmt->get_result();

			if($result->num_rows){
				$row = $result->fetch_assoc();
			}
		}
		$stmt->close();

		if(isset($row)){
			$this->ip = $row["ip"];
			$this->data = json_decode($row["data"],true);
			$this->time = $row["time"];

			$this->getVPNCheckResult();
				
			$this->exists = true;
			$this->saveToCache();
		} else {
			$this->time = date("Y-m-d H:i:s");
			$this->exists = true;
			$this->getVPNCheckResult(false);

			$stmt = $mysqli->prepare("INSERT INTO `db_297066_12`.`gigadrive_ipinfo` (`ip`,`data`,`vpnCheckResult`,`time`) VALUES(?,?,?,?);");
			$stmt->bind_param("ssds",$ip,$this->data,$this->vpnCheckResult,$this->time);
			$stmt->execute();
			$stmt->close();
				
			$this->saveToCache();
		}
	}
	
	/**
	 * Returns the IP address
	 * 
	 * @access public
	 * @return string
	 */
	public function getIP(){
		return $this->ip;
	}
	
	/**
	 * Returns data fetched from the ipstack.com API
	 * 
	 * @access public
	 * @return array|json
	 */
	public function getData(){
		if($this->data == null){
			$url = "http://api.ipstack.com/" . $this->ip . "?access_key=" . IPSTACK_KEY . "&format=1";
			$content = file_get_contents($url);
			$data = json_decode($content,true);
			if(isset($data["city"])){
				$this->data = $data;
				$jsonData = json_encode($this->data);

				$mysqli = Database::Instance()->get();

				$stmt = $mysqli->prepare("UPDATE `db_297066_12`.`gigadrive_ipinfo` SET `data` = ? WHERE `ip` = ?");
				$stmt->bind_param("ss",$jsonData,$this->ip);
				$stmt->execute();
				$stmt->close();
				
				$this->saveToCache();
			}
		}

		return $this->data;
	}
	
	/**
	 * Returns the timestamp at which this IP address was first discovered
	 * 
	 * @access public
	 * @return string
	 */
	public function getTime(){
		return $this->time;
	}
	
	/**
	 * Returns whether this IP address exists and was saved to the database
	 * 
	 * @access public
	 * @return bool
	 */
	public function exists(){
		return $this->exists;
	}

	/**
	 * Returns the VPN check result from getipintel.net (deprecated)
	 * 
	 * @access public
	 * @param bool $update If true, the result will be saved to the database
	 * @return double The result on a scale between 0.00 and 1.00
	 */
	public function getVPNCheckResult($update = true){
		$mysqli = Database::Instance()->get();

		if(is_null($this->vpnCheckResult)){
			$vpnData = json_decode(file_get_contents("http://check.getipintel.net/check.php?ip=" . $this->ip . "&contact=support@gigadrivegroup.com&format=json&flags=f"),true);
			if(isset($vpnData["status"])){
				if($vpnData["status"] == "success"){
					$this->vpnCheckResult = (double)$vpnData["result"];

					if($update == true){
						$stmt = $mysqli->prepare("UPDATE `db_297066_12`.`gigadrive_ipinfo` SET `vpnCheckResult` = ? WHERE `ip` = ?");
						$stmt->bind_param("ds",$this->vpnCheckResult,$this->ip);
						$stmt->execute();
						$stmt->close();
					}
				}
			}
		}

		return $this->vpnCheckResult;
	}
	
	/**
	 * Saves the IP data to the cache
	 * 
	 * @access public
	 */
	public function saveToCache(){
		$n = "ipInformation_" . $this->ip;
		CacheHandler::setToCache($n, $this, CacheHandler::OBJECT_CACHE_TIME);
	}
}
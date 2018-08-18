<?php

use Phpfastcache\CacheManager;
use Phpfastcache\Config\Config;

/**
 * Class CacheHandler
 * 
 * @description Utility methods to use with phpFastCache
 * @package Cache
 * @author Gigadrive (support@gigadrivegroup.com)
 * @copyright 2016-2018 Gigadrive
 * @link https://gigadrivegroup.com/dev/technologies
 */
class CacheHandler {
	/**
	 * Gets the phpFastCache CacheManager object
	 * 
	 * @access public
	 * @return CacheManager
	 */
	public static function Manager(){
		try {
			static $InstanceCache = null;
			if($InstanceCache == null){
				$InstanceCache = CacheManager::getInstance("files");
			}
	
			return $InstanceCache;
		} catch(Exception $e){
			return CacheManager::getInstance("files");
		}
	}

	/**
	 * Save an object to the cache
	 * 
	 * @access public
	 * @param string $name The key under which the object should be stored (if key is already being used, the old object will be overwritten)
	 * @param mixed $value The object to be stored
	 * @param int $expiry The amount in seconds for which the object should be held in the cache
	 */
	public static function setToCache($name,$value,$expiry){
		try {
			$name = self::validateName($name);
			if(\CacheHandler::existsInCache($name)) \CacheHandler::deleteFromCache($name);
			
			$c = \CacheHandler::Manager()->getItem($name);
			if(is_null($c->get())){
				$c->set($value)->expiresAfter($expiry);
				\CacheHandler::Manager()->save($c);
			}
		} catch(Exception $e){}
	}
	
	/**
	 * Get an object from the cache
	 * 
	 * @access public
	 * @param string $name The key under which the object was stored
	 * @return mixed Returns the cached object
	 */
	public static function getFromCache($name){
		try {
			$name = self::validateName($name);
			if(\CacheHandler::existsInCache($name)){
				$c = \CacheHandler::Manager()->getItem($name);
				
				return $c->get();
			} else {
				return null;
			}
		} catch(Exception $e){
			return null;
		}
	}
	
	/**
	 * Returns whether an object exists in the cache
	 * 
	 * @access public
	 * @param string $name The key under which the object was stored
	 * @return bool Returns true if the object exists
	 */
	public static function existsInCache($name){
		try {
			$name = self::validateName($name);
			return \CacheHandler::Manager()->hasItem($name);
			//return getFromCache($name) != null;
		} catch(Exception $e){
			return false;
		}
	}
	
	/**
	 * Deletes an item from the cache
	 * 
	 * @access public
	 * @param string $name The key under which the object was stored
	 * @return bool Returns true if the object could be removed
	 */
	public static function deleteFromCache($name){
		try {
			$name = self::validateName($name);
			$r = false;
			
			if(\CacheHandler::existsInCache($name)){
				\CacheHandler::Manager()->deleteItem($name);
				$r = true;
			}
			
			return $r;
		} catch(Exception $e){
			return false;
		}
	}

	private static function validateName($name){
		try {
			return str_replace("{","-",str_replace("}","-",str_replace("(","-",str_replace(")","-",str_replace("/","-",str_replace("\\","-",str_replace("@","-",str_replace(":","-",$name))))))));
		} catch(Exception $e){
			return $name;
		}
	}
}

CacheManager::setDefaultConfig(new Config([
	"path" => __DIR__ . "/../../tmp/"
]));
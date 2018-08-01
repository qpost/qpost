<?php

use phpFastCache\CacheManager;
use phpFastCache\Core\phpFastCache;

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
		static $InstanceCache = null;
		if($InstanceCache == null){
			$InstanceCache = CacheManager::getInstance("files");
		}

		return $InstanceCache;
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
		if(CacheHandler::existsInCache($name)) CacheHandler::deleteFromCache($name);
		
		$c = CacheHandler::Manager()->getItem($name);
		if(is_null($c->get())){
			$c->set($value)->expiresAfter($expiry);
			CacheHandler::Manager()->save($c);
		}
	}
	
	/**
	 * Get an object from the cache
	 * 
	 * @access public
	 * @param string $name The key under which the object was stored
	 * @return mixed Returns the cached object
	 */
	public static function getFromCache($name){
		if(CacheHandler::existsInCache($name)){
			$c = CacheHandler::Manager()->getItem($name);
			
			return $c->get();
		} else {
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
		return CacheHandler::Manager()->hasItem($name);
		//return getFromCache($name) != null;
	}
	
	/**
	 * Deletes an item from the cache
	 * 
	 * @access public
	 * @param string $name The key under which the object was stored
	 * @return bool Returns true if the object could be removed
	 */
	public static function deleteFromCache($name){
		$r = false;
		
		if(CacheHandler::existsInCache($name)){
			CacheHandler::Manager()->deleteItem($name);
			$r = true;
		}
		
		return $r;
	}
}
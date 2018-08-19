<?php

use Phpfastcache\CacheManager;
use Phpfastcache\Config\Config;

/**
 * Utility methods to use with caching
 * 
 * @package Cache
 * @author Gigadrive (support@gigadrivegroup.com)
 * @copyright 2016-2018 Gigadrive
 * @link https://gigadrivegroup.com/dev/technologies
 */
class CacheHandler {
	/**
	 * Gets the stash caching pool object
	 * 
	 * @access public
	 * @return Stash\Pool
	 */
	public static function Manager(){
		static $InstanceCache = null;
		if($InstanceCache == null){
			$driver = new Stash\Driver\FileSystem(array());
			$InstanceCache = new Stash\Pool($driver);
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
		$pool = self::Manager();

		$item = $pool->getItem($name);
		
		$item->set($value);
		$item->expiresAfter($expiry);

		$pool->save($item);
	}
	
	/**
	 * Get an object from the cache
	 * 
	 * @access public
	 * @param string $name The key under which the object was stored
	 * @return mixed Returns the cached object
	 */
	public static function getFromCache($name){
		if(self::existsInCache($name)){
			$item = self::Manager()->getItem($name);

			return $item->get();
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
		$pool = self::Manager();

		$item = $pool->getItem($name);
		if(!is_null($item) && $item->isHit()){
			return true;
		} else {
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
		if(self::existsInCache($name)){
			self::Manager()->deleteItem($name);
		} else {
			return false;
		}
	}

	/**
	 * Clears the entire cache
	 * 
	 * @access public
	 */
	public static function clearCache(){
		self::Manager()->clear();
	}
}
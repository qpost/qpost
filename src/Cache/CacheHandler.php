<?php
/**
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpostapp.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://gnu.org/licenses/>
 */

namespace qpost\Cache;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use function sys_get_temp_dir;

class CacheHandler {
	/**
	 * Save an object to the cache
	 *
	 * @param string $name The key under which the object should be stored (if the key is already being used, the old object will be overwritten)
	 * @param mixed $value The object to be stored
	 * @param int $expiry The amount in seconds for which the object should be held in the cache
	 */
	public static function setToCache(string $name, $value, int $expiry): void {
		$cache = self::Manager();
		$item = $cache->getItem($name);
		$item->set($value);
		$item->expiresAfter($expiry);
		$cache->save($item);
	}

	/**
	 * Gets the stash caching pool object
	 *
	 * @return FilesystemAdapter
	 */
	public static function Manager(): FilesystemAdapter {
		static $cache = null;
		if (is_null($cache)) {
			$cache = new FilesystemAdapter("app.cache", 0, sys_get_temp_dir() . "/mcsh-cache");
		}
		return $cache;
	}

	/**
	 * Returns whether an object exists in the cache
	 *
	 * @param string $name The key under which the object was stored
	 * @return bool Returns true if the object exists
	 */
	public static function existsInCache(string $name): bool {
		return !is_null(self::getFromCache($name));
	}

	/**
	 * Get an object from the cache
	 *
	 * @param string $name The key under which the object was stored
	 * @return mixed|null Returns the cached object, null if it does not exist
	 */
	public static function getFromCache(string $name) {
		$cache = self::Manager();
		$item = $cache->getItem($name);
		if ($item->isHit()) {
			return $item->get();
		}
		return null;
	}

	/**
	 * Deletes an item from the cache.
	 *
	 * @param string $name The key under which the object was stored
	 * @return bool Returns true if the object could be removed
	 */
	public static function deleteFromCache(string $name): bool {
		$cache = self::Manager();
		$item = $cache->getItem($name);
		if ($item->isHit()) {
			$cache->deleteItem($name);
			return true;
		}
		return false;
	}

	/**
	 * Clears the entire cache
	 */
	public static function clearCache(): void {
		$cache = self::Manager();
		$cache->clear();
	}
}
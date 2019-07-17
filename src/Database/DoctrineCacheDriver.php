<?php
/**
 * Copyright (C) 2018-2019 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpo.st
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

namespace qpost\Database;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use qpost\Cache\CacheHandler;

class DoctrineCacheDriver extends CacheProvider {
	/**
	 * {@inheritDoc}
	 */
	protected function doContains($id) {
		return CacheHandler::existsInCache($id);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function doDelete($id) {
		return CacheHandler::deleteFromCache($id);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function doFetch($id) {
		$data = CacheHandler::getFromCache($id);

		return !is_null($data) ? $data : false;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function doFlush() {
		CacheHandler::clearCache();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function doGetStats() {
		// TODO
		return [
			Cache::STATS_HITS => 0,
			Cache::STATS_MISSES => 0,
			Cache::STATS_UPTIME => 0,
			Cache::STATS_MEMORY_USAGE => 0,
			Cache::STATS_MEMORY_AVAILABLE => 0
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function doSave($id, $data, $lifeTime = 0) {
		CacheHandler::setToCache($id, $data, $lifeTime);
	}
}
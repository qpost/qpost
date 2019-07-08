<?php

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
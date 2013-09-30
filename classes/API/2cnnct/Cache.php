<?php

/**
 * 2cnnct API Cache
 *
 * @package  api
 * @author   Leon van der Veen <leon@deskbookers.com>
 */
interface API_2cnnct_Cache
{
	/**
	 * Has cache
	 * 
	 * @param string $cacheName
	 * @param int $lifetime
	 * @return bool
	 */
	public function hasCache($cacheName, $lifetime = null);

	/**
	 * Get cache
	 * 
	 * @param string $cacheName
	 * @param int $lifetime
	 * @return mixed
	 */
	public function getCache($cacheName, $lifetime = null);

	/**
	 * Set cache
	 * 
	 * @param string $cacheName
	 * @param mixed $value
	 * @param int $lifetime
	 * @return bool
	 */
	public function setCache($cacheName, $value, $lifetime = null);
}

<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * 2cnnct API File Cache
 *
 * @package  api
 * @author   Leon van der Veen <leon@deskbookers.com>
 */
class API_2cnnct_FileCache implements API_2cnnct_Cache
{
	/**
	 * Cache path
	 * 
	 * @var string
	 */
	protected $path_;

	/**
	 * Lifetime
	 * 
	 * @var int
	 */
	protected $lifetime_ = 60;

	/**
	 * Constructor
	 * 
	 * @param string $path
	 */
	public function __construct($path, $lifetime = null)
	{
		$this->path_ = realpath($path) . '/';
		if ($lifetime !== null)
		{
			$this->lifetime_ = $lifetime;
		}
	}

	/**
	 * Factory
	 * 
	 * @param string $path
	 * @param int $lifetime
	 * @return API_2cnnct_FileCache
	 */
	static public function factory($path, $lifetime = null)
	{
		return new API_2cnnct_FileCache($path, $lifetime);
	}

	/**
	 * Lifetime
	 * 
	 * @param int $lifetime
	 * @return int
	 */
	protected function lifetime($lifetime = null)
	{
		return $lifetime === null ? $this->lifetime_ : $lifetime;
	}

	/**
	 * Cache key
	 * 
	 * @param string $cacheName
	 * @return string
	 */
	protected function cacheKey($cacheName)
	{
		$hash = sha1($cacheName);
		return substr($hash, 0, 2) . '/' . $hash;
	}

	/**
	 * Has cache
	 * 
	 * @param string $cacheName
	 * @param int $lifetime
	 * @return bool
	 */
	public function hasCache($cacheName, $lifetime = null)
	{
		$key = $this->cacheKey($cacheName);
		return (
			file_exists($this->path_ . $key)
			&& (time() - filemtime($this->path_ . $key)) < $this->lifetime($lifetime)
		);
	}

	/**
	 * Get cache
	 * 
	 * @param string $cacheName
	 * @param int $lifetime
	 * @return mixed
	 */
	public function getCache($cacheName, $lifetime = null)
	{
		if ( ! $this->hasCache($cacheName))
		{
			return null;
		}
		try
		{
			// Get file contents and unserialize it
			return unserialize(file_get_contents($this->path_ . $this->cacheKey($cacheName)));
		}
		catch (Exception $e)
		{
			return null;
		}
	}

	/**
	 * Set cache
	 * 
	 * @param string $cacheName
	 * @param mixed $value
	 * @param int $lifetime
	 * @return bool
	 */
	public function setCache($cacheName, $value, $lifetime = null)
	{
		// Serialize data
		$data = serialize($value);

		// Key
		$key = $this->cacheKey($cacheName);

		// Folder exists?
		$dir = dirname($this->path_ . $key);
		if ( ! is_dir($dir))
		{
			mkdir($dir, 0777, true);
		}

		try
		{
			// Store data
			return (bool) file_put_contents($this->path_ . $key, $data, LOCK_EX);
		}
		catch (Exception $e)
		{
			return false;
		}
	}
}

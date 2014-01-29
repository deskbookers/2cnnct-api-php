<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * 2cnnct API
 *
 * @package  api
 * @author   Leon van der Veen <leon@deskbookers.com>
 */
class API_2cnnct_API
{
	/**
	 * Instance
	 * 
	 * @var API_2cnnct_API
	 */
	static protected $instance_ = null;

	/**
	 * Instance
	 * 
	 * @param API_2cnnct_API $instance
	 * @return API_2cnnct_API
	 */
	static public function instance(API_2cnnct_API $instance = null)
	{
		if ($instance !== null) self::$instance_ = $instance;
		return self::$instance_;
	}

	/**
	 * Public key
	 * 
	 * @var string
	 */
	private $publicKey_ = NULL;

	/**
	 * Private key
	 * 
	 * @var string
	 */
	private $privateKey_ = NULL;

	/**
	 * API url
	 * 
	 * @var string
	 */
	private $apiHost_ = NULL;

	/**
	 * API version
	 * 
	 * @var int
	 */
	private $apiVersion_ = 1;

	/**
	 * Locale
	 * 
	 * @var string
	 */
	private $locale_ = NULL;

	/**
	 * Format
	 * 
	 * @var string
	 */
	protected $format_ = 'json';

	/**
	 * Cache
	 * 
	 * @var API_2cnnct_Cache
	 */
	protected $cache_ = null;

	/**
	 * Constructor
	 * 
	 * @param string $publicKey
	 * @param string $privateKey
	 * @param string $apiHost
	 * @param int $resellerID
	 * @param int $apiVersion
	 * @param string $locale
	 * @param string $format
	 * @param API_2cnnct_Cache $cache
	 */
	public function __construct($publicKey, $privateKey, $apiHost, $resellerID, $apiVersion = 1, $locale = null, $format = null, API_2cnnct_Cache $cache = null)
	{
		// Set vars
		$this->publicKey_ = $publicKey;
		$this->privateKey_ = $privateKey;
		$this->apiHost_ = $apiHost;
		$this->apiVersion_ = $apiVersion;
		$this->locale_ = $locale;
		$this->format_ = $format ?: 'json';
		$this->resellerID_ = $resellerID;
		$this->cache_ = $cache;
	}

	/**
	 * Factory
	 * 
	 * @param string $publicKey
	 * @param string $privateKey
	 * @param string $apiHost
	 * @param int $resellerID
	 * @param int $apiVersion
	 * @param string $locale
	 * @param string $format
	 * @param API_2cnnct_Cache $cache
	 */
	static public function factory($publicKey, $privateKey, $apiHost, $resellerID, $apiVersion = 1, $locale = null, $format = null, API_2cnnct_Cache $cache = null)
	{
		return new self($publicKey, $privateKey, $apiHost, $resellerID, $apiVersion, $locale, $format, $cache);
	}

	/**
	 * Build check data
	 * 
	 * Build check data for a method, timestamp and URI.
	 * 
	 * @param string $method
	 * @param int $timestamp
	 * @param string $uri
	 * @param array $post
	 * @return string
	 */
	private function buildCheckData($method, $timestamp, $uri, array $post)
	{
		return $method . "\n" . $timestamp . "\n" . $uri . "\n" . json_encode($post);
	}

	/**
	 * Build check hash
	 * 
	 * @param string $method
	 * @param int $timestamp
	 * @param string $uri
	 * @param array $post
	 * @return string
	 */
	private function buildCheckHash($method, $timestamp, $uri, array $post)
	{
		return hash_hmac('sha512', $this->buildCheckData($method, $timestamp, $uri, $post), $this->privateKey_);
	}

	/**
	 * Build uri
	 * 
	 * @param string $uri
	 * @param array $uriParams
	 * @return string
	 */
	protected function buildUri($uri, array $uriParams = null)
	{
		foreach ( (array) $uriParams as $key => $value)
		{
			if (strlen($value) == 0) throw new Exception('Invalid uri param \'' . $key . '\' given, can not be empty');
			$uri = str_replace('<' . $key . '>', $value, $uri);
		}
		return $uri;
	}

    /**
	 * Post
	 * 
	 * @param array $data
	 * @param string $uri
	 * @param array $uriParams
	 * @return mixed
	 */
	public function post(array $data, $uri, array $uriParams = null)
	{
		// Call method and convert
		if (class_exists('Kohana_Profiler'))
		{
			$token = Profiler::start('2cnnct API', 'POST ' . $uri);
			try
			{
				$r = $this->convert($this->post_($data, $uri, $uriParams));
				Profiler::stop($token);
				return $r;
			}
			catch (Exception $e)
			{
				Profiler::stop($token);
				throw $e;
			}
		}

		return $this->convert($this->post_($data, $uri, $uriParams));
	}
	
	/**
	 * Post (internal)
	 * 
	 * @param array $data
	 * @param string $uri
	 * @param array $uriParams
	 * @return mixed
	 */
	protected function post_(array $data, $uri, array $uriParams = null)
	{
		// Prepare data
		$data = (array) $data;
		foreach ($data as $key => $value)
		{
			$data[$key] = json_encode($value);
		}
		$data['__i18n'] = json_encode($this->locale_);
		$data['__format'] = json_encode($this->format_);
		$data['__resellerID'] = json_encode($this->resellerID_);
		if (is_callable('Logger::longo') && Logger::longo())
		{
			$data['__longo'] = json_encode(Logger::longo()->tags());
		}

		// URI
		$uri = '/api/v' . $this->apiVersion_ . '/' . $this->buildUri($uri, $uriParams);

		// Body
		$query = http_build_query($data);

		// Make request through curl
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, 'https://' . $this->apiHost_ . $uri);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Timestamp: ' . ($timestamp = time()),
			'Authenticate: ' . $this->publicKey_ . ':' . $this->buildCheckHash('POST', $timestamp, $uri, $data),
		));
		$response = curl_exec($ch);
		curl_close($ch);

		// Decode response
		$json = json_decode($response);
		if ( ! is_object($json))
		{
			unset($response);
			throw new API_2cnnct_CallException(500, 'Invalid API response format');
		}
		else
		{
			unset($response);
			if (property_exists($json, 'error') && $json->error)
			{
				throw new API_2cnnct_CallException(
					property_exists($json, 'errorCode') ? $json->errorCode : 500,
					property_exists($json, 'errorMessage') ? $json->errorMessage : 'Error',
					property_exists($json, 'errorTrace') ? $json->errorTrace : null
				);
			}
			else if ( ! property_exists($json, 'result'))
			{
				throw new API_2cnnct_CallException(500, 'Invalid API response format');
			}
		}

		// Return result
		return $json->result;
	}

	/**
	 * Get cached
	 * 
	 * @param array $fields
	 * @param string $uri
	 * @param array $uriParams
	 * @param array $data
	 * @return mixed
	 */
	public function getCached(array $fields, $uri, array $uriParams = null, array $data = null, API_2cnnct_Cache $cache = null, $lifetime = null)
	{
		// Cache
		if ($cache === null)
		{
			$cache = $this->cache_;
		}
		if ($cache === null)
		{
			throw new Exception('No valid API Cache instance is provided');
		}

		// Cache name
		$cacheName = $this->buildUri($uri, $uriParams) . '(' . json_encode($fields) . ')(' . json_encode($data) . ')';

		// Has cache?
		if ($cache->hasCache($cacheName, $lifetime))
		{
			return $this->convert($cache->getCache($cacheName, $lifetime));
		}

		// Get list and cache it
		$data = $this->get_($fields, $uri, $uriParams, $data);
		$cache->setCache($cacheName, $data, $lifetime);

		// Return data
		return $this->convert($data);
	}

	/**
	 * Get
	 * 
	 * @param array $fields
	 * @param string $uri
	 * @param array $uriParams
	 * @param array $data
	 * @return mixed
	 */
	public function get(array $fields, $uri, array $uriParams = null, array $data = null)
	{
		// Call method and convert
		if (class_exists('Kohana_Profiler'))
		{
			$__data = [];
			foreach ( (array) $data as $key => $val) if (strpos($key, '$') === 0) $__data[$key] = $val;
			$token = Profiler::start('2cnnct API', 'GET ' . $uri . ' ' . json_encode($__data));
			try
			{
				$r = $this->convert($this->get_($fields, $uri, $uriParams, $data));
				Profiler::stop($token);
				return $r;
			}
			catch (Exception $e)
			{
				Profiler::stop($token);
				throw $e;
			}
		}

		return $this->convert($this->get_($fields, $uri, $uriParams, $data));
	}

	/**
	 * Get (internal)
	 * 
	 * @param array $fields
	 * @param string $uri
	 * @param array $uriParams
	 * @param array $data
	 * @return mixed
	 */
	protected function get_(array $fields, $uri, array $uriParams = null, array $data = null)
	{
		// Prepare data
		$data = (array) $data;
		foreach ($data as $key => $value)
		{
			$data[$key] = json_encode($value);
		}
		$data['__fields'] = json_encode($fields);
		$data['__i18n'] = json_encode($this->locale_);
		$data['__format'] = json_encode($this->format_);
		$data['__resellerID'] = json_encode($this->resellerID_);
		if (is_callable('Logger::longo') && Logger::longo())
		{
			$data['__longo'] = json_encode(Logger::longo()->tags());
		}

		// URI
		$uri = '/api/v' . $this->apiVersion_ . '/' . $this->buildUri($uri, $uriParams);
		$query = http_build_query($data);
		if (!empty($query)) $uri .= '?' . $query;

		// Make request through curl
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, 'https://' . $this->apiHost_ . $uri);
		curl_setopt($ch, CURLOPT_HTTPGET, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Timestamp: ' . ($timestamp = time()),
			'Authenticate: ' . $this->publicKey_ . ':' . $this->buildCheckHash('GET', $timestamp, $uri, []),
		));
		$response = curl_exec($ch);
		curl_close($ch);

		// Decode response
		$json = json_decode($response);
		if ( ! is_object($json))
		{
			unset($response);
			throw new API_2cnnct_CallException(500, 'Invalid API response format');
		}
		else
		{
			unset($response);
			if (property_exists($json, 'error') && $json->error)
			{
				throw new API_2cnnct_CallException(
					property_exists($json, 'errorCode') ? $json->errorCode : 500,
					property_exists($json, 'errorMessage') ? $json->errorMessage : 'Error',
					property_exists($json, 'errorTrace') ? $json->errorTrace : null
				);
			}
			elseif ( ! property_exists($json, 'result'))
			{
				throw new API_2cnnct_CallException(500, 'Invalid API response format');
			}
		}

		// Return result
		return $json->result;
	}

	/**
	 * Convert
	 * 
	 * @param mixed $data
	 * @return mixed
	 */
	protected function convert($data)
	{
		if (is_array($data))
		{
			$array = array();
			foreach ($data as $item)
			{
				$array[] = $this->convert($item);
			}
			return $array;
		}
		elseif (is_object($data))
		{
			$object = array();
			foreach ($data as $key => $val)
			{
				$object[$key] = $this->convert($val);
			}
			return new API_2cnnct_Object($this, $object);
		}
		else
		{
			return $data;
		}
	}
}

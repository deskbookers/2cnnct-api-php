<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * 2cnnct API Object
 *
 * @package  api
 * @author   Leon van der Veen <leon@deskbookers.com>
 */
class API_2cnnct_Object implements IteratorAggregate, JsonSerializable, Countable 
{
	/**
	 * API
	 * 
	 * @var API_2cnnct_API
	 */
	protected $api_ = null;

	/**
	 * Data
	 * 
	 * @var array
	 */
	protected $data_ = null;

	/**
	 * As array
	 * 
	 * @see toArray
	 * @return array
	 */
	public function as_array()
	{
		return $this->toArray();
	}

	/**
	 * To array
	 * 
	 * @param bool $deep
	 * @return array
	 */
	public function toArray($deep = false)
	{
		if ($deep)
		{
			return static::toArrayDeep( (array) $this->data_);
		}
		
		return (array) $this->data_;
	}

	/**
	 * To array deep
	 * 
	 * @param mixed $data
	 * @return mixed
	 */
	protected static function toArrayDeep($data)
	{
		if (is_object($data))
		{
			if ($data instanceof API_2cnnct_Object)
			{
				return $data->toArray(true);
			}
			else
			{
				return static::toArrayDeep( (array) $data);
			}
		}
		else if (is_array($data))
		{
			foreach ($data as $k => $v)
			{
				$data[$k] = static::toArrayDeep($v);
			}
			return $data;
		}
		return $data;
	}

	/**
	 * Count
	 * 
	 * @return int
	 */
	public function count()
	{
		return count($this->data_);
	}

	/**
	 * JSON Serialize
	 * 
	 * @return object
	 */
	public function jsonSerialize()
	{
		return $this->data_;
	}

	/**
	 * Iterator
	 * 
	 * @return ArrayIterator 
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->data_);
	}

	/**
	 * Constructor
	 * 
	 * @param API_2cnnct_API $api
	 * @param array $data
	 */
	public function __construct(API_2cnnct_API $api, array $data = null)
	{
		// Set vars
		$this->api_ = $api;
		$this->data_ = $data;
	}

	/**
	 * Loaded
	 * 
	 * @return bool
	 */
	public function loaded()
	{
		return $this->data_ !== null && count($this->data_) > 0;
	}

	/**
	 * Get
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		if ( ! $this->has($key))
		{
			throw new DBException('Undefined property \':property\'', array(':property' => $key));
		}
		return $this->data_[$key];
	}

	/**
	 * Set
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @return This
	 */
	public function set($key, $value)
	{
		$this->data_[$key] = $value;
		return $this;
	}

	/**
	 * Get or default
	 * 
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getOrDefault($key, $default = null)
	{
		if ( ! $this->has($key)) return $default;
		else return $this->data_[$key];
	}

	/**
	 * __get
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->get($key);
	}

	/**
	 * __set
	 * 
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * __isset
	 * 
	 * @param string $key
	 * @return boool
	 */
	public function __isset($key)
	{
		return $this->has($key);
	}

	/**
	 * Has
	 * 
	 * @param string $key
	 * @return bool
	 */
	public function has($key)
	{
		return array_key_exists($key, $this->data_);
	}
}

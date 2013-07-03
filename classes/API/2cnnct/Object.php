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
	 * To array
	 * 
	 * @return array
	 */
	public function toArray()
	{
		return (array) $this->data_;
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

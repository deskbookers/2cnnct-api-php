<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * 2cnnct API Call Exception
 * 
 * @package  api
 * @author   Leon van der Veen <leon@deskbookers.com>
 */
class API_2cnnct_CallException extends Exception
{
	/**
	 * Trace
	 * 
	 * @var array
	 */
	protected $trace_ = null;

	/**
	 * Extra
	 * 
	 * @var array
	 */
	protected $extra_ = null;

	/**
	 * Constructor
	 * 
	 * @param int $code
	 * @param string $errorMessage
	 * @param array $trace
	 * @param array $extra
	 */
	public function __construct($code, $errorMessage, $trace = null, array $extra = null)
	{
		parent::__construct($errorMessage, $code);
		$this->trace_ = $trace;
		$this->extra_ = $extra;
	}

	/**
	 * Get extra
	 * 
	 * @param string $key
	 * @param mixed $default
	 * @return array
	 */
	public function extra($key = null, $default = null)
	{
		if (func_num_args() > 0)
		{
			return Arr::get( (array) $this->extra_, $key, $default);
		}
		return (array) $this->extra_;
	}

	/**
	 * Trace
	 * 
	 * @return array
	 */
	public function trace()
	{
		return (array) $this->trace_;
	}
}

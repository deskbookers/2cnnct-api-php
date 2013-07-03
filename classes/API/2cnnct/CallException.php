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
	 * Constructor
	 * 
	 * @param int $code
	 * @param string $errorMessage
	 * @param array $trace
	 */
	public function __construct($code, $errorMessage, $trace = null)
	{
		parent::__construct($errorMessage, $code);
		$this->trace_ = $trace;
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

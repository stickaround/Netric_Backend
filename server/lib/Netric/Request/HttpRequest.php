<?php
/**
 * Http Request
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Request;

class HttpRequest implements RequestInterface
{
	/**
	 * Array of stores to get params from
	 *
	 * @var array
	 */
	private $httpStores = null;

	/**
	 * Initialize request object variables
	 */
	public function __constructor()
	{
		if (function_exists("apache_request_headers"))
			$headers = \apache_request_headers();
		else
			$headers = array();

		$this->httpStores = array(
			$headers, $_COOKIE, $_POST, $_GET
		);
	}

	/**
	 * Get a request param by name
	 *
	 * @param string $name The name of the param to get
	 */
	public function getParam($name)
	{
		// Check through any http request objects
		foreach ($this->httpStores as $httpStore)
		{
			// Return the first match
			if (isset($httpStore[$name]))
			{
				return $httpStore[$name];
			}
		}

		// Not found
		return null;
	}

	/**
	 * Get all params in an associative array
	 *
	 * @return array
	 */
	public function getParams()
	{
		$ret = array();

		// Check through any http request objects
		foreach ($this->httpStores as $httpStore)
		{
			foreach ($httpStore as $pname=>$pval)
			{
				// Over-write duplicates
				$ret[$pname] = $pval;
			}
		}

		return $ret;
	}

	/**
	 * Get the raw body of the request
	 *
	 * @return string
	 */
	public function getBody()
	{
		return file_get_contents("php://input");
	}
}

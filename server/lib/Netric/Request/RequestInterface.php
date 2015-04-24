<?php
/**
 * Request interface
 */
namespace Netric\Request;

interface RequestInterface
{
	/**
	 * Get a request param by name
	 *
	 * @param string $name The name of the param to get
	 */
	public function getParam($name);

	/**
	 * Get all params in an associative array
	 *
	 * @return array
	 */
	public function getParams();

	/**
	 * Get the raw body of the request
	 *
	 * @return string
	 */
	public function getBody();
}
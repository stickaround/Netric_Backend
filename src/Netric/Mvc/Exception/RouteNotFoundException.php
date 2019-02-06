<?php
namespace Netric\Mvc\Exception;

use RuntimeException;

/**
 * Exception thrown when the client attemtps to load a route that does not exist.
 *
 * @package Netric\Mvc\Exception
 */
class RouteNotFoundException extends RuntimeException
{
}

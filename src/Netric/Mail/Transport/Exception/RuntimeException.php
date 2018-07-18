<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Mail\Transport\Exception;

use Netric\Mail\Exception;

/**
 * Exception for Netric\Mail component.
 */
class RuntimeException extends Exception\RuntimeException implements ExceptionInterface
{
}
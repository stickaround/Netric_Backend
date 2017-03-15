<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Mail\Storage\Exception;

use Netric\Mail\Exception;

/**
 * Exception for Netric\Mail component.
 */
class OutOfBoundsException extends Exception\OutOfBoundsException implements ExceptionInterface
{
}

<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Mail\Transport\Exception;

use Netric\Mail\Exception;

/**
 * Exception for Netric\Mail\Transport component.
 */
class DomainException extends Exception\DomainException implements ExceptionInterface
{
}

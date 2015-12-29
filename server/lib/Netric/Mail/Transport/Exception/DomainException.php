<?php
/**
 * Netric Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Netric Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Netric\Mail\Transport\Exception;

use Netric\Mail\Exception;

/**
 * Exception for Netric\Mail\Transport component.
 */
class DomainException extends Exception\DomainException implements ExceptionInterface
{
}

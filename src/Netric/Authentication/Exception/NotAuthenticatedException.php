<?php

declare(strict_types=1);

namespace Netric\Authentication\Exception;

use RuntimeException;

/**
 * Thrown when a caller tries to get an identity before a user has been authenticated
 */
class NotAuthenticatedException extends RuntimeException
{
}

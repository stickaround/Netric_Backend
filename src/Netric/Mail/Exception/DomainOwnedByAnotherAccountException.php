<?php

declare(strict_types=1);

namespace Netric\Mail\Exception;

use RuntimeException;

class DomainOwnedByAnotherAccountException extends RuntimeException implements ExceptionInterface
{
}

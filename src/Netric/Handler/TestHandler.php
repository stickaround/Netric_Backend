<?php

declare(strict_types=1);

namespace Netric\Handler;

use NetricApi\TestIf;

class TestHandler implements TestIf
{
    public function ping(): string
    {
        return "Hello";
    }
}

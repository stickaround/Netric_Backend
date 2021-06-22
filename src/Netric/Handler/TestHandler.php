<?php

namespace Netric\Handler;

use NetricApi\TestIf;

class TestHandler implements TestIf
{
    public function ping(): string
    {
        return "Hello";
    }
}

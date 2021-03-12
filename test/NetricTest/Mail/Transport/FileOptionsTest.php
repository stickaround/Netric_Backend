<?php

namespace NetricTest\Mail\Transport;

use Netric\Mail\Transport\FileOptions;
use PHPUnit\Framework\TestCase;

/**
 * @group      Netric_Mail
 */
class FileOptionsTest extends TestCase
{
    public function setUp(): void
    {
        $this->options = new FileOptions();
    }

    public function testPathIsSysTempDirByDefault()
    {
        $this->assertEquals(sys_get_temp_dir(), $this->options->getPath());
    }

    public function testDefaultCallbackIsSetByDefault()
    {
        $callback = $this->options->getCallback();
        $this->assertIsCallable($callback);
        $test     = call_user_func($callback, '');
        $this->assertMatchesRegularExpression('#^NetricMail_\d+_\d+\.eml$#', $test);
    }

    public function testPathIsMutable()
    {
        $original = $this->options->getPath();
        $this->options->setPath(__DIR__);
        $test     = $this->options->getPath();
        $this->assertNotEquals($original, $test);
        $this->assertEquals(__DIR__, $test);
    }

    public function testCallbackIsMutable()
    {
        $original = $this->options->getCallback();
        $new      = function ($transport) {
        };
        $this->options->setCallback($new);
        $test     = $this->options->getCallback();
        $this->assertNotSame($original, $test);
        $this->assertSame($new, $test);
    }
}

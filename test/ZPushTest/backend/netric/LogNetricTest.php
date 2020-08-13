<?php

/**
 * Test the the custom netric backend for ActiveSync
 */

namespace ZPushTest\backend\netric;

use NetricTest\Bootstrap;
use PHPUnit\Framework\TestCase;
use Netric\Mail\Transport\InMemory;
use Netric\Mail\SenderService;

// Add all z-push required files
require_once("z-push.includes.php");

// Include config
require_once(dirname(__FILE__) . '/../../../../config/zpush.config.php');

// Include backend classes
require_once('backend/netric/lognetric.php');


class LogNetricTest extends TestCase
{
    /**
     * Test logging errors
     */
    public function testLog()
    {
        // By default the logging is set to LOG_ERR
        $log = new \LogNetric();
        $log->Log(LOGLEVEL_ERROR, "My Test");
        $this->assertNotEmpty($log->getLastMessage());
    }
}

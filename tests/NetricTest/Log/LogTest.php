<?php
/**
 * Test entity/object class
 */
namespace NetricTest\Log;

use Netric\Log\Log;
use PHPUnit\Framework\TestCase;
use Netric\Config\Config;

class LogTest extends TestCase
{
    /**
     * Tennant account
     *
     * @var \Netric\Account\Account
     */
    private $account = null;
    
    /**
     * Administrative user
     *
     * @var \Netric\User
     */
    private $user = null;
    
     /**
     * Account log
     *
     * @var \Netric\Log
     */
    private $log = null;

    /**
     * Setup each test
     */
    protected function setUp()
    {
        // Create a silent writer
        $this->log = new Log(new Config(['writer'=>'null']));
    }
    
    /**
     * Test logging errors
     */
    public function testLogError()
    {
        // By default the logging is set to LOG_ERR
        $ret = $this->log->error("My Test");
        $this->assertNotEquals($ret, false);
    }
}

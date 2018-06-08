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
        /*
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->user = $this->account->getUser(\Netric\Entity\ObjType\UserEntity::USER_SYSTEM);
        $this->log = $this->account->getServiceManager()->get("Log");
        */

        $configValues = array(
            'log'=> __DIR__ . '/../../data/tmp/log'
        );
        $config = new Config($configValues);

        $this->log = new Log($config);
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

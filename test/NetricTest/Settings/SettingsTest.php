<?php

/**
 * Test the Settings service factory
 */

namespace NetricTest\Settings;

use Netric\Settings;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Account\Account;
use Netric\Entity\ObjType\UserEntity;
use Netric\Settings\SettingsFactory;

/**
 * @group integration
 */
class SettingsTest extends TestCase
{
    /**
     * Settings service to work with
     *
     * @var Settings
     */
    private $settings = null;

    /**
     * Test user
     *
     * @var Netric\Entity\ObjType\UserEntity
     */
    private $user = null;

    /**
     * Account that is currently used for running the unit tests
     */
    protected Account $account;

    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
        $sm = $this->account->getServiceManager();
        $this->settings = $sm->get(SettingsFactory::class);
        $this->user = $this->account->getUser(UserEntity::USER_SYSTEM);
    }

    public function testGetAndSet()
    {
        $testVal = "MyValue";
        $ret = $this->settings->set("utest/val", $testVal, $this->account->getAccountId());
        $this->assertTrue($ret);

        $this->assertEquals($testVal, $this->settings->get("utest/val", $this->account->getAccountId()));
    }

    public function testGetAndSetForUser()
    {
        $testVal = "MyValue";
        $ret = $this->settings->setForUser($this->user, "utest/val", $testVal);
        $this->assertTrue($ret);

        $this->assertEquals($testVal, $this->settings->getForUser($this->user, "utest/val"));
    }

    /**
     * Make sure values are being cached
     */
    public function testCache()
    {
        $testVal = "MyValue";
        $key = "utest/val1";
        $this->settings->set($key, $testVal, $this->account->getAccountId());

        // Test to see if it is cached
        $refSettings = new \ReflectionObject($this->settings);
        $getCached = $refSettings->getMethod("getCached");
        $getCached->setAccessible(true);
        $this->assertEquals($testVal, $getCached->invoke($this->settings, $this->account->getAccountId(), $key));
    }

    /**
     * By pass cache to make sure it is getting saved right to the database
     */
    public function testDb()
    {
        $testVal = "MyValue";
        $key = "utest/val2";
        $this->settings->set($key, $testVal, $this->account->getAccountId());

        // Test to see if it is cached
        $refSettings = new \ReflectionObject($this->settings);
        $getDb = $refSettings->getMethod("getDb");
        $getDb->setAccessible(true);
        $this->assertEquals($testVal, $getDb->invoke($this->settings, $key));
    }
}

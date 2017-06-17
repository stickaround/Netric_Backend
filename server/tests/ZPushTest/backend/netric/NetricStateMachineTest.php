<?php
/**
 * Test searching entities
 */
namespace ZPushTest\backend\netric;

use PHPUnit\Framework\TestCase;
use Netric\Log\LogInterface;

// Add all z-push required files
require_once("z-push.includes.php");

// Include config
require_once(dirname(__FILE__) . '/../../../../config/zpush.config.php');

// Include backend classes
require_once('backend/netric/netric.php');
require_once('backend/netric/netricstatemachine.php');

/**
 * Class NetricStateMachineTest
 * @package ZPushTest\backend\netric
 * @group integration
 */
class NetricStateMachineTest extends TestCase
{
    /**
     * Handle to account
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Common constants used
     *
     * @cons string
     */
    const TEST_USER = "test_statemachine";
    const TEST_DEVID = "LKJGFBIGLK654";

    /**
     * DB state machine
     *
     * @var \NetricStateMachine
     */
    private $stateMachine = null;

    /**
     * Setup each test
     */
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();

        // Get dependencies
        $log = $this->getMockBuilder(LogInterface::class)->getMock();
        $db = $this->account->getServiceManager()->get("Db");
        $cache = $this->account->getServiceManager()->get("Cache");
        $settings = $this->account->getServiceManager()->get("Netric/Settings/Settings");

        // Initialize zpush - copied from zpush index file
        if (!defined ( 'REAL_BASE_PATH' )) {
            \ZPush::CheckConfig();
        }

        // Setup the provider service
        $this->stateMachine = new \NetricStateMachine($log, $db, $cache, $settings);

        // Clean up any states
        $this->stateMachine->CleanStates(self::TEST_DEVID, 'test', false);
    }

    /**
     * Cleanup
     */
    protected function tearDown()
    {
        $this->stateMachine->UnLinkUserDevice(self::TEST_USER, self::TEST_DEVID);
        $this->stateMachine->CleanStates(self::TEST_DEVID, 'test', false);
        $this->stateMachine->SetStateVersion(\NetricStateMachine::SUPPORTED_STATE_VERSION);
    }

    public function testLinkUserDevice()
    {
        $ret = $this->stateMachine->LinkUserDevice(self::TEST_USER, self::TEST_DEVID);
        $this->assertTrue($ret);
    }

    public function testUnLinkUserDevice()
    {
        // The device should not be linked so the first pass will be false
        $this->assertFalse(
            $this->stateMachine->UnLinkUserDevice(self::TEST_USER, self::TEST_DEVID));

        // Link the device
        $this->stateMachine->LinkUserDevice(self::TEST_USER, self::TEST_DEVID);

        // Now that they are linked, unlinking should return true
        $this->assertTrue($this->stateMachine->UnLinkUserDevice(self::TEST_USER, self::TEST_DEVID));
    }

    public function testGetAllDevices()
    {
        // Add a test stat
        $this->stateMachine->SetState([], self::TEST_DEVID, 'test');

        $allDevices = $this->stateMachine->GetAllDevices();
        $this->assertGreaterThanOrEqual(1, count($allDevices));
    }

    public function testGetAllDevicesForUser()
    {
        // Link a test device
        $this->stateMachine->LinkUserDevice(self::TEST_USER, self::TEST_DEVID);
        $allDevices = $this->stateMachine->GetAllDevices(self::TEST_USER);
        $this->assertEquals(self::TEST_DEVID, $allDevices[0]);
    }

    public function testGetAllStatesForDevice()
    {
        // Add a test stat
        $this->stateMachine->SetState([], self::TEST_DEVID, 'test');

        $states = $this->stateMachine->GetAllStatesForDevice(self::TEST_DEVID);
        $this->assertEquals(1, count($states));
    }

    public function testSetStateVersion()
    {
        $testOnlyVersion = "utest";
        $this->stateMachine->SetStateVersion($testOnlyVersion);
        $this->assertEquals($testOnlyVersion, $this->stateMachine->GetStateVersion());
    }

    public function testGetStateVersion()
    {
        $this->assertEquals(\NetricStateMachine::SUPPORTED_STATE_VERSION, $this->stateMachine->GetStateVersion());
    }

    public function testCleanStates()
    {
        // Add a test stat
        $this->stateMachine->SetState([], self::TEST_DEVID, 'test');

        // Clean all
        $this->stateMachine->CleanStates(self::TEST_DEVID, 'test', false);

        // Make sure no states exist
        $states = $this->stateMachine->GetAllStatesForDevice(self::TEST_DEVID);
        $this->assertEquals(0, count($states));
    }

    public function testGetState()
    {
        $state = [['test'=>'ZPush can put whatever it wants']];

        // Add a test state
        $this->stateMachine->SetState($state, self::TEST_DEVID, 'test');

        $loadedState = $this->stateMachine->GetState(self::TEST_DEVID, 'test', false);

        $this->assertEquals($state, $loadedState);
    }

    public function testGetStateHash()
    {
        // Add a test state
        $this->stateMachine->SetState([], self::TEST_DEVID, 'test');
        $hash = $this->stateMachine->GetStateHash(self::TEST_DEVID, 'test');
        $this->assertNotNull($hash);
    }
}
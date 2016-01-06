<?php
/**
 * Test account setup functions
 */
namespace NetricTest\Account;

use Netric\Account\Setup;
use Netric\Account\AccountIdentityMapper;
use PHPUnit_Framework_TestCase;

class SetupTest extends PHPUnit_Framework_TestCase
{
    /**
     * Account identity mapper used for testing
     *
     * @var AccountIdentityMapper
     */
    private $mapper = null;

    /**
     * Cache interface
     *
     * @var \Netric\Cache\CacheInterface
     */
    private $cache = null;

    /**
     * Setup each test
     */
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();

        $this->cache = $this->account->getServiceManager()->get("Cache");
        $dataMapper = $this->account->getServiceManager()->get("Application_DataMapper");

        $this->mapper = new AccountIdentityMapper($dataMapper, $this->cache);
    }

    /**
     * Make sure we can initialize a new account
     */
    public function testInitialize()
    {
        // TODO: test creating a new account
    }

    /**
     * Make sure we can update an existing account to the latest version/revision
     */
    public function testUpdate()
    {
        // TODO: test updating an existing account
    }
}

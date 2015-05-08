<?php
/**
 * Test entity  loader class that is responsible for creating and initializing exisiting objects
 */
namespace NetricTest\Account;

use Netric;
use PHPUnit_Framework_TestCase;

class AccountIdentityMapperTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tennant account
     *
     * @var \Netric\Account
     */
    private $account = null;

    /**
     * Identity mapper used for testing
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

        $this->mapper = new Netric\Account\AccountIdentityMapper($dataMapper, $this->cache);
    }

    public function testLoadById()
    {
        $application = $this->account->getApplication();

        // First reset cache to make sure the mapper is setting it correctly
        $this->cache->delete("netric/account/" . $this->account->getId());
        
        // Setup Reflection Methods
        $refIm = new \ReflectionObject($this->mapper);
        $loadFromCache = $refIm->getMethod("loadFromCache");
        $loadFromCache->setAccessible(true);
        $loadFromMemory = $refIm->getMethod("loadFromMemory");
        $loadFromMemory->setAccessible(true);
        $propCache = $refIm->getProperty("cache");
        $propCache->setAccessible(true);
        $propAppDm = $refIm->getProperty("appDm");
        $propAppDm->setAccessible(true);

        // Make sure cache initially returns false
        $args = array($this->account->getId(), &$this->account);
        $this->assertFalse($loadFromCache->invokeArgs($this->mapper, $args));

        // Make sure memory initially returns false
        $args = array($this->account->getId(), &$this->account);
        $this->assertFalse($loadFromMemory->invokeArgs($this->mapper, $args));

        // Test loading existing account which should cache it
        $testAccount = $this->mapper->loadById($this->account->getId(), $application);
        $this->assertEquals($this->account->getId(), $testAccount->getId());

        // Make sure cache returns true
        $args = array($this->account->getId(), &$this->account);
        $this->assertTrue($loadFromCache->invokeArgs($this->mapper, $args));

        // Make sure memory returns true
        $args = array($this->account->getId(), &$this->account);
        $this->assertNotNull($loadFromMemory->invokeArgs($this->mapper, $args));

        // Unset the datamapper so we can test memory and cache
        $propAppDm->setValue($this->mapper, null);

        // Make sure we are loading from memory by disabling the cache
        $propCache->setValue($this->mapper, null);
        $testAccount =$this->mapper->loadById($this->account->getId(), $application);
        $this->assertEquals($this->account->getId(), $testAccount->getId());
        $propCache->setValue($this->mapper, $this->cache); // re-enable

        // Make sure the cache is working by disabling the loadedAccounts
        $loadedAccounts = $refIm->getProperty("loadedAccounts");
        $loadedAccounts->setAccessible(true);
        $loadedAccounts->setValue($this->mapper, null);
        $testAccount = $this->mapper->loadById($this->account->getId(), $application);
        $this->assertEquals($this->account->getId(), $testAccount->getId());
    }

    public function testLoadByName()
    {
        $application = $this->account->getApplication();

        // First reset cache to make sure the mapper is setting it correctly
        $this->cache->delete("netric/account/nametoidmap/" . $this->account->getName());

        // Test loading existing account which should cache it
        $testAccount = $this->mapper->loadByName($this->account->getName(), $application);
        $this->assertEquals($this->account->getId(), $testAccount->getId());

        // Check local memory map
        $propNameToIdMap = new \ReflectionProperty($this->mapper, "nameToIdMap");
        $propNameToIdMap->setAccessible(true);
        $vals = $propNameToIdMap->getValue($this->mapper);
        $this->assertEquals($this->account->getId(), $vals[$this->account->getName()]);

        // Unset the datamapper so we can test memory and cache
        $propAppDm = new \ReflectionProperty($this->mapper, "appDm");
        $propAppDm->setAccessible(true);
        $propAppDm->setValue($this->mapper, null);

        // Make sure we are loading from memory by disabling the cache
        $propCache = new \ReflectionProperty($this->mapper, "cache");
        $propCache->setAccessible(true);
        $propCache->setValue($this->mapper, null);
        $testAccount = $this->mapper->loadByName($this->account->getName(), $application);
        $this->assertEquals($this->account->getId(), $testAccount->getId());

        // Make sure the cache is working by disabling local memory cache
        $propCache->setValue($this->mapper, $this->cache);
        $propNameToIdMap->setValue($this->mapper, null);
        $testAccount = $this->mapper->loadByName($this->account->getName(), $application);
        $this->assertEquals($this->account->getId(), $testAccount->getId());
    }
}

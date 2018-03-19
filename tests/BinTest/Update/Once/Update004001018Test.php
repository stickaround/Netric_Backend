<?php
/**
 * Make sure the bin/scripts/update/once/004/001/018.php script works
 */
namespace BinTest\Update\Once;

use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Authentication\AuthenticationServiceFactory;
use Netric\Console\BinScript;
use PHPUnit\Framework\TestCase;

class Update004001018Test extends TestCase
{
    /**
     * Handle to account
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Path to the script to test
     *
     * @var string
     */
    private $scriptPath = null;

    /**
     * Test entities that should be cleaned up on tearDown
     *
     * @var EntityInterface[]
     */
    private $testEntities = [];

    /**
     * Setup each test
     */
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/once/004/001/018.php";
    }

    /**
     * Cleanup after a test runs
     */
    protected function tearDown()
    {
        // Cleanup any test entities
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        foreach ($this->testEntities as $entity) {
            $loader->delete($entity, true);
        }
    }

    /**
     * Make sure the file exists
     *
     * This is more a test of the test to make sure we set the path right, but why
     * not just use unit tests for our tests? :)
     */
    public function testExists()
    {
        $this->assertTrue(file_exists($this->scriptPath), $this->scriptPath . " not found!");
    }

    /**
     * At a basic level, make sure we can run without throwing any exceptions
     */
    public function testRun()
    {
        $serviceManager = $this->account->getServiceManager();
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $entityDefinitionLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);
        $db = $serviceManager->get(RelationalDbFactory::class);

        $objType = "user";
        $objectsTable = "objects_$objType";
        $entityDefinitionLoader->clearCache($objType);
        $entity = $entityLoader->create($objType);
        $entity->setValue("name", "UnitTestUser");
        $entity->setValue("email", "unittest@netric.com");
        $entity->setValue("password", "unittestpassword");
        $entityLoader->save($entity);
        $this->testEntities[] = $entity;

        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // Authenticate the user
        $authService = $serviceManager->get(AuthenticationServiceFactory::class);
        $sessionStr = $authService->authenticate("unittest@netric.com", "unittestpassword");

        $this->assertNotNull($sessionStr);

        // Test the user if it was moved to the new objects table
        $def = $entityDefinitionLoader->get($objType);
        $movedEntity = $entityLoader->get($objType, $entity->getId());

        $this->assertEquals($def->getTable(), $objectsTable);
        $this->assertTrue($db->tableExists($objectsTable));
        $this->assertEquals($entity->getName(), $movedEntity->getName());
    }
}
<?php
/**
 * Make sure the bin/scripts/update/once/004/001/024.php script works
 */
namespace BinTest\Update\Once;

use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\DataMapper\DataMapperFactory;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Entity\DataMapper\DataMapperFactory as EntityDataMapperFactory;
use Netric\Console\BinScript;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Permissions\Dacl;
use PHPUnit\Framework\TestCase;

class Update004001024Test extends TestCase
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
     * Test entity to make sure we update
     *
     * @var EntityInterface
     */
    private $testEntity = null;


    /**
     * Setup each test
     */
    public function setUp(): void
{
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/once/004/001/024.php";
        $serviceManager = $this->account->getServiceManager();
        $definitionDataMapper = $serviceManager->get(DataMapperFactory::class);

        // Pre-create the objects table not attached with pkey on id like it was pre this update
        $db = $serviceManager->get(RelationalDbFactory::class);
        $db->query("CREATE TABLE objects_utest_preguid () INHERITS (objects)");
        $db->query("CREATE TABLE objects_utest_preguid_act(
                      CONSTRAINT objects_utest_preguid_act_pkey PRIMARY KEY (id)
                    ) INHERITS (objects_utest_preguid)");

        // Create a new entity type and modify the table to match pre 4.1.24
        $testType = new EntityDefinition('utest_preguid');
        $testType->setTitle("Unit Test Update");
        $testType->setSystem(false);
        $testType->setDacl(new Dacl());
        $definitionDataMapper->save($testType);

        // Create test entity
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $testEntity = $entityLoader->create('utest_preguid');
        $entityLoader->save($testEntity);
        $this->testEntity = $testEntity;
    }

    /**
     * Cleanup after a test runs
     */
    public function tearDown(): void
{
        $serviceManager = $this->account->getServiceManager();
        $definitionDataMapper = $serviceManager->get(DataMapperFactory::class);

        // delete entities
        if ($this->testEntity) {
            $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
            $entityLoader->delete($this->testEntity, true);
        }

        // cleanup definition
        if ($definitionDataMapper->fetchByName('utest_preguid')) {
            $definitionDataMapper->deleteDef($definitionDataMapper->fetchByName('utest_preguid'));
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

        // Null guid to activate the update
        $db = $serviceManager->get(RelationalDbFactory::class);
        $db->query("UPDATE  objects_utest_preguid_act SET guid=null");

        // Run the 024.php update once script to scan the objects_moved table and update the referenced entities
        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // Make sure a guid was set (clear cache first then reload fresh)
        $entityLoader->clearCache("utest_preguid", $this->testEntity->getId());
        $loaded = $entityLoader->get("utest_preguid", $this->testEntity->getId());
        $this->assertNotEmpty($loaded->getGuid());

        // Make sure index was created for the _act table
        $this->assertTrue($db->indexExists('objects_utest_preguid_act_id_idx'));

        // Make sure index was created for the _del table
        $this->assertTrue($db->indexExists('objects_utest_preguid_del_id_idx'));
    }
}

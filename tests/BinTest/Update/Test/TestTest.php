<?php
namespace BinTest\Update\Test;

use Netric\EntityQuery;
use Netric\EntityGroupings\Group;
use Netric\EntityGroupings\EntityGroupings;
use Netric\Console\BinScript;
use Netric\Entity\EntityInterface;
use PHPUnit\Framework\TestCase;

use Netric\Db\DbFactory;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperFactory;
use Netric\Application\Schema\SchemaDataMapperPgsql;
use Netric\Application\Schema\SchemaProperty;

/**
 * Make sure the bin/scripts/update/once/004/001/015.php script works
 *
 * @group integration
 */
class Update004001015Test extends TestCase
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
     * Entities to clean up
     *
     * @var EntityInterface[]
     */
    private $testEntities = [];

    /**
     * Groups to clean up inside the object_groupings table
     *
     * @var groupId[]
     */
    private $testObjectGroupings = [];

    /**
     * Tables that were created during unit testing
     *
     * @var groupId[]
     */
    private $tablesCreated = [];

    /**
     * The datamapper for grouping entity
     *
     * @var EntityGroupingDataMapper
     */
    private $entityGroupingDataMapper = null;

    /**
     * The loader for entity
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * Setup each test
     */
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $serviceManager = $this->account->getServiceManager();

        $this->db = $serviceManager->get(RelationalDbFactory::class);

        $this->entityLoader = $this->account->getServiceManager()->get("EntityLoader");
    }

    /**
     * Cleanup any test entities
     */
    protected function tearDown()
    {
        $entityLoader = $this->account->getServiceManager()->get("EntityLoader");
        foreach ($this->testEntities as $entity) {
            $entityLoader->delete($entity, true);
        }
    }

    /**
     * Make sure the file exists
     *
     * This is more a test of the test to make sure we set the path right
     */
    public function testExists()
    {

    }

    /**
     * Make sure that we are able to copy the fkey grouping table to object_groupings table
     */
    public function testFkeyGroupingTable()
    {
        // Create a test user
        $this->testUser = $this->entityLoader->create("user");
        $this->testUser->setValue("name", "wftest-" . rand());
        $this->testUser->setValue("email", "test@test.com");
        $this->entityLoader->save(($this->testUser));
        $this->account->setCurrentUser($this->testUser);

        $this->testEntities[] = $this->testUser;
    }
}

<?php
namespace BinTest\Update\Once;

use Netric\Console\BinScript;
use Netric\Entity\EntityInterface;
use PHPUnit\Framework\TestCase;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperFactory;
use Netric\Application\Schema\SchemaDataMapperPgsql;
use Netric\Application\Schema\SchemaProperty;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;

/**
 * Make sure the bin/scripts/update/once/004/001/016.php script works
 *
 * @group integration
 */
class Update004001016Test extends TestCase
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
    private $testGroupingIDs = [];

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
     * Handle to account relational database
     *
     * @var  RelationalDbInterface
     */
    private $db = null;

    /**
     * Setup each test
     */
    protected function setUp(): void
{
        $this->account = \NetricTest\Bootstrap::getAccount();
        $serviceManager = $this->account->getServiceManager();

        $this->entityGroupingDataMapper = $serviceManager->get(EntityGroupingDataMapperFactory::class);
        $this->db = $serviceManager->get(RelationalDbFactory::class);

        $this->entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/once/004/001/016.php";
    }

    /**
     * Cleanup any test entities
     */
    protected function tearDown(): void
{
        // Clean up the test entities
        foreach ($this->testEntities as $entity) {
            $this->entityLoader->delete($entity, true);
        }

        // Clean up test object groupings
        $groupings = $this->entityGroupingDataMapper->getGroupings(ObjectTypes::CONTACT, 'groups');
        foreach ($this->testGroupingIDs as $groupingId) {
            $groupings->delete($groupingId);
            $this->entityGroupingDataMapper->saveGroupings($groupings);
        }
    }

    /**
     * Make sure the file exists
     *
     * This is more a test of the test to make sure we set the path right
     */
    public function testExists()
    {
        $this->assertTrue(file_exists($this->scriptPath), $this->scriptPath . " not found!");
    }


    /**
     * Make sure that we are able to copy the fkey_multi grouping table to object_groupings table
     */
    public function testFkeyMultiGroupingTable()
    {
        // Create a test grouping
        $newGroupingName = "Unit Test Group" . rand();
        $groupingsGroups = $this->entityGroupingDataMapper->getGroupings(ObjectTypes::CONTACT, "groups");
        $groupsGrp = $groupingsGroups->getByName($newGroupingName);
        if (!$groupsGrp)
            $groupsGrp = $groupingsGroups->create($newGroupingName);
        $groupingsGroups->add($groupsGrp);
        $this->entityGroupingDataMapper->saveGroupings($groupingsGroups);
        $this->testGroupingIDs[] = $groupsGrp->id;

        // Create a new entity
        $customer = $this->entityLoader->create(ObjectTypes::CONTACT);
        $customer->setValue('name', 'test update 4.001.016');
        $contactId = $this->entityLoader->save($customer, $this->user);

        /* 
         * Manually update the entity groups field to avoid the entity datamapper 
         * from inserting into the object_grouping_mem table
         */
        $definitionLoader = $this->account->getServiceManager()->get(EntityDefinitionLoaderFactory::class);
        $customerDefinition = $definitionLoader->get(ObjectTypes::CONTACT);
        $data = ['groups' => '[ ' . $groupsGrp->id . ']'];
        $this->db->update(
            $customerDefinition->object_table,
            ['groups' => "[" . $groupsGrp->id . "]"],
            ['id' => $contactId]
        );

        // Run the update script
        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // Make sure that the script inserted the grouping into object_grouping_mem
        $result = $this->db->query(
            "SELECT * FROM object_grouping_mem WHERE grouping_id=:grouping_id",
            ['grouping_id' => $groupsGrp->id]
        );
        $this->assertGreaterThan(0, $result->rowCount());
    }
}

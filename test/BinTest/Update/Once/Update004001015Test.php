<?php
namespace BinTest\Update\Once;

use Netric\Console\BinScript;
use Netric\Entity\EntityInterface;
use PHPUnit\Framework\TestCase;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperFactory;
use Netric\Application\Schema\SchemaRdbDataMapper;
use Netric\Application\Schema\SchemaProperty;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;

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
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/once/004/001/015.php";
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

        // Cleanup the test groupings in object_groupings table
        foreach ($this->testObjectGroupings as $table => $groupIds) {
            foreach ($groupIds as $groupId) {
                $this->deleteGroupInObjectGroupings($table, $groupId);
            }
        }

        foreach ($this->tablesCreated as $table) {
            $this->db->query("DROP TABLE $table");
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
     * Make sure that we are able to copy the fkey grouping table to object_groupings table
     */
    public function testFkeyGroupingTable()
    {
        /*
         * If the fkey grouping table is not existing, then we need to create it
         * So we can add a test data and be able to continue the testing
         */
        $tableName = "customer_stages";
        if ($this->db->tableExists($tableName) === false) {
            $schemaDefinition = array(
                "$tableName" => array(
                    "PROPERTIES" => array(
                        'id' => array('type' => SchemaProperty::TYPE_BIGSERIAL),
                        'name' => array('type' => SchemaProperty::TYPE_CHAR_128),
                        'sort_order' => array('type' => SchemaProperty::TYPE_SMALLINT, 'default' => '0'),
                        'color' => array('type' => SchemaProperty::TYPE_CHAR_6),
                        'commit_id' => array('type' => SchemaProperty::TYPE_BIGINT),
                    ),
                    'PRIMARY_KEY' => 'id',
                    "INDEXES" => array()
                )
            );

            $this->createTable($tableName, $schemaDefinition);
        }

        $fieldName = "stage_id";
        $objType = ObjectTypes::CONTACT;

        // Add a test group data to the old fkey group table
        $groupName = "Customer $fieldName Group Unit Test";
        $oldGroupingId = $this->db->insert($tableName, ["name" => $groupName]);
        $this->testObjectGroupings[$tableName][] = $oldGroupingId;

        // Test the group that was in properly saved
        $this->assertGreaterThan(0, $oldGroupingId);

        // Create new entity
        $entity = $this->entityLoader->create($objType);
        $entity->setValue("name", "test");
        $entityId = $this->entityLoader->save($entity);
        $this->testEntities[] = $entity;

        // Manually set the id of the entity since the datamapper will refresh the reference on save
        $entity->setValue("stage_id", $oldGroupingId);
        $targetTable = $entity->getDefinition()->object_table;
        $sql = "UPDATE $targetTable SET field_data = :field_data WHERE guid = :guid";
        $this->db->query($sql, [
            "field_data" => json_encode($entity->toArray()),
            "guid" => $entity->getValue('guid')]);

        // Run the update script that will copy the group data from customer_stages to object_groupings
        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // The update script should have updated the customer entity with the new id of the customer stages group
        $entityLoaded = $this->entityLoader->get($objType, $entity->getId());
        $entityGroup = $entityLoaded->getValue($fieldName);
        $entityGroupName = $entityLoaded->getValueName($fieldName);

        // Get new object groupings (which should have a new entry for the added group)
        $groupings = $this->entityGroupingDataMapper->getGroupings($objType, $fieldName);
        $group = $groupings->getByName($groupName);
        $this->assertEquals($entityGroup, $group->id);
        $this->assertEquals($entityGroupName, $group->name);

        $this->testObjectGroupings["object_groupings"][] = $group->id;
    }

    /**
     * Make sure that we are able to copy the fkey_multi grouping table to object_groupings table
     */
    public function testFkeyMultiGroupingTable()
    {
        /*
         * If the fkey_multi grouping table is not existing, then we need to create it
         * So we can add a test data and be able to continue the testing
         */
        $tableName = "customer_labels";
        if ($this->db->tableExists($tableName) === false) {
            $schemaDefinition = array(
                "$tableName" => array(
                    "PROPERTIES" => array(
                        'id' => array('type' => SchemaProperty::TYPE_BIGSERIAL),
                        'name' => array('type' => SchemaProperty::TYPE_CHAR_64),
                        'parent_id' => array('type' => SchemaProperty::TYPE_INT),
                        'f_special' => array('type' => SchemaProperty::TYPE_BOOL, "default" => "false"),
                        'color' => array('type' => SchemaProperty::TYPE_CHAR_6),
                        'commit_id' => array('type' => SchemaProperty::TYPE_BIGINT),
                    ),
                    'PRIMARY_KEY' => 'id',
                    "INDEXES" => array(
                        array('properties' => array("parent_id")),
                    )
                )
            );

            $this->createTable($tableName, $schemaDefinition);
        }

        $fieldName = "groups";
        $objType = ObjectTypes::CONTACT;

        // Add a test group
        $groupName1 = "$objType Group Test Unit Test 1";
        $groupName2 = "$objType Group Test Unit Test 2";

        // Add a test group data to the old fkey group table
        $groupId1 = $this->db->insert($tableName, ["name" => $groupName1]);
        $groupId2 = $this->db->insert($tableName, ["name" => $groupName2]);
        $this->testObjectGroupings[$tableName][] = $groupId1;
        $this->testObjectGroupings[$tableName][] = $groupId2;

        // Validate that the insert worked
        $this->assertGreaterThan(0, $groupId1);
        $this->assertGreaterThan(0, $groupId2);

        // Create new entity and set the new group that was created
        $entity = $this->entityLoader->create($objType);
        $entity->addMultiValue($fieldName, "{$groupId1}", $groupName1);
        $entity->addMultiValue($fieldName, "{$groupId2}", $groupName2);
        $this->entityLoader->save($entity);
        $this->testEntities[] = $entity;

        // Run the update script that will copy the data from fkey tables to object_groupings
        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // The update script should have updated the entity with the new id of the ic groups
        $entityLoaded = $this->entityLoader->get($objType, $entity->getId());
        $entityGroups = $entityLoaded->getValue($fieldName);
        $entityGroupNames = $entityLoaded->getValueNames($fieldName);

        // Get the object groupings
        $groupings = $this->entityGroupingDataMapper->getGroupings($objType, $fieldName);
        $group1 = $groupings->getByName($groupName1);
        $group2 = $groupings->getByName($groupName2);

        $this->assertEquals($entityGroups[0], $groupId1);
        $this->assertEquals($entityGroups[1], $groupId2);
        $this->assertEquals($entityGroupNames[$groupId1], $groupName1);
        $this->assertEquals($entityGroupNames[$groupId2], $groupName2);

        $this->testObjectGroupings["object_groupings"][] = $group1->id;
        $this->testObjectGroupings["object_groupings"][] = $group2->id;
    }

    /**
     * Make sure that we are able to copy the fkey_multi grouping table (private entity definition) to object_groupings table
     */
    public function testFkeyMultiGroupingTablePrivate()
    {
        /*
         * If the fkey_multi grouping table is not existing, then we need to create it
         * So we can add a test data and be able to continue the testing
         */
        $tableName = "contacts_personal_labels";
        if ($this->db->tableExists($tableName) === false) {
            $schemaDefinition = array(
                "$tableName" => array(
                    "PROPERTIES" => array(
                        'id' => array('type' => SchemaProperty::TYPE_BIGSERIAL),
                        'user_id' => array('type' => SchemaProperty::TYPE_INT),
                        'name' => array('type' => SchemaProperty::TYPE_CHAR_64),
                        'color' => array('type' => SchemaProperty::TYPE_CHAR_6),
                        'parent_id' => array('type' => SchemaProperty::TYPE_INT),
                    ),
                    'PRIMARY_KEY' => 'id',
                    "INDEXES" => array(
                        array('properties' => array("user_id")),
                        array('properties' => array("parent_id")),
                    )
                )
            );

            $this->createTable($tableName, $schemaDefinition);
        }

        // Create a user entity so it will be used to filters groupings since we are dealing with private entity definition
        $userEntity = $this->entityLoader->create("user");
        $userEntity->setValue("name", "Unit Test User" . rand());
        $this->entityLoader->save($userEntity);
        $this->testEntities[] = $userEntity;

        $fieldName = "groups";
        $objType = ObjectTypes::CONTACT_PERSONAL;
        $filters = ["user_id" => $userEntity->getId()];

        // Add a test group
        $groupName1 = "$objType Group Test Unit Test 1";
        $groupName2 = "$objType Group Test Unit Test 2";

        // Add a test group data to the old fkey group table
        $groupId1 = $this->db->insert($tableName, ["name" => $groupName1, "user_id" => $userEntity->getId()]);
        $groupId2 = $this->db->insert($tableName, ["name" => $groupName2, "user_id" => $userEntity->getId()]);
        $this->testObjectGroupings[$tableName][] = $groupId1;
        $this->testObjectGroupings[$tableName][] = $groupId2;

        // Test the group that was in properly saved
        $this->assertGreaterThan(0, $groupId1);
        $this->assertGreaterThan(0, $groupId2);

        // Create new entity and set the new group that was created
        $entity = $this->entityLoader->create($objType);
        $entity->addMultiValue($fieldName, "{$groupId1}", $groupName1);
        $entity->addMultiValue($fieldName, "{$groupId2}", $groupName2);
        $this->entityLoader->save($entity);
        $this->testEntities[] = $entity;

        // Run the update script that will copy the data from fkey tables to object_groupings
        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // The update script should have updated the entity with the new id of the ic groups
        $entityLoaded = $this->entityLoader->get($objType, $entity->getId());
        $entityGroups = $entityLoaded->getValue($fieldName);
        $entityGroupNames = $entityLoaded->getValueNames($fieldName);

        // Get the object groupings
        $groupings = $this->entityGroupingDataMapper->getGroupings($objType, $fieldName, $filters);
        $group1 = $groupings->getByName($groupName1);
        $group2 = $groupings->getByName($groupName2);

        $this->assertEquals($entityGroups[0], $groupId1);
        $this->assertEquals($entityGroups[1], $groupId2);
        $this->assertEquals($entityGroupNames[$groupId1], $groupName1);
        $this->assertEquals($entityGroupNames[$groupId2], $groupName2);

        $this->testObjectGroupings["object_groupings"][] = $group1->id;
        $this->testObjectGroupings["object_groupings"][] = $group2->id;
    }

    /**
     * Function that will hard delete the test group in object_groupings table
     * 
     * @param {string} $table The table where we will be deleting the groupId
     * @param {int} $groupId The group id that will be deleted
     */
    private function deleteGroupInObjectGroupings($table, $groupId)
    {
        $this->db->query("DELETE FROM $table WHERE id=:id", ['id' => $groupId]);
    }

    /**
     * Function that will create a new table in the database
     *
     * @param {string} $tableName The name of the table that will be created
     * @param {array} $schemaDefinition The schema definition that will be used to create the new table
     */
    private function createTable($tableName, $schemaDefinition)
    {
        $serviceManager = $this->account->getServiceManager();
        $schemaDM = new SchemaRdbDataMapper($this->db, $schemaDefinition);
        $schemaDM->update($this->account->getId());

        $this->tablesCreated[] = $tableName;
    }
}

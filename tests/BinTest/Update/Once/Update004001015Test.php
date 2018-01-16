<?php
namespace BinTest\Update\Once;

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
     * Groups to clean up
     *
     * @var groupId[]
     */
    private $testGroups = [];

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

        $this->entityGroupingDataMapper = $serviceManager->get(EntityGroupingDataMapperFactory::class);
        $this->db = $serviceManager->get(RelationalDbFactory::class);

        $this->entityLoader = $this->account->getServiceManager()->get("EntityLoader");
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/once/004/001/015.php";
    }

    /**
     * Cleanup any test entities
     */
    protected function tearDown()
    {
        // Clean up the test entities
        foreach ($this->testEntities as $entity) {
            $this->entityLoader->delete($entity, true);
        }

        // Cleanup the test groupings
        foreach ($this->testGroups as $objType => $fieldNames) {
            foreach ($fieldNames as $fieldName => $groups) {
                foreach ($groups as $groupDetails) {
                    $groupings = $this->entityGroupingDataMapper->getGroupings($objType, $fieldName, $groupDetails["filters"]);
                    $groupings->delete($groupDetails["groupId"]);
                    $this->entityGroupingDataMapper->saveGroupings($groupings);
                }
            }
        }

        // Cleanup the test groupings in object_groupings table
        foreach ($this->testObjectGroupings as $groupId) {
            $this->deleteGroupInObjectGroupings($groupId);
        }

        // Cleanup the tables created for this unit test
        $serviceManager = $this->account->getServiceManager();
        $schemaDb = $serviceManager->get(DbFactory::class);
        foreach ($this->tablesCreated as $table) {
            $schemaDb->query("DROP TABLE $table");
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
        $objType = "customer";

        // Get the groupings for customer stages
        $groupings = $this->entityGroupingDataMapper->getGroupings($objType, $fieldName);

        // Add a test group
        $groupName = "Customer $fieldName Group Unit Test";
        $group = $this->addGroup($groupings, $groupName);

        // Set the newly created test group here so it will be deleted after running the unit test
        $this->testGroups[$objType][$fieldName][] = ["groupId" => $group->id, "filters" => []];

        // Test the group that was in properly saved
        $this->assertGreaterThan(0, $group->id);
        $this->assertEquals($group->name, $groupName);

        // Create new entity and set the new group that was created
        $entity = $this->entityLoader->create($objType);
        $entity->setValue("name", "test");
        $entity->setValue($fieldName, $group->id, $group->name);
        $this->entityLoader->save($entity);
        $this->testEntities[] = $entity;

        // Run the update script that will copy the data from fkey tables to object_groupings
        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // The update script should have updated the customer entity with the new id of the customer stages group
        $entityLoaded = $this->entityLoader->get($objType, $entity->getId());
        $entityGroup = $entityLoaded->getValue($fieldName);
        $entityGroupName = $entityLoaded->getValueName($fieldName);

        // Test the entity that the referenced fieldName is being updated with the new group id that was saved in object_groupings
        $groupInObjectGroupings = $this->getGroupInObjectGroupingsTable($objType, $fieldName, $groupName);
        $this->assertEquals($entityGroup, $groupInObjectGroupings->id);
        $this->assertEquals($entityGroupName, $groupName);

        $this->testObjectGroupings[] = $groupInObjectGroupings->id;
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
        $objType = "customer";

        // Get the groupings based on the $objType set
        $groupings = $this->entityGroupingDataMapper->getGroupings($objType, $fieldName);

        // Add a test group
        $groupName1 = "$objType Group Test Unit Test 1";
        $groupName2 = "$objType Group Test Unit Test 2";
        $group1 = $this->addGroup($groupings, $groupName1);
        $group2 = $this->addGroup($groupings, $groupName2);

        // Set the newly created test groups here so it will be deleted after running the unit test
        $this->testGroups[$objType][$fieldName][] = ["groupId" => $group1->id, "filters" => []];
        $this->testGroups[$objType][$fieldName][] = ["groupId" => $group2->id, "filters" => []];

        // Test the group that was in properly saved
        $this->assertGreaterThan(0, $group1->id);
        $this->assertGreaterThan(0, $group2->id);
        $this->assertEquals($group1->name, $groupName1);
        $this->assertEquals($group2->name, $groupName2);

        // Create new entity and set the new group that was created
        $entity = $this->entityLoader->create($objType);
        $entity->addMultiValue($fieldName, "{$group1->id}", $group1->name);
        $entity->addMultiValue($fieldName, "{$group2->id}", $group2->name);
        $this->entityLoader->save($entity);
        $this->testEntities[] = $entity;

        // Run the update script that will copy the data from fkey tables to object_groupings
        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // The update script should have updated the entity with the new id of the ic groups
        $entityLoaded = $this->entityLoader->get($objType, $entity->getId());
        $entityGroups = $entityLoaded->getValue($fieldName);
        $entityGroupNames = $entityLoaded->getValueNames($fieldName);

        // Test the entity that the referenced fieldName is being updated with the new group id that was saved in object_groupings
        $groupInObjectGroupings1 = $this->getGroupInObjectGroupingsTable($objType, $fieldName, $groupName1);
        $groupInObjectGroupings2 = $this->getGroupInObjectGroupingsTable($objType, $fieldName, $groupName2);
        $this->assertEquals($entityGroupNames[$groupInObjectGroupings1->id], $groupName1);
        $this->assertEquals($entityGroupNames[$groupInObjectGroupings2->id], $groupName2);

        $this->testObjectGroupings[] = $groupInObjectGroupings1->id;
        $this->testObjectGroupings[] = $groupInObjectGroupings2->id;
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

        // Create a user entity so it will be used to filters groupings since we are dealing with private entity definition
        $userEntity = $this->entityLoader->create("user");
        $userEntity->setValue("name", "Unit Test User");
        $this->entityLoader->save($userEntity);
        $this->testEntities[] = $userEntity;

        $fieldName = "groups";
        $objType = "contact_personal";
        $filters = ["user_id" => $userEntity->getId()];

        // Get the groupings based on the $objType set
        $groupings = $this->entityGroupingDataMapper->getGroupings($objType, $fieldName, $filters);

        // Add a test group
        $groupName1 = "$objType Group Test Unit Test 1";
        $groupName2 = "$objType Group Test Unit Test 2";
        $group1 = $this->addGroup($groupings, $groupName1);
        $group2 = $this->addGroup($groupings, $groupName2);

        // Set the newly created test groups here so it will be deleted after running the unit test
        $this->testGroups[$objType][$fieldName][] = ["groupId" => $group1->id, "filters" => $filters];
        $this->testGroups[$objType][$fieldName][] = ["groupId" => $group2->id, "filters" => $filters];

        // Test the group that was in properly saved
        $this->assertGreaterThan(0, $group1->id);
        $this->assertGreaterThan(0, $group2->id);
        $this->assertEquals($group1->name, $groupName1);
        $this->assertEquals($group2->name, $groupName2);

        // Create new entity and set the new group that was created
        $entity = $this->entityLoader->create($objType);
        $entity->addMultiValue($fieldName, "{$group1->id}", $group1->name);
        $entity->addMultiValue($fieldName, "{$group2->id}", $group2->name);
        $this->entityLoader->save($entity);
        $this->testEntities[] = $entity;

        // Run the update script that will copy the data from fkey tables to object_groupings
        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // The update script should have updated the entity with the new id of the ic groups
        $entityLoaded = $this->entityLoader->get($objType, $entity->getId());
        $entityGroups = $entityLoaded->getValue($fieldName);
        $entityGroupNames = $entityLoaded->getValueNames($fieldName);

        // Test the entity that the referenced fieldName is being updated with the new group id that was saved in object_groupings
        $groupInObjectGroupings1 = $this->getGroupInObjectGroupingsTable($objType, $fieldName, $groupName1);
        $groupInObjectGroupings2 = $this->getGroupInObjectGroupingsTable($objType, $fieldName, $groupName2);
        $this->assertEquals($entityGroupNames[$groupInObjectGroupings1->id], $groupName1);
        $this->assertEquals($entityGroupNames[$groupInObjectGroupings2->id], $groupName2);

        $this->testObjectGroupings[] = $groupInObjectGroupings1->id;
        $this->testObjectGroupings[] = $groupInObjectGroupings2->id;
    }

    /**
     * Function that will query the object_groupings table
     *
     * @param {string} $objType The object type of the grouping. This will be used to get the entity definition
     * @param {string} $fieldName The fieldName that was used by the grouping
     * @param {string} $groupName The name of the group that will be used to query the object_groupings
     * @return {Group} $group Return the result of the query as a Group class
     */
    private function getGroupInObjectGroupingsTable($objType, $fieldName, $groupName)
    {
        $serviceManager = $this->account->getServiceManager();
        $def = $serviceManager->get(EntityDefinitionLoaderFactory::class)->get($objType);
        $field = $def->getField($fieldName);

        // Create where conditions to check if group data already in object_groupings table
        $whereConditionValues = array(
            "name" => $groupName,
            "object_type_id" => $def->id,
            "field_id" => $field->id
        );

        $whereConditions = array(
            "name = :name",
            "object_type_id = :object_type_id",
            "field_id = :field_id"
        );

        $sql = "SELECT * from object_groupings WHERE " . implode(" and ", $whereConditions);
        $result = $this->db->query($sql, $whereConditionValues);
        $row = $result->fetch();

        // Create a group to return
        $group = new Group();
        $group->id = $row[$field->fkeyTable['key']];
        $group->name = $row[$field->fkeyTable['title']];

        return $group;
    }

    /**
     * Function that will hard delete the test group in object_groupings table
     * @param {int} $groupId The group id that will be deleted
     */
    private function deleteGroupInObjectGroupings($groupId)
    {
        $serviceManager = $this->account->getServiceManager();
        $this->db->query('DELETE FROM object_groupings WHERE id=:id', ['id' => $groupId]);
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
        $schemaDb = $serviceManager->get(DbFactory::class);
        $schemaDM = new SchemaDataMapperPgsql($schemaDb, $schemaDefinition);

        $schemaDM->update($this->account->getId());

        $this->tablesCreated[] = $tableName;
    }

    /**
     * Function that will add a group in the groupings
     *
     * @param {EntityGroupings} $groupings The groupings where we will add a new group
     * @param {string} $groupName The name that will be used when adding a new group
     * @param {string} $userId The user id that will be set to the new group
     *
     * @return Group Returns the newly created group
     */
    private function addGroup(EntityGroupings $groupings, $groupName, $userId = null)
    {

        // We need to check first if $groupName already exist, if so, then we will use it as our test group
        $group = $groupings->getByName($groupName);
        if ($group === false) {
            $group = new Group();
            $group->name = $groupName;

            if ($userId) {
                $group->setValue("user_id", $userId);
            }

            $groupings->add($group);
            $groupings->setDataMapper($this->entityGroupingDataMapper);
            $groupings->save();
        }

        return $group;
    }
}

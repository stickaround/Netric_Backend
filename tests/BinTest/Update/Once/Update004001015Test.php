<?php
namespace BinTest\Update\Once;

use Netric\EntityQuery;
use Netric\EntityGroupings\Group;
use Netric\EntityGroupings\EntityGroupings;
use Netric\Console\BinScript;
use Netric\Entity\EntityInterface;
use PHPUnit\Framework\TestCase;

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
        $this->entityGroupingDataMapper = $this->account->getServiceManager()->get('Netric/EntityGroupings/DataMapper/EntityGroupingDataMapper');
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
     * Make sure that we are able to copy the activity_types to object_groupings table
     */
    public function testActivityTypes()
    {
        $serviceManager = $this->account->getServiceManager();
        $entityIndex = $serviceManager->get("Netric/EntityQuery/Index/Index");

        // Create new notes to trigger activity type for notes
        $note = $this->entityLoader->create("note");
        $note->setValue("name", "test");
        $this->entityLoader->save($note);
        $this->testEntities[] = $note;

        // Run the update script that will copy the data from fkey tables to object_groupings
        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // Test the group that was in object_groupings
        $groupInObjectGroupings = $this->getGroupInObjectGroupingsTable("activity", "type_id", "Note");
        $this->assertEquals($groupInObjectGroupings->name, "Note");

        // Get the activity that was created when we saved the note entity
        $query = new EntityQuery("activity");
        $query->andWhere("obj_reference")->equals("note:" . $note->getId());
        $result = $entityIndex->executeQuery($query);
        $activity = $result->getEntity(0);

        $this->assertEquals($activity->getValue("type_id"), $groupInObjectGroupings->id);
    }

    /**
     * Make sure that we are able to copy the entity groups to object_groupings table
     */
    public function testEntityGroups()
    {
        // Create a user entity so it will be used to filters groupings since we are dealing with private entity definition
        $userEntity = $this->entityLoader->create("user");
        $userEntity->setValue("name", "Unit Test User");
        $this->entityLoader->save($userEntity);
        $this->testEntities[] = $userEntity;
        
        $objTypes = array (
            "contact_personal" => array ("fieldName" => "groups", "filters" => ["user_id" => $userEntity->getId()]),
            "note" => array ("fieldName" => "groups", "filters" => ["user_id" => $userEntity->getId()]),
            "infocenter_document" => array ("fieldName" => "groups"),
            "user" => array ("fieldName" => "groups"),
            "customer" => array ("fieldName" => "groups"),
            "content_feed" => array ("fieldName" => "groups"),
            "project" => array ("fieldName" => "groups"),
            "product" => array ("fieldName" => "categories"),
            "content_feed_post" => array ("fieldName" => "categories")            
        );

        foreach ($objTypes as $objType => $details) {

            $fieldName = $details['fieldName'];
            $filters = ($details['filters']) ? $details['filters'] : [];
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
    }

    /**
     * Make sure that we are able to copy the customer stages and status to object_groupings table
     */
    public function testCustomerStagesAndStatus()
    {
        $objType = "customer";
        $fieldNames = ["stage_id", "status_id"];

        foreach ($fieldNames as $fieldName) {

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
    }

    /**
     * Make sure that we are able to copy the Lead's grouping fkey tables to object_groupings table
     */
    public function testLeadGroupings()
    {
        $objType = "lead";
        $fieldNames = ["class_id", "queue_id", "rating_id", "source_id", "status_id"];

        foreach ($fieldNames as $fieldName) {

            // Get the groupings for lead based on the $fieldName
            $groupings = $this->entityGroupingDataMapper->getGroupings($objType, $fieldName);

            // Add a test group
            $groupName = "Lead $fieldName Group Unit Test";
            $group = $this->addGroup($groupings, $groupName);

            // Set the newly created test group here so it will be deleted after running the unit test
            $this->testGroups[$objType][$fieldName][] = ["groupId" => $group->id, "filters" => []];

            // Test the group that was in properly saved
            $this->assertGreaterThan(0, $group->id);
            $this->assertEquals($group->name, $groupName);

            // Create new entity and set the new group that was created
            $entity = $this->entityLoader->create($objType);
            $entity->setValue("title", "test");
            $entity->setValue($fieldName, $group->id, $group->name);
            $this->entityLoader->save($entity);
            $this->testEntities[] = $entity;

            // Run the update script that will copy the data from fkey tables to object_groupings
            $binScript = new BinScript($this->account->getApplication(), $this->account);
            $this->assertTrue($binScript->run($this->scriptPath));

            // The update script should have updated the lead entity with the new id of the lead groupings
            $entityLoaded = $this->entityLoader->get($objType, $entity->getId());
            $entityGroup = $entityLoaded->getValue($fieldName);
            $entityGroupName = $entityLoaded->getValueName($fieldName);

            // Test the entity that the referenced fieldName is being updated with the new group id that was saved in object_groupings
            $groupInObjectGroupings = $this->getGroupInObjectGroupingsTable($objType, $fieldName, $groupName);
            $this->assertEquals($entityGroup, $groupInObjectGroupings->id);
            $this->assertEquals($entityGroupName, $groupName);

            $this->testObjectGroupings[] = $groupInObjectGroupings->id;
        }
    }

    /**
     * Make sure that we are able to copy the Lead's grouping fkey tables to object_groupings table
     */
    public function testOpportunityGroupings()
    {
        $objType = "opportunity";
        $fieldNames = ["objection_id", "stage_id", "type_id", "lead_source_id"];

        foreach ($fieldNames as $fieldName) {

            // Get the groupings for opportunity based on the $fieldName
            $groupings = $this->entityGroupingDataMapper->getGroupings($objType, $fieldName);

            // Add a test group
            $groupName = "Opportunity $fieldName Group Unit Test";
            $group = $this->addGroup($groupings, $groupName);

            // Set the newly created test group here so it will be deleted after running the unit test
            $this->testGroups[$objType][$fieldName][] = ["groupId" => $group->id, "filters" => []];

            // Test the group that was in properly saved
            $this->assertGreaterThan(0, $group->id);
            $this->assertEquals($group->name, $groupName);

            // Create new entity and set the new group that was created
            $entity = $this->entityLoader->create($objType);
            $entity->setValue("title", "test");
            $entity->setValue($fieldName, $group->id, $group->name);
            $this->entityLoader->save($entity);
            $this->testEntities[] = $entity;

            // Run the update script that will copy the data from fkey tables to object_groupings
            $binScript = new BinScript($this->account->getApplication(), $this->account);
            $this->assertTrue($binScript->run($this->scriptPath));

            // The update script should have updated the opportunity entity with the new id of the opportunity groupings
            $entityLoaded = $this->entityLoader->get($objType, $entity->getId());
            $entityGroup = $entityLoaded->getValue($fieldName);
            $entityGroupName = $entityLoaded->getValueName($fieldName);

            // Test the entity that the referenced fieldName is being updated with the new group id that was saved in object_groupings
            $groupInObjectGroupings = $this->getGroupInObjectGroupingsTable($objType, $fieldName, $groupName);
            $this->assertEquals($entityGroup, $groupInObjectGroupings->id);
            $this->assertEquals($entityGroupName, $groupName);

            $this->testObjectGroupings[] = $groupInObjectGroupings->id;
        }
    }

    /**
     * Make sure that we are able to copy the Invoice status_id to object_groupings table
     */
    public function testInvoiceStatusGrouping()
    {
        $objType = "invoice";
        $fieldName = "status_id";

        // Get the groupings for invoice based on the $fieldName
        $groupings = $this->entityGroupingDataMapper->getGroupings($objType, $fieldName);

        // Add a test group
        $groupName = "Invoice $fieldName Group Unit Test";
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

        // The update script should have updated the invoice entity with the new id of the status_id groupings
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
     * Make sure that we are able to copy the Case's grouping fkey tables to object_groupings table
     */
    public function testCaseGroupings()
    {
        $objType = "case";
        $fieldNames = ["severity_id", "status_id", "type_id"];

        foreach ($fieldNames as $fieldName) {

            // Get the groupings for opportunity based on the $fieldName
            $groupings = $this->entityGroupingDataMapper->getGroupings($objType, $fieldName);

            // Add a test group
            $groupName = "Case $fieldName Group Unit Test";
            $group = $this->addGroup($groupings, $groupName);

            // Set the newly created test group here so it will be deleted after running the unit test
            $this->testGroups[$objType][$fieldName][] = ["groupId" => $group->id, "filters" => []];

            // Test the group that was in properly saved
            $this->assertGreaterThan(0, $group->id);
            $this->assertEquals($group->name, $groupName);

            // Create new entity and set the new group that was created
            $entity = $this->entityLoader->create($objType);
            $entity->setValue("title", "test");
            $entity->setValue($fieldName, $group->id, $group->name);
            $this->entityLoader->save($entity);
            $this->testEntities[] = $entity;

            // Run the update script that will copy the data from fkey tables to object_groupings
            $binScript = new BinScript($this->account->getApplication(), $this->account);
            $this->assertTrue($binScript->run($this->scriptPath));

            // The update script should have updated the case entity with the new id of the case groupings
            $entityLoaded = $this->entityLoader->get($objType, $entity->getId());
            $entityGroup = $entityLoaded->getValue($fieldName);
            $entityGroupName = $entityLoaded->getValueName($fieldName);

            // Test the entity that the referenced fieldName is being updated with the new group id that was saved in object_groupings
            $groupInObjectGroupings = $this->getGroupInObjectGroupingsTable($objType, $fieldName, $groupName);
            $this->assertEquals($entityGroup, $groupInObjectGroupings->id);
            $this->assertEquals($entityGroupName, $groupName);

            $this->testObjectGroupings[] = $groupInObjectGroupings->id;
        }
    }

    /**
     * Make sure that we are able to copy the Priorities grouping fkey tables to object_groupings table
     */
    public function testProjectPrioritiesGrouping()
    {
        $objTypes = ["project", "task"];
        $fieldName = "priority";

        foreach ($objTypes as $objType) {

            // Get the groupings for project/task priorities
            $groupings = $this->entityGroupingDataMapper->getGroupings($objType, $fieldName);

            // Add a test group
            $groupName = "$objType Priority Group Unit Test";
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

            // The update script should have updated the project/task entity with the new id of the case groupings
            $entityLoaded = $this->entityLoader->get($objType, $entity->getId());
            $entityGroup = $entityLoaded->getValue($fieldName);
            $entityGroupName = $entityLoaded->getValueName($fieldName);

            // Test the entity that the referenced fieldName is being updated with the new group id that was saved in object_groupings
            $groupInObjectGroupings = $this->getGroupInObjectGroupingsTable($objType, $fieldName, $groupName);
            $this->assertEquals($entityGroup, $groupInObjectGroupings->id);
            $this->assertEquals($entityGroupName, $groupName);

            $this->testObjectGroupings[] = $groupInObjectGroupings->id;
        }
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
        $db = $serviceManager->get("Netric/Db/Relational/RelationalDb");
        $def = $serviceManager->get("Netric/EntityDefinition/EntityDefinitionLoader")->get($objType);
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
        $result = $db->query($sql, $whereConditionValues);
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
        $db = $serviceManager->get("Netric/Db/Relational/RelationalDb");
        $db->query('DELETE FROM object_groupings WHERE id=:id', ['id' => $groupId]);
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

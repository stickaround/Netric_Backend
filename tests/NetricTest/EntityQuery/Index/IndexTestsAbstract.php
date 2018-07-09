<?php

/**
 * Define common tests that will need to be run with all data mappers.
 *
 * In order to implement the unit tests, a datamapper test case just needs
 * to extend this class and create a getDataMapper class that returns the
 * datamapper to be tested
 */
namespace NetricTest\EntityQuery\Index;

use Netric;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityQuery;
use Netric\EntityQuery\Where;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Entity\ObjType\UserEntity;
use PHPUnit\Framework\TestCase;
use Netric\Entity\ObjType\UserEntity;

/**
 * @group integration
 */
abstract class IndexTestsAbstract extends TestCase
{
    /**
     * Tenant account
     *
     * @var \Netric\Account\Account
     */
    protected $account = null;

    /**
     * Test entities to delete
     *
     * @var EntityInterface[]
     */
    private $testEntities = array();

    /**
     * Test groupings to delete
     *
     * @var array(array('obj_type', 'field', 'grouping_id'))
     */
    private $testGroupings = array();

    /**
     * Setup each test
     */
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->user = $this->account->getUser(UserEntity::USER_SYSTEM);
    }

    /**
     * Cleanup
     */
    protected function tearDown()
    {
        $entityLoader = $this->account->getServiceManager()->get("EntityLoader");
        foreach ($this->testEntities as $entity) {
            $entityLoader->delete($entity, true);
        }

        // Cleanup Groupings
        foreach ($this->testGroupings as $groupData) {
            $this->deleteGrouping($groupData['obj_type'], $groupData['field'], $groupData['id']);
        }
    }

    /**
     * Required by all derrieved classes
     *
     * @return \Netric\EnittyQuery\Index\IndexInterface The setup index to query
     */
    abstract protected function getIndex();

    /**
     * Create a test customer
     */
    protected function createTestCustomer($typeId = 2)
    {
        $uniName = "utestequals." . uniqid();

        // Status id
        $statusG = $this->createGrouping("customer", "status_id", "Unit Test Status" . uniqid());

        // Groups
        $groupsG = $this->createGrouping("customer", "groups", "Unit Test Group" . uniqid());

        // Save a test object
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $customer = $loader->create("customer");
        $customer->setValue("name", $uniName);
        $customer->setValue("f_nocall", true);
        $customer->setValue("type_id", $typeId);
        $customer->setValue("last_contacted", time());
        $customer->setValue("status_id", $statusG['id'], $statusG['name']);
        $customer->addMultiValue("groups", $groupsG['id'], $groupsG['name']);
        $loader->save($customer);

        $this->testEntities[] = $customer;

        return $customer;
    }

    /**
     * Create an object grouping entry for testing
     *
     * @param string $objType
     * @param string $field
     * @param string $name
     * @return array("id", "name")
     */
    protected function createGrouping($objType, $field, $name, $parent = null)
    {
        $dm = $this->account->getServiceManager()->get('Netric\EntityGroupings\DataMapper\EntityGroupingDataMapper');
        $groupings = $dm->getGroupings($objType, $field);
        $group = $groupings->create($name);
        if ($parent) {
            $group->parentId = $parent;
        }
        $groupings->add($group);
        $dm->saveGroupings($groupings);
        $group = $groupings->getByName($name, $parent);

        // Add to queue to cleanup on tearDown
        $this->testGroupings[] = array("obj_type" => $objType, "field" => $field, "id" => $group->id);

        return $group->toArray();
    }

    /**
     * Delete an object grouping
     *
     * @param seting $objType
     * @param string $field
     * @param int $id
     */
    protected function deleteGrouping($objType, $field, $id)
    {
        $dm = $this->account->getServiceManager()->get('Netric\EntityGroupings\DataMapper\EntityGroupingDataMapper');
        $groupings = $dm->getGroupings($objType, $field);
        $groupings->delete($id);
        $dm->saveGroupings($groupings);
    }

    /**
     * Run tests with combination of "and" and "or" conditions
     */
    public function testEntityQueryIndexSanitizeConditionValue()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        $serviceManager = $this->account->getServiceManager();
        $index = $serviceManager->get(IndexFactory::class);
        $entityDefinitionLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);

        $taskDef = $entityDefinitionLoader->get("task");
        $userIdField = $taskDef->getField("user_id");

        $sanitizedValue = $index->sanitizeWhereCondition($userIdField, UserEntity::USER_CURRENT);
        $this->assertEquals($sanitizedValue, $this->user->getId());
    }

    /**
     * Run tests with combination of "and" and "or" conditions
     */
    public function testIndexQueryRdbSave()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        $serviceManager = $this->account->getServiceManager();
        $index = $serviceManager->get(IndexFactory::class);

        // Create customer test object
        $customer = $this->createTestCustomer();

        $res = $index->save($customer);
        $this->assertTrue($res);
    }

    /**
     * Run tests with entity query index using object fields
     */
    public function testIndexQueryUsingObjectField()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        $serviceManager = $this->account->getServiceManager();
        $index = $serviceManager->get(IndexFactory::class);
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $entityDefinitionLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);

        // Create customer test object. This will also create an activity log
        $customer = $this->createTestCustomer();

        $activityDef = $entityDefinitionLoader->get("activity");
        $verbObjectField = $activityDef->getField("verb_object");

        // Test first if verb_object field has no subtype
        $this->assertEmpty($verbObjectField->subtype);

        /*
         * Now, let's query the activity using a field with no subtype
         */
        $query = new EntityQuery("activity");
        $query->where('verb_object')->equals($customer->getObjRef());
        $res = $index->executeQuery($query);

        // This should return 0 result since verb_object was not set
        $this->assertEmpty(0, $res->getTotalNum());


        /**
         * Query the activity entity using object reference
         */
        $query = new EntityQuery("activity");
        $query->where('verb')->equals("create");
        $query->where('obj_reference')->equals($customer->getObjRef());
        $res = $index->executeQuery($query);

        // This should return 1 result since creating an entity will always create an 1 activity log
        $this->assertEquals(1, $res->getTotalNum());

        // Get the activity entity
        $activityEntity = $res->getEntity(0);

        /*
         * We will update the activity's verb_object, then we will try to query it
         */
        $customer->setValue("verb_object", $customer->getObjRef());
        $entityLoader->save($customer);

        $query = new EntityQuery("activity");
        $query->where('verb_object')->equals($customer->getObjRef());
        $res = $index->executeQuery($query);

        // This should return 1 result since verb_object we have now set the verb_object
        $this->assertEmpty(0, $res->getTotalNum());
    }

    /**
     * Run tests with combination of "and" and "or" conditions
     */
    public function testCombinationOfWhereConditions()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        $personTypeId = 1;
        $organizationTypeId = 2;

        // Create customer test objects
        $customer1 = $this->createTestCustomer($personTypeId);
        $customer2 = $this->createTestCustomer($personTypeId);
        $customer3 = $this->createTestCustomer($organizationTypeId);
        $customer4 = $this->createTestCustomer($organizationTypeId);
        
        $testObjType = $customer1->getObjType();
        $serviceManager = $this->account->getServiceManager();
        $index = $serviceManager->get(IndexFactory::class);

        /*
         * Test multiple or conditions and 1 "and" operator
         */
        $query = new EntityQuery($testObjType);
        $query->where('name')->equals($customer1->getValue("name"));
        $query->orWhere('name')->equals($customer2->getValue("name"));
        $query->orWhere('name')->equals($customer3->getValue("name"));
        $query->orWhere('name')->equals($customer4->getValue("name"));
        $query->andWhere('type_id')->equals($organizationTypeId);
        $res = $index->executeQuery($query);

        // Should get at least 2 results since we only have set the type_id = 2
        $this->assertGreaterThanOrEqual(2, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($obj->getValue("type_id"), $organizationTypeId);

        /*
         * Test multiple "and" conditions that can get a specific customer
         */
        $query = new EntityQuery($testObjType);
        $query->where('name')->equals($customer1->getValue("name"));
        $query->andWhere('status_id')->equals($customer1->getValue("status_id"));
        $query->andWhere('last_contacted')->equals($customer1->getValue("last_contacted"));
        $res = $index->executeQuery($query);

        // Should get only 1 result since this query is for specific for $customer1
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($obj->getValue("id"), $customer1->getValue("id"));

        /*
         * Test multiple "and" conditions and 1 or statement
         */
        $query = new EntityQuery($testObjType);
        $query->orWhere('type_id')->equals($personTypeId);
        $query->andwhere('name')->equals($customer4->getValue("name"));
        $query->andWhere('status_id')->equals($customer4->getValue("status_id"));
        $query->andWhere('last_contacted')->equals($customer4->getValue("last_contacted"));
        $res = $index->executeQuery($query);

        // Should get at least 3 results since the "and" conditions are specific for $customer4 and customer 1,2 has type_id $personType
        $this->assertGreaterThanOrEqual(3, $res->getTotalNum());

        /*
         * Test multiple "or" conditions in the same text field
         */
        $query = new EntityQuery($testObjType);
        $query->where('name')->equals($customer1->getValue("name"));
        $query->orWhere('name')->equals($customer2->getValue("name"));
        $query->orWhere('name')->equals($customer3->getValue("name"));
        $res = $index->executeQuery($query);

        // Should get 3 result3 since we set 3 "or" conditions for name field
        $this->assertEquals(3, $res->getTotalNum());

        /*
         * Test multiple "or" conditions in the same multi field
         */
        $query = new EntityQuery($testObjType);
        $query->where('groups')->equals($customer1->getValue("groups")[0]);
        $query->orWhere('groups')->equals($customer2->getValue("groups")[0]);
        $query->orWhere('groups')->equals($customer3->getValue("groups")[0]);
        $res = $index->executeQuery($query);

        // Should get 3 result3 since we set 3 "or" conditions for groups field
        $this->assertEquals(3, $res->getTotalNum());

        /*
         * Test multiple "or" conditions in different fields
         */
        $query = new EntityQuery($testObjType);
        $query->where('status_id')->equals($customer1->getValue("status_id"));
        $query->orwhere('name')->equals($customer2->getValue("name"));
        $query->orWhere('id')->equals($customer3->getValue("id"));
        $query->orWhere('groups')->equals($customer4->getValue("groups")[0]);
        $res = $index->executeQuery($query);

        // Should get 4 results since we set 4 "or" conditions for different fields
        $this->assertEquals(4, $res->getTotalNum());

        /*
         * Test multiple "and" conditions in the same field
         */
        $query = new EntityQuery($testObjType);
        $query->where('status_id')->equals($customer1->getValue("status_id"));
        $query->andWhere('status_id')->equals($customer2->getValue("status_id"));
        $query->andWhere('status_id')->equals($customer3->getValue("status_id"));
        $res = $index->executeQuery($query);

        // Should get 0 result since no customer can have 3 different status_id
        $this->assertEquals(0, $res->getTotalNum());
    }

    /**
     * Make sure "OR" and "AND" query conditions will work
     */
    public function testObjectMultiEqualsCondition()
    {
        $serviceManager = $this->account->getServiceManager();
        $loader = $serviceManager->get(EntityLoaderFactory::class);
        $index = $serviceManager->get(IndexFactory::class);

        $memberId = rand();
        $memberId1 = rand();
        $memberId2 = rand();
        $memberId3 = rand();

        // Create a project that only has 1 member
        $projectEntity = $loader->create("project");
        $projectEntity->setValue("name", "Test Project 4");
        $projectEntity->addMultiValue("members", $memberId, "Member");
        $pid = $loader->save($projectEntity);

        // Create a project that has 1 member
        $projectEntity1 = $loader->create("project");
        $projectEntity1->setValue("name", "Test Project 1");
        $projectEntity1->addMultiValue("members", $memberId1, "Member One");
        $pid1 = $loader->save($projectEntity1);

        // Create a project that has 2 members
        $projectEntity2 = $loader->create("project");
        $projectEntity2->setValue("name", "Test Project 2");
        $projectEntity2->addMultiValue("members", $memberId1, "Member One");
        $projectEntity2->addMultiValue("members", $memberId2, "Member Two");
        $pid2 = $loader->save($projectEntity2);

        // Create a project that only has 3 members
        $projectEntity3 = $loader->create("project");
        $projectEntity3->setValue("name", "Test Project 3");
        $projectEntity3->addMultiValue("members", $memberId1, "Member One");
        $projectEntity1->addMultiValue("members", $memberId2, "Member Two");
        $projectEntity1->addMultiValue("members", $memberId3, "Member Three");
        $pid3 = $loader->save($projectEntity3);

        // Set the entities so it will be cleaned up properly
        $this->testEntities[] = $projectEntity;
        $this->testEntities[] = $projectEntity1;
        $this->testEntities[] = $projectEntity2;
        $this->testEntities[] = $projectEntity3;

        /*
         * Query the project of a specific member with 1 project
         */
        $query = new EntityQuery("project");
        $query->where("members")->equals($memberId);

        // Execute the query
        $res = $index->executeQuery($query);

        $resultEntity = $res->getEntity(0);
        $this->assertEquals($pid, $resultEntity->getId());
        $this->assertEquals($projectEntity->getName(), $resultEntity->getName());
        $this->assertEquals(1, $res->getTotalNum());

        /*
         * Query the project of a specific member with multiple projects
         */
        $query = new EntityQuery("project");
        $query->where("members")->equals($memberId1);
        $res = $index->executeQuery($query);

        // This will have a result of 3 since $member1 has 3 projects
        $this->assertEquals(3, $res->getTotalNum());

        /*
         * Query the projects of two different members
         */
        $query = new EntityQuery("project");
        $query->where("members")->equals($memberId1);
        $query->orWhere("members")->equals($memberId);
        $res = $index->executeQuery($query);

        // This will have a result of 4 projects since both $member1 has 3 projects while $memberId has 1
        $this->assertEquals(4, $res->getTotalNum());

        /*
         * Query the projects of two different members that only has 1 projects each
         */
        $query = new EntityQuery("project");
        $query->where("members")->equals($memberId);
        $query->orWhere("members")->equals($memberId3);
        $res = $index->executeQuery($query);

        // This will have a result of two since both $member and $member3 has one project each
        $this->assertEquals(2, $res->getTotalNum());

        /*
         * Query the projects that has the same members
         */
        $query = new EntityQuery("project");
        $query->where("members")->equals($memberId1);
        $query->andWhere("members")->equals($memberId2);
        $res = $index->executeQuery($query);

        // This will have a result of 1 project since both $member and $member3 has one project each
        $resultEntity = $res->getEntity(0);
        $this->assertEquals(1, $res->getTotalNum());
        $this->assertEquals($pid2, $resultEntity->getId());

        /*
         * Query the projects that has the same members and will include other project using "or" condition
         */
        $query = new EntityQuery("project");
        $query->where("members")->equals($memberId1);
        $query->andWhere("members")->equals($memberId2);
        $query->orWhere("members")->equals($memberId);
        $res = $index->executeQuery($query);

        $this->assertEquals(2, $res->getTotalNum());

        /*
         * Create a query that will use members and name field
         */
        $query = new EntityQuery("project");
        $query->where("members")->equals($memberId1);
        $query->andWhere("name")->equals("Test Project");

        // This will have 0 results since $member1 is not a member in Test Project
        $this->assertEquals(0, $res->getTotalNum());
    }

    /**
     * Run test of is equal conditions
     */
    public function testWhereFullText()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }
        //$this->assertTrue(false, "Index could not be setup!");

        // Save a test object
        $testEnt = $this->createTestCustomer();

        // Query value
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('*')->fullText($testEnt->getValue("name"));
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getId(), $obj->getId());
    }

    /**
     * Run test of is equal conditions
     */
    public function testWhereEqualsText()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }
        //$this->assertTrue(false, "Index could not be setup!");

        // Save a test object
        $testEnt = $this->createTestCustomer();

        // Query value
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('name')->equals($testEnt->getValue("name"));
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getId(), $obj->getId());

        // Query null - first name is not set
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('first_name')->equals(null);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    /**
     * Run test of is equal conditions
     */
    public function testWhereEqualsNumber()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }
        //$this->assertTrue(false, "Index could not be setup!");

        $uniName = "utestequals." . uniqid();

        // Save a test object
        $testEnt = $this->createTestCustomer();

        // Test with number
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('type_id')->equals(2);
        $res = $index->executeQuery($query);
        $this->assertTrue($res->getTotalNum() >= 1);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        // Test null
        // -------------------------------------------------
        $testEnt->setValue("type_id", null);
        $this->account->getServiceManager()->get("Entity_DataMapper")->save($testEnt);
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('type_id')->equals(null);
        $res = $index->executeQuery($query);
        $this->assertTrue($res->getTotalNum() >= 1);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    /**
     * Run test of is equal conditions
     *
     * @group testWhereEqualsFkey
     */
    public function testWhereEqualsFkey()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }
        //$this->assertTrue(false, "Index could not be setup!");

        $uniName = "utestequals." . uniqid();

        // Save a test object
        $testEnt = $this->createTestCustomer();

        // Test value is set
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('status_id')->equals($testEnt->getValue("status_id"));
        $res = $index->executeQuery($query);
        $this->assertTrue($res->getTotalNum() >= 1);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        // Test null
        // -------------------------------------------------
        $cachedStatus = $testEnt->getValue("status_id");
        $testEnt->setValue("status_id", null);
        $this->account->getServiceManager()->get("Entity_DataMapper")->save($testEnt);
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('status_id')->equals(null);
        $res = $index->executeQuery($query);
        $this->assertTrue($res->getTotalNum() >= 1);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        // Make sure query with old id does not return entity
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('status_id')->equals($cachedStatus);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
    }

    /**
     * Run test of is equal conditions
     */
    public function testWhereEqualsFkeyMulti()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }
        //$this->assertTrue(false, "Index could not be setup!");

        $uniName = "utestequals." . uniqid();

        // Save a test object
        $testEnt = $this->createTestCustomer();

        // Query collection for fkey_multi
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $groups = $testEnt->getValue("groups");
        $query->where('groups')->equals($groups[0]);
        $res = $index->executeQuery($query);
        $this->assertTrue($res->getTotalNum() >= 1);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        $cachedGroups = $testEnt->getValue("groups");
        $testEnt->setValue("groups", null);
        $this->account->getServiceManager()->get("Entity_DataMapper")->save($testEnt);

        // Test null for groups
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $groups = $testEnt->getValue("groups");
        $query->where('groups')->equals(null);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        // Make sure object no longer returns on null query with old id
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $groups = $testEnt->getValue("groups");
        $query->where('groups')->equals($cachedGroups[0]);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
    }

    /**
     * Run test of is equal conditions
     */
    public function testWhereEqualsBool()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }
        //$this->assertTrue(false, "Index could not be setup!");

        // Save a test object
        $testEnt = $this->createTestCustomer();

        // Query collection for boolean
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('f_nocall')->equals(true);
        $res = $index->executeQuery($query);
        $this->assertTrue($res->getTotalNum() >= 1);
        // Look for the entity above
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
            }
        }
        $this->assertTrue($found);
    }

    /**
     * Check if we can query an object when a subtype is set
     */
    public function testWhereEqualsObject()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }
        //$this->assertTrue(false, "Index could not be setup!");

        $dm = $this->account->getServiceManager()->get("Entity_DataMapper");

        // Create a test customer
        $testEnt = $this->createTestCustomer();

        // Create a test case attached to the customer
        $case = $this->account->getServiceManager()->get("EntityLoader")->create("case");
        $case->setValue("name", "Unit Test Case");
        $case->setValue("customer_id", $testEnt->getId(), $testEnt->getName());
        $cid = $dm->save($case);

        // Make sure this gets cleaned up
        $this->testEntities[] = $case;

        // Query for customer id
        $query = new EntityQuery($case->getObjType());
        $query->where('customer_id')->equals($testEnt->getId());
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());

        // Query with null customer id
        $case->setValue("customer_id", "");
        $dm->save($case);
        $query = new EntityQuery($case->getObjType());
        $query->where('id')->equals($case->getId());
        $query->where('customer_id')->equals("");
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
    }

    /**
     * Check if we can query an object multi when a subtype is set
     */
    public function testWhereEqualsObjectMulti()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }
        //$this->assertTrue(false, "Index could not be setup!");

        $dm = $this->account->getServiceManager()->get("Entity_DataMapper");

        // Create a test project with a member
        $project = $this->account->getServiceManager()->get("EntityLoader")->create("project");
        $project->setValue("name", "Unit Test Project");
        $project->setValue("members", $this->user->getId(), $this->user->getName());
        $pid = $dm->save($project);

        // Make sure this gets cleaned up
        $this->testEntities[] = $project;

        // Query for project members
        $query = new EntityQuery($project->getObjType());
        $query->where('members')->equals($this->user->getId());
        $res = $index->executeQuery($query);
        $this->assertEquals($project->getId(), $res->getEntity(0)->getId());
    }

    /**
     * Try to query an object reference where there is no subtype for the field
     */
    public function testWhereEqualsObjectReference()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        $entityLoader = $this->account->getServiceManager()->get("EntityLoader");

        // Create a test customer
        $testEnt = $this->createTestCustomer();

        // Create a notification for this customer
        $objReference = Netric\Entity\Entity::encodeObjRef($testEnt->getDefinition()->getObjType(), $testEnt->getId());
        $notification = $entityLoader->create("notification");
        $notification->setValue("name", "Unit Test Notification");
        $notification->setValue("obj_reference", $objReference);
        $entityLoader->save($notification);

        // Make sure this gets cleaned up
        $this->testEntities[] = $notification;

        // Query for this notification
        $query = new EntityQuery($notification->getDefinition()->getObjType());
        $query->where('obj_reference')->equals($objReference);
        $res = $index->executeQuery($query);
        $this->assertGreaterThan(0, $res->getTotalNum());

        // Now set the object reference to null for testing empty
        $notification->setValue("obj_reference", "");
        $entityLoader->save($notification);

        // Query the null condition
        $query = new EntityQuery($notification->getDefinition()->getObjType());
        $query->where('id')->equals($notification->getId());
        $query->where('obj_reference')->equals("");
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
    }

    /**
     * Not euquals text
     */
    public function testWhereNotEqualsText()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        // Save a test object
        $testEnt = $this->createTestCustomer();

        // Query value
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('name')->doesNotEqual($testEnt->getValue("name"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);

        // Does not equal null
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('name')->doesNotEqual(null);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    /**
     * Not euquals text
     */
    public function testWhereNotEqualsNumber()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        // Save a test object
        $testEnt = $this->createTestCustomer();

        // Query value
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('type_id')->doesNotEqual(2);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);

        // Does not equal null
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('type_id')->doesNotEqual(null);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    /**
     * Run test of is equal conditions
     */
    public function testWhereNotEqualsFkey()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        // Save a test object
        $testEnt = $this->createTestCustomer();

        // Test value is set
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('status_id')->doesNotEqual($testEnt->getValue("status_id"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);

        // Test null
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('status_id')->doesNotEqual(null);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    /**
     * Run test of is equal conditions
     */
    public function testWhereNotEqualsFkeyMulti()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        // Save a test object
        $testEnt = $this->createTestCustomer();

        // Test value is set
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $groups = $testEnt->getValue("groups");
        $query->where('groups')->doesNotEqual($groups[0]);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);

        // Test null
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('groups')->doesNotEqual(null);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    /**
     * Test numbers for is greater
     */
    public function testIsLessNumber()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }
        //$this->assertTrue(false, "Index could not be setup!");

        // Save a test object
        $testEnt = $this->createTestCustomer();

        // Is greater inclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('type_id')->isLessThan(3);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        // Is greater exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('type_id')->isLessThan(2);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);

        // Is greater or equal inclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('type_id')->isLessOrEqualTo(2);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        // Is greater or equal exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('type_id')->isLessOrEqualTo(1);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
    }

    /**
     * Test numbers for is greater
     */
    public function testIsLessDateTime()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }
        //$this->assertTrue(false, "Index could not be setup!");

        // Save a test object
        $testEnt = $this->createTestCustomer();

        // Is greater inclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('last_contacted')->isLessThan(strtotime("+1 day"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        // Is greater exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('last_contacted')->isLessThan(strtotime("-1 day"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);

        // Is greater or equal inclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('last_contacted')->isLessOrEqualTo($testEnt->getValue("last_contacted"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        // Is greater or equal exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('last_contacted')->isLessOrEqualTo(strtotime("-1 day"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
    }

    /**
     * Test numbers for is greater
     */
    public function testIsGreaterNumber()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }
        //$this->assertTrue(false, "Index could not be setup!");

        $uniName = "utestequals." . uniqid();

        // Save a test object
        $testEnt = $this->createTestCustomer();

        // Is greater inclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('type_id')->isGreaterThan(1);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        // Is greater exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('type_id')->isGreaterThan(2);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);

        // Is greater or equal inclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('type_id')->isGreaterOrEqualTo(2);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        // Is greater or equal exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('type_id')->isGreaterOrEqualTo(3);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
    }

    /**
     * Test numbers for is greater
     */
    public function testIsGreaterDateTime()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }
        //$this->assertTrue(false, "Index could not be setup!");

        $uniName = "utestequals." . uniqid();

        // Save a test object
        $testEnt = $this->createTestCustomer();

        // Is greater inclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('last_contacted')->isGreaterThan(strtotime("-1 day"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        // Is greater exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('last_contacted')->isGreaterThan(strtotime("+1 day"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);

        // Is greater or equal inclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('last_contacted')->isGreaterOrEqualTo($testEnt->getValue("last_contacted"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        // Is greater or equal exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('last_contacted')->isGreaterOrEqualTo(strtotime("+1 day"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
    }

    /**
     * Check begins with
     */
    public function testBeginsWith()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        // Save a test object
        $testEnt = $this->createTestCustomer();

        // Query null - first name is not set
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('name')->beginsWith(substr($testEnt->getValue("name"), 0, 10));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    /**
     * Check begins with
     */
    public function testContains()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        // Save a test object
        $testEnt = $this->createTestCustomer();

        // Query null - first name is not set
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('name')->contains(substr($testEnt->getValue("name"), 4, 6));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    /**
     * Test date contains
     */
    public function testDateIsEqual()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        // Save a test object
        $testEnt = $this->createTestCustomer();

        // Day is equal
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('last_contacted')->dayIsEqual(date("j"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        // Month is equal
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('last_contacted')->monthIsEqual(date("n"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        // Year is equal
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('last_contacted')->yearIsEqual(date("Y"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    public function testWithinLastXNum()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        // Save a test object
        $testEnt = $this->createTestCustomer();

        // Day - inclusive
        // -------------------------------------------------
        $testEnt->setValue("last_contacted", strtotime("-2 days"));
        $this->account->getServiceManager()->get("Entity_DataMapper")->save($testEnt);

        $query = new EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->lastNumDays(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getId(), $obj->getId());

        // Day - exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->lastNumDays(1);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());

        // Week - inclusive
        // -------------------------------------------------
        $testEnt->setValue("last_contacted", strtotime("-2 weeks"));
        $this->account->getServiceManager()->get("Entity_DataMapper")->save($testEnt);

        $query = new EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->lastNumWeeks(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getId(), $obj->getId());

        // Week - exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->lastNumWeeks(1);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());

        // Month - inclusive
        // -------------------------------------------------
        $testEnt->setValue("last_contacted", strtotime("-2 months"));
        $this->account->getServiceManager()->get("Entity_DataMapper")->save($testEnt);

        $query = new EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->lastNumMonths(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getId(), $obj->getId());

        // Month - exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->lastNumMonths(1);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());

        // Year - inclusive
        // -------------------------------------------------
        $testEnt->setValue("last_contacted", strtotime("-2 years"));
        $this->account->getServiceManager()->get("Entity_DataMapper")->save($testEnt);

        $query = new EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->lastNumYears(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getId(), $obj->getId());

        // Year - exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->lastNumYears(1);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());
    }

    public function testWithinNextXNum()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        // Save a test object
        $testEnt = $this->createTestCustomer();

        // Day - inclusive
        // -------------------------------------------------
        $testEnt->setValue("last_contacted", strtotime("+2 days"));
        $this->account->getServiceManager()->get("Entity_DataMapper")->save($testEnt);

        $query = new EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->nextNumDays(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getId(), $obj->getId());

        // Day - exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->nextNumDays(1);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());

        // Week - inclusive
        // -------------------------------------------------
        $testEnt->setValue("last_contacted", strtotime("+2 weeks"));
        $this->account->getServiceManager()->get("Entity_DataMapper")->save($testEnt);

        $query = new EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->nextNumWeeks(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getId(), $obj->getId());

        // Week - exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->nextNumWeeks(1);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());

        // Month - inclusive
        // -------------------------------------------------
        $testEnt->setValue("last_contacted", strtotime("+2 months"));
        $this->account->getServiceManager()->get("Entity_DataMapper")->save($testEnt);

        $query = new EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->nextNumMonths(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getId(), $obj->getId());

        // Month - exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->nextNumMonths(1);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());

        // Year - inclusive
        // -------------------------------------------------
        $testEnt->setValue("last_contacted", strtotime("+2 years"));
        $this->account->getServiceManager()->get("Entity_DataMapper")->save($testEnt);

        $query = new EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->nextNumYears(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getId(), $obj->getId());

        // Year - exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->nextNumYears(1);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());
    }


    /**
     * Test query string patter explosion
     */
    public function testSearchStrExpl()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        // Single email address
        $qstr = "sky.stebnicki@aereus.com";
        $terms = $index->queryStringToTerms($qstr);
        $this->assertEquals($terms[0], "sky.stebnicki@aereus.com");

        // terms and phrases
        $qstr = "sky.stebnicki@aereus.com \"in quotes\" single";
        $terms = $index->queryStringToTerms($qstr);
        $this->assertEquals($terms[0], "sky.stebnicki@aereus.com");
        $this->assertEquals($terms[1], "\"in quotes\"");
        $this->assertEquals($terms[2], "single");
    }

    public function testSearchDeleted()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        // Save a test object
        $dm = $this->account->getServiceManager()->get("Entity_DataMapper");
        $obj = $this->account->getServiceManager()->get("EntityLoader")->create("project_story");
        $obj->setValue("name", "testSearchDeleted");
        $oid = $dm->save($obj);
        $dm->delete($obj);

        // First test regular query without f_deleted flag set
        $query = new EntityQuery("project_story");
        $query->where('id')->equals($oid);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());

        // Test deleted flag set should return with deleted customer
        $query = new EntityQuery("project_story");
        $query->where('id')->equals($oid);
        $query->where('f_deleted')->equals(true);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $ent = $res->getEntity(0);
        $this->assertEquals($oid, $ent->getId());

        // Cleanup
        $dm->delete($obj, true);
    }

    /**
     * Test getting heiarchy for groups for each index - may have custom version
     */
    public function testGetHeiarchyDownGrp()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        $g1 = $this->createGrouping("customer", "groups", "HeiarchyDownGrp1");
        $g2 = $this->createGrouping("customer", "groups", "HeiarchyDownGrp2", $g1['id']);

        $def = $this->account->getServiceManager()->get("EntityDefinitionLoader")->get("customer");
        $field = $def->getField("groups");

        $children = $index->getHeiarchyDownGrp($field, $g1["id"]);
        $this->assertTrue(count($children) > 0);
        $found1 = false;
        $found2 = false;
        foreach ($children as $gid) {
            if ($gid == $g1['id']) {
                $found1 = true;
            }
            if ($gid == $g2['id']) {
                $found2 = true;
            }
        }
        $this->assertTrue($found1);
        $this->assertTrue($found2);

        // Cleanup
        $this->deleteGrouping("customer", "groups", $g1['id']);
        $this->deleteGrouping("customer", "groups", $g2['id']);
    }

    /**
     * Test getting heiarchy for objects
     */
    public function testGetHeiarchyDownObj()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }
        $loader = $this->account->getServiceManager()->get("EntityLoader");
        $dm = $this->account->getServiceManager()->get("Entity_DataMapper");

        $folder1 = $loader->create("folder");
        $folder1->setValue("name", "My Test Folder");
        $dm->save($folder1);
        $this->assertNotNull($folder1->getId());

        $folder2 = $loader->create("folder");
        $folder2->setValue("name", "My Test SubFolder");
        $folder2->setValue("parent_id", $folder1->getId());
        $dm->save($folder2);
        $this->assertNotNull($folder2->getId());

        $children = $index->getHeiarchyDownObj("folder", $folder1->getId());
        $this->assertTrue(count($children) > 0);
        $found1 = false;
        $found2 = false;
        foreach ($children as $gid) {
            if ($gid == $folder1->getId()) {
                $found1 = true;
            }
            if ($gid == $folder2->getId()) {
                $found2 = true;
            }
        }
        $this->assertTrue($found1);
        $this->assertTrue($found2);

        // Cleanup
        $dm->delete($folder2, true);
        $dm->delete($folder1, true);
    }

    /**
     * Test getting heiarchy for objects
     */
    public function testGetHeiarchyUpObj()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }
        $loader = $this->account->getServiceManager()->get("EntityLoader");
        $dm = $this->account->getServiceManager()->get("Entity_DataMapper");

        $folder1 = $loader->create("folder");
        $folder1->setValue("name", "My Test Folder");
        $dm->save($folder1);
        $this->assertNotNull($folder1->getId());

        $folder2 = $loader->create("folder");
        $folder2->setValue("name", "My Test SubFolder");
        $folder2->setValue("parent_id", $folder1->getId());
        $dm->save($folder2);
        $this->assertNotNull($folder2->getId());

        $children = $index->getHeiarchyUpObj("folder", $folder2->getId());
        $this->assertTrue(count($children) > 0);
        $found1 = false;
        $found2 = false;
        foreach ($children as $gid) {
            if ($gid == $folder1->getId()) {
                $found1 = true;
            }
            if ($gid == $folder2->getId()) {
                $found2 = true;
            }
        }
        $this->assertTrue($found1);
        $this->assertTrue($found2);

        // Cleanup
        $dm->delete($folder2, true);
        $dm->delete($folder1, true);
    }

    /**
     * Make sure that the query will load and run plugins
     */
    public function testPlugin()
    {
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        $testPlugin = new TestAssets\TestIndexPlugin();

        $property = new \ReflectionProperty("\\Netric\\EntityQuery\\Index\\IndexAbstract", "pluginsLoaded");
        $property->setAccessible(true);
        $property->setValue($index, ["customer" => $testPlugin]);

        // Query value
        $query = new EntityQuery("customer");
        $query->where('*')->fullText("test");
        $index->executeQuery($query);

        // Make sure the plugin was called
        $this->assertTrue($testPlugin->beforeRan);
        $this->assertTrue($testPlugin->afterRan);
    }

    /**
     * Some indexes will construct entities from the results
     * which could make them all come across as dirty unless the
     * index specifically calls resetIsDirty on the entity.
     */
    public function testEntitiesNotDirty()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        // Save a test object
        $testEnt = $this->createTestCustomer();

        // Query collection for boolean
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('f_nocall')->equals(true);
        $res = $index->executeQuery($query);
        $this->assertTrue($res->getTotalNum() >= 1);
        // Look for the entity above
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            $this->assertFalse($ent->isDirty());
        }
    }

    /**
     * Make sure that we are able to query the object reference entity
     */
    public function testQueryObjectReference()
    {
        $dm = $this->account->getServiceManager()->get("Entity_DataMapper");

        // Create an entity and initialize values
        $customerName = "Test Customer";
        $customer = $this->account->getServiceManager()->get("EntityLoader")->create("customer");
        $customer->setValue("name", $customerName);
        $customer->setValue("owner_id", $this->user->getId());
        $cid = $dm->save($customer, $this->user);

        $customerEntity = $this->account->getServiceManager()->get("EntityFactory")->create("customer");
        $dm->getById($customerEntity, $cid);
        $this->assertEquals($customerEntity->getName(), $customerName);

        // Create reminder and set the customer as our object reference
        $customerReminder = "Customer Reminder";
        $reminder = $this->account->getServiceManager()->get("EntityLoader")->create("reminder");
        $reminder->setValue("name", $customerReminder);
        $reminder->setValue("obj_reference", "customer:$cid:$customerName");
        $rid = $dm->save($reminder, $this->user);

        // Set the entities so it will be cleaned up properly
        $this->testEntities[] = $customer;
        $this->testEntities[] = $reminder;

        $reminderEntity = $this->account->getServiceManager()->get("EntityFactory")->create("reminder");
        $dm->getById($reminderEntity, $rid);
        $this->assertEquals($reminderEntity->getName(), $customerReminder);
        $this->assertEquals($reminderEntity->getValue("obj_reference"), "customer:$cid:$customerName");
        $this->assertEquals($reminderEntity->getValueName("obj_reference"), $customerName);

        // Now query the customer's reminder using the obj reference used
        $query = new Netric\EntityQuery("reminder");
        $query->where("obj_reference")->equals("customer:$cid:$customerName");
        $query->where("id")->equals($rid);

        $index = $this->account->getServiceManager()->get("EntityQuery_Index");
        // Execute the query
        $res = $index->executeQuery($query);

        $this->assertEquals(1, $res->getTotalNum());

        // This should be the test reminder we created that was associated with the customer
        $resultEntity = $res->getEntity(0);
        $this->assertEquals($rid, $resultEntity->getId());
        $this->assertEquals("Customer Reminder", $resultEntity->getName());
    }

    /**
     * Make sure "OR" and "AND" query conditions will work
     * @group testBooleanOperatorsWithConditions
     */
    public function testBooleanOperatorsWithConditions()
    {
        $dm = $this->account->getServiceManager()->get("Entity_DataMapper");

        // Create an entity and initialize values
        $customerName1 = "Test Customer 1";
        $customer1 = $this->account->getServiceManager()->get("EntityLoader")->create("customer");
        $customer1->setValue("name", $customerName1);
        $customer1->setValue("owner_id", $this->user->getId());
        $customer1->setValue("type_id", "1");
        $customer1->setValue("city", "new_city");
        $cid1 = $dm->save($customer1, $this->user);

        $customerName2 = "Test Customer 2";
        $customer2 = $this->account->getServiceManager()->get("EntityLoader")->create("customer");
        $customer2->setValue("name", $customerName2);
        $customer2->setValue("owner_id", $this->user->getId());
        $customer2->setValue("type_id", "1");
        $customer2->setValue("city", "old_city");
        $cid2 = $dm->save($customer2, $this->user);

        $customerName3 = "Test Customer 3";
        $customer3 = $this->account->getServiceManager()->get("EntityLoader")->create("customer");
        $customer3->setValue("name", $customerName3);
        $customer3->setValue("owner_id", $this->user->getId());
        $customer3->setValue("type_id", "2");
        $customer3->setValue("city", "new_city");
        $cid3 = $dm->save($customer3, $this->user);

        $customerName4 = "Test Customer 4";
        $customer4 = $this->account->getServiceManager()->get("EntityLoader")->create("customer");
        $customer4->setValue("name", $customerName3);
        $customer4->setValue("owner_id", $this->user->getId());
        $customer4->setValue("type_id", "2");
        $customer4->setValue("city", "old_city");
        $cid4 = $dm->save($customer4, $this->user);

        // Set the entities so it will be cleaned up properly
        $this->testEntities[] = $customer1;
        $this->testEntities[] = $customer2;
        $this->testEntities[] = $customer3;
        $this->testEntities[] = $customer4;

        // Query the customers using and where conditions. This should only query the customer 1
        $query = new Netric\EntityQuery("customer");
        $query->where("type_id")->equals(1);
        $query->where("city")->equals("new_city");

        $index = $this->account->getServiceManager()->get("EntityQuery_Index");
        // Execute the query
        $res = $index->executeQuery($query);

        $this->assertEquals(1, $res->getTotalNum());
        $resultEntity = $res->getEntity(0);
        $this->assertEquals(1, $resultEntity->getValue("type_id"));
        $this->assertEquals("new_city", $resultEntity->getValue("city"));

        // Query the customers using or where conditions. This should query all the customers
        $query = new Netric\EntityQuery("customer");
        $query->orWhere("type_id")->equals(1);
        $query->orWhere("type_id")->equals(2);

        $index = $this->account->getServiceManager()->get("EntityQuery_Index");
        // Execute the query
        $res = $index->executeQuery($query);

        // We should be be able to query all 4 customers
        $this->assertEquals(4, $res->getTotalNum());

        // Query the customers using the combination of or/and where conditions.
        $query = new Netric\EntityQuery("customer");
        $query->where("type_id")->equals(1);
        $query->orWhere("city")->equals("old_city");

        $index = $this->account->getServiceManager()->get("EntityQuery_Index");
        // Execute the query
        $res = $index->executeQuery($query);

        // We should be be able to query all 3 customers
        $this->assertEquals(3, $res->getTotalNum());
    }

    /**
     * Test hierarcy subqueries
     *
     * @group testHierarcySubqueries
     *
    public function testHierarcySubqueries()
    {
    $indexes = array("db");
    if (index_is_available("elastic"))
    $indexes[] = "elastic";

    // Setup files and folders for example
    $antfs = new AntFs($this->dbh, $this->user);
    $fldr = $antfs->openFolder("/tests/testHierarcySubqueries", true);
    $this->assertNotNull($fldr);
    $fldr2 = $antfs->openFolder("/tests/testHierarcySubqueries/Child", true);
    $this->assertNotNull($fldr2);
    $file = $fldr2->openFile("testsync", true);
    $this->assertNotNull($file);

    foreach ($indexes as $indName)
    {
    $fldr->setIndex($indName);
    $fldr->index();
    $fldr2->setIndex($indName);
    $fldr2->index();
    $file->setIndex($indName);
    $file->index();

    // Test equal to root which should return none
    $objList = new CAntObjectList($this->dbh, "file", $this->user);
    $objList->setIndex($indName); // Manually set index type
    $objList->addCondition("and", "folder_id", "is_equal", $fldr->id);
    $objList->getObjects();
    $this->assertEquals(0, $objList->getNumObjects());

    // Now test with is_less_or_equal
    $objList = new CAntObjectList($this->dbh, "file", $this->user);
    $objList->setIndex($indName); // Manually set index type
    $objList->addCondition("and", "folder_id", "is_less_or_equal", $fldr->id);
    $objList->getObjects();
    $this->assertTrue($objList->getNumObjects() > 0);
    }

    // Cleanup
    $file->removeHard();
    $fldr2->removeHard();
    $fldr->removeHard();
    }
     *
     */

    /**
     * Test if using an fkey label works
     *
     * @group testFkeyLabelToId
     *
    public function testFkeyLabelToId()
    {
    $dbh = $this->dbh;

    $obj = new CAntObject($dbh, "activity", null, $this->user);
    $grpdat = $obj->getGroupingEntryByName("type_id", "testFkeyLabelToId");
    if (!$grpdat)
    $grpdat = $obj->addGroupingEntry("type_id", "testFkeyLabelToId");
    $obj->setValue("name", "Test customer testFkeyLabelToId");
    $obj->setValue("type_id", $grpdat["id"]);
    $oid = $obj->save();

    // Query based on type_id label
    $objList = new CAntObjectList($this->dbh, "activity", $this->user);
    $objList->addCondition("and", "type_id", "is_equal", "testFkeyLabelToId");
    $objList->getObjects();
    $this->assertTrue($objList->getNumObjects() > 0);

    // Cleanup
    $obj->deleteGroupingEntry("groups", $grpdat['id']);
    $obj->removeHard();
    }
     *
     */
}

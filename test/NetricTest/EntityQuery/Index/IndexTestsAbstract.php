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
use NetricTest\Bootstrap;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
use Netric\Entity\Entity;
use Netric\EntityQuery\Index\IndexAbstract;
use Netric\EntityQuery\Aggregation\Min;
use Ramsey\Uuid\Uuid;

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
    private $testEntities = [];

    /**
     * Test groupings to delete
     *
     * @var array(array('obj_type', 'field', 'grouping_id'))
     */
    private $testGroupings = [];

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
        $this->user = $this->account->getUser(null, UserEntity::USER_SYSTEM);

        $this->defLoader = $this->account->getServiceManager()->get(EntityDefinitionLoaderFactory::class);
        $this->index = $this->account->getServiceManager()->get(IndexFactory::class);
    }

    /**
     * Cleanup
     */
    protected function tearDown(): void
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        foreach ($this->testEntities as $entity) {
            $entityLoader->delete($entity, $this->account->getAuthenticatedUser());
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
        $statusG = $this->createGrouping(ObjectTypes::CONTACT, "status_id", "Unit Test Status" . uniqid());

        // Groups
        $groupsG = $this->createGrouping(ObjectTypes::CONTACT, "groups", "Unit Test Group" . uniqid());

        // Save a test object
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $customer = $loader->create(ObjectTypes::CONTACT, $this->account->getAccountId());
        $customer->setValue("name", $uniName);
        $customer->setValue("f_nocall", true);
        $customer->setValue("type_id", $typeId);
        $customer->setValue("last_contacted", time());
        $customer->setValue("status_id", $statusG['group_id'], $statusG['name']);
        $customer->addMultiValue("groups", $groupsG['group_id'], $groupsG['name']);
        $loader->save($customer, $this->account->getSystemUser());
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
    protected function createGrouping($objType, $field, $name)
    {
        $groupingLoader = $this->account->getServiceManager()->get(GroupingLoaderFactory::class);
        $groupings = $groupingLoader->get("$objType/$field");

        if ($groupings->getByName($name)) {
            return $groupings->getByName($name)->toArray();
        }

        $group = $groupings->create($name);
        $groupings->add($group);
        $groupingLoader->save($groupings);

        // Add to queue to cleanup on tearDown
        $this->testGroupings[] = [
            "obj_type" => $objType,
            "field" => $field,
            "group_id" => $group->getGroupId()
        ];

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
        $dm = $this->account->getServiceManager()->get(EntityGroupingDataMapperFactory::class);
        $groupings = $dm->getGroupings("$objType/$field");
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

        $currentAccountUser = $this->account->getUser();
        $serviceManager = $this->account->getServiceManager();
        $index = $serviceManager->get(IndexFactory::class);
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $entityDefinitionLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);

        $projectDef = $entityDefinitionLoader->get(ObjectTypes::PROJECT);
        $ownerIdField = $projectDef->getField("owner_id");

        $sanitizedValue = $index->sanitizeWhereCondition($ownerIdField, UserEntity::USER_CURRENT);
        $this->assertEquals($sanitizedValue, $currentAccountUser->getEntityId());

        // Now let's create a project entity and set the value of owner_id to current user's id
        $projectEntity = $entityLoader->create(ObjectTypes::PROJECT, $this->account->getAccountId());
        $projectEntity->setValue("name", "new project test");
        $projectEntity->setValue("owner_id", $currentAccountUser->getEntityId());
        $entityLoader->save($projectEntity, $this->account->getSystemUser());

        $this->testEntities[] = $projectEntity;

        // We will now create a query using UserEntity::USER_CURRENT to get the $projectEntity
        $query = new EntityQuery(ObjectTypes::PROJECT);
        $query->where('owner_id')->equals(UserEntity::USER_CURRENT);
        $res = $index->executeQuery($query);

        // This should return 1 result since we have created 1 project that has current user's id
        $this->assertEquals(1, $res->getTotalNum());
        $this->assertEquals($projectEntity->getEntityId(), $res->getEntity(0)->getEntityId());
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

        $res = $index->save($customer, $this->account->getSystemUser());
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

        $activityDef = $entityDefinitionLoader->get(ObjectTypes::ACTIVITY);
        $verbObjectField = $activityDef->getField("verb_object");

        // Test first if verb_object field has no subtype
        $this->assertEmpty($verbObjectField->subtype);

        /*
         * Now, let's query the activity using a field with no subtype
         */
        $query = new EntityQuery(ObjectTypes::ACTIVITY);
        $query->where('verb_object')->equals($customer->getEntityId());
        $res = $index->executeQuery($query);

        // This should return 0 result since verb_object was not set
        $this->assertEmpty(0, $res->getTotalNum());

        /**
         * Query the activity entity using object reference
         */
        $query = new EntityQuery(ObjectTypes::ACTIVITY);
        $query->where('verb')->equals("create");
        $query->where('obj_reference')->equals($customer->getEntityId());
        $res = $index->executeQuery($query);

        // This should return 1 result since creating an entity will always create an 1 activity log
        $this->assertEquals(1, $res->getTotalNum());

        // Get the activity entity
        $activityEntity = $res->getEntity(0);

        /*
         * We will update the activity's verb_object, then we will try to query it
         */
        $customer->setValue("verb_object", $customer->getEntityId());
        $entityLoader->save($customer, $this->account->getSystemUser());

        $query = new EntityQuery(ObjectTypes::ACTIVITY);
        $query->where('verb_object')->equals($customer->getEntityId());
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
        $this->assertEquals($obj->getEntityId(), $customer1->getEntityId());

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
        $query->orWhere('entity_id')->equals($customer3->getValue("entity_id"));
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

        $member = $loader->create(ObjectTypes::USER, $this->account->getAccountId());
        $loader->save($member, $this->account->getSystemUser());

        $member1 = $loader->create(ObjectTypes::USER, $this->account->getAccountId());
        $loader->save($member1, $this->account->getSystemUser());

        $member2 = $loader->create(ObjectTypes::USER, $this->account->getAccountId());
        $loader->save($member2, $this->account->getSystemUser());

        $member3 = $loader->create(ObjectTypes::USER, $this->account->getAccountId());
        $loader->save($member3, $this->account->getSystemUser());

        $this->testEntities[] = $member;
        $this->testEntities[] = $member1;
        $this->testEntities[] = $member2;
        $this->testEntities[] = $member3;

        $memberId = $member->getEntityId();
        $memberId1 = $member1->getEntityId();
        $memberId2 = $member2->getEntityId();
        $memberId3 = $member3->getEntityId();

        // Create a project that only has 1 member
        $projectEntity = $loader->create(ObjectTypes::PROJECT, $this->account->getAccountId());
        $projectEntity->setValue("name", "Test Project 4");
        $projectEntity->addMultiValue("members", $memberId, "Member");
        $pid = $loader->save($projectEntity, $this->account->getSystemUser());

        // Create a project that has 1 member
        $projectEntity1 = $loader->create(ObjectTypes::PROJECT, $this->account->getAccountId());
        $projectEntity1->setValue("name", "Test Project 1");
        $projectEntity1->addMultiValue("members", $memberId1, "Member One");
        $pid1 = $loader->save($projectEntity1, $this->account->getSystemUser());

        // Create a project that has 2 members
        $projectEntity2 = $loader->create(ObjectTypes::PROJECT, $this->account->getAccountId());
        $projectEntity2->setValue("name", "Test Project 2");
        $projectEntity2->addMultiValue("members", $memberId1, "Member One");
        $projectEntity2->addMultiValue("members", $memberId2, "Member Two");
        $pid2 = $loader->save($projectEntity2, $this->account->getSystemUser());

        // Create a project that only has 3 members
        $projectEntity3 = $loader->create(ObjectTypes::PROJECT, $this->account->getAccountId());
        $projectEntity3->setValue("name", "Test Project 3");
        $projectEntity3->addMultiValue("members", $memberId1, "Member One");
        $projectEntity3->addMultiValue("members", $memberId2, "Member Two");
        $projectEntity3->addMultiValue("members", $memberId3, "Member Three");
        $pid3 = $loader->save($projectEntity3, $this->account->getSystemUser());

        // Set the entities so it will be cleaned up properly
        $this->testEntities[] = $projectEntity;
        $this->testEntities[] = $projectEntity1;
        $this->testEntities[] = $projectEntity2;
        $this->testEntities[] = $projectEntity3;

        /*
         * Query the project of a specific member with 1 project
         */
        $query = new EntityQuery(ObjectTypes::PROJECT);
        $query->where("members")->equals($memberId);

        // Execute the query
        $res = $index->executeQuery($query);

        $resultEntity = $res->getEntity(0);
        $this->assertEquals($pid, $resultEntity->getEntityId());
        $this->assertEquals($projectEntity->getName(), $resultEntity->getName());
        $this->assertEquals(1, $res->getTotalNum());

        /*
         * Query the project of a specific member with multiple projects
         */
        $query = new EntityQuery(ObjectTypes::PROJECT);
        $query->where("members")->equals($memberId1);
        $res = $index->executeQuery($query);

        // This will have a result of 3 since $member1 has 3 projects
        $this->assertEquals(3, $res->getTotalNum());

        /*
         * Query the projects of two different members
         */
        $query = new EntityQuery(ObjectTypes::PROJECT);
        $query->where("members")->equals($memberId1);
        $query->orWhere("members")->equals($memberId);
        $res = $index->executeQuery($query);

        // This will have a result of 4 projects since both $member1 has 3 projects while $memberId has 1
        $this->assertEquals(4, $res->getTotalNum());

        /*
         * Query the projects of two different members that only has 1 projects each
         */
        $query = new EntityQuery(ObjectTypes::PROJECT);
        $query->where("members")->equals($memberId);
        $query->orWhere("members")->equals($memberId3);
        $res = $index->executeQuery($query);

        // This will have a result of two since both $member and $member3 has one project each
        $this->assertEquals(2, $res->getTotalNum());

        /*
         * Query the projects that has the same members
         */
        $query = new EntityQuery(ObjectTypes::PROJECT);
        $query->where("members")->equals($memberId2);
        $query->andWhere("members")->equals($memberId3);
        $res = $index->executeQuery($query);

        // This will have a result of 1 project since both $member2 and $member3 has one project each
        $this->assertEquals(1, $res->getTotalNum());
        $this->assertEquals($pid3, $res->getEntity(0)->getEntityId());

        /*
         * Query the projects that has the same members and will include other project using "or" condition
         */
        $query = new EntityQuery(ObjectTypes::PROJECT);
        $query->where("members")->equals($memberId1);
        $query->andWhere("members")->equals($memberId2);
        $query->orWhere("members")->equals($memberId);
        $res = $index->executeQuery($query);

        $this->assertEquals(2, $res->getTotalNum());

        /*
         * Create a query that will use members and name field
         */
        $query = new EntityQuery(ObjectTypes::PROJECT);
        $query->where("members")->equals($memberId1);
        $query->andWhere("name")->equals("Test Project");
        $res = $index->executeQuery($query);

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
        $this->assertEquals($testEnt->getEntityId(), $obj->getEntityId());
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

        // Save a test object
        $testEnt = $this->createTestCustomer();

        // Query value
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('name')->equals($testEnt->getValue("name"));
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getEntityId(), $obj->getEntityId());

        // Query null - first name is not set
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('first_name')->equals(null);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        // Set customer name with single quote and double quote
        $serviceManager = $this->account->getServiceManager();
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $customerName = "customer's new name with double \"";
        $testEnt->setValue('name', $customerName);
        $entityLoader->save($testEnt, $this->account->getSystemUser());

        $query->where('name')->equals($customerName);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        // Test null
        // -------------------------------------------------
        $testEnt->setValue("type_id", null);
        $this->account->getServiceManager()->get(EntityDataMapperFactory::class)->save(
            $testEnt,
            $this->account->getSystemUser()
        );
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('type_id')->equals(null);
        $res = $index->executeQuery($query);
        $this->assertTrue($res->getTotalNum() >= 1);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        // Test null
        // -------------------------------------------------
        $cachedStatus = $testEnt->getValue("status_id");
        $testEnt->setValue("status_id", null);
        $this->account->getServiceManager()->get(EntityDataMapperFactory::class)->save(
            $testEnt,
            $this->account->getSystemUser()
        );
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('status_id')->equals(null);
        $res = $index->executeQuery($query);
        $this->assertTrue($res->getTotalNum() >= 1);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        $cachedGroups = $testEnt->getValue("groups");
        $testEnt->setValue("groups", null);
        $this->account->getServiceManager()->get(EntityDataMapperFactory::class)->save(
            $testEnt,
            $this->account->getSystemUser()
        );

        // Test null for groups
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $groups = $testEnt->getValue("groups");
        $query->where('groups')->equals(null);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        // Test empty groups
        $testEnt->setValue("groups", []);
        $this->account->getServiceManager()->get(EntityDataMapperFactory::class)->save(
            $testEnt,
            $this->account->getSystemUser()
        );

        // Test null for groups
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $groups = $testEnt->getValue("groups");
        $query->where('groups')->equals("");
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity($i);
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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

        $dm = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);

        // Create a test customer
        $testEnt = $this->createTestCustomer();

        // Create a test case attached to the customer
        $case = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::ISSUE, $this->account->getAccountId());
        $case->setValue("name", "Unit Test Case");
        $case->setValue("customer_id", $testEnt->getEntityId(), $testEnt->getName());
        $cid = $dm->save($case, $this->account->getSystemUser());

        // Make sure this gets cleaned up
        $this->testEntities[] = $case;

        // Query for customer id
        $query = new EntityQuery($case->getObjType());
        $query->where('customer_id')->equals($testEnt->getEntityId());
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());

        // Query with null customer id
        $case->setValue("customer_id", "");
        $dm->save($case, $this->account->getSystemUser());
        $query = new EntityQuery($case->getObjType());
        $query->where('entity_id')->equals($case->getEntityId());
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

        $dm = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);

        // Create a test project with a member
        $project = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::PROJECT, $this->account->getAccountId());
        $project->setValue("name", "Unit Test Project");
        $project->setValue("members", $this->user->getEntityId(), $this->user->getName());
        $pid = $dm->save($project, $this->account->getSystemUser());

        // Make sure this gets cleaned up
        $this->testEntities[] = $project;

        // Query for project members
        $query = new EntityQuery($project->getObjType());
        $query->where('members')->equals($this->user->getEntityId());
        $res = $index->executeQuery($query);
        $this->assertEquals($project->getEntityId(), $res->getEntity(0)->getEntityId());
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

        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        // Create a test customer
        $testEnt = $this->createTestCustomer();

        // Create a notification for this customer
        $objReference = $testEnt->getEntityId();
        $notification = $entityLoader->create(ObjectTypes::NOTIFICATION, $this->account->getAccountId());
        $notification->setValue("name", "Unit Test Notification");
        $notification->setValue("obj_reference", $objReference);
        $entityLoader->save($notification, $this->account->getSystemUser());

        // Make sure this gets cleaned up
        $this->testEntities[] = $notification;

        // Query for this notification
        $query = new EntityQuery($notification->getDefinition()->getObjType());
        $query->where('obj_reference')->equals($objReference);
        $res = $index->executeQuery($query);
        $this->assertGreaterThan(0, $res->getTotalNum());

        // Now set the object reference to null for testing empty
        $notification->setValue("obj_reference", "");
        $entityLoader->save($notification, $this->account->getSystemUser());

        // Query the null condition
        $query = new EntityQuery($notification->getDefinition()->getObjType());
        $query->where('entity_id')->equals($notification->getEntityId());
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        // Should be able to perform query even if condition value has single quote
        $query->where('name')->doesNotEqual("non-existing customer with single quote ' ");
        $res = $index->executeQuery($query);
        $this->assertGreaterThanOrEqual(1, $res->getTotalNum());
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        // Set customer name with single quote and double quote
        $serviceManager = $this->account->getServiceManager();
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $customerName = "customer's new name with double \"";
        $testEnt->setValue('name', $customerName);
        $entityLoader->save($testEnt, $this->account->getSystemUser());

        // Test begin with using a condition value with single quote
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('name')->beginsWith("customer's new");
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        // Set customer name with single quote and double quote
        $serviceManager = $this->account->getServiceManager();
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $customerName = "customer's new name with double \"";
        $testEnt->setValue('name', $customerName);
        $entityLoader->save($testEnt, $this->account->getSystemUser());

        // Test contains using a condition value with single quote
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('name')->contains("tomer's new");
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
            if ($ent->getEntityId() == $testEnt->getEntityId()) {
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
        $this->account->getServiceManager()->get(EntityDataMapperFactory::class)->save(
            $testEnt,
            $this->account->getSystemUser()
        );

        $query = new EntityQuery($testEnt->getObjType());
        $query->where('entity_id')->equals($testEnt->getEntityId());
        $query->where('last_contacted')->lastNumDays(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());

        // Day - exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('entity_id')->equals($testEnt->getEntityId());
        $query->where('last_contacted')->lastNumDays(1);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getEntityId(), $obj->getEntityId());

        // Week - inclusive
        // -------------------------------------------------
        $testEnt->setValue("last_contacted", strtotime("-2 weeks"));
        $this->account->getServiceManager()->get(EntityDataMapperFactory::class)->save(
            $testEnt,
            $this->account->getSystemUser()
        );

        $query = new EntityQuery($testEnt->getObjType());
        $query->where('entity_id')->equals($testEnt->getEntityId());
        $query->where('last_contacted')->lastNumWeeks(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());

        // Week - exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('entity_id')->equals($testEnt->getEntityId());
        $query->where('last_contacted')->lastNumWeeks(1);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getEntityId(), $obj->getEntityId());

        // Month - inclusive
        // -------------------------------------------------
        $testEnt->setValue("last_contacted", strtotime("-2 months"));
        $this->account->getServiceManager()->get(EntityDataMapperFactory::class)->save(
            $testEnt,
            $this->account->getSystemUser()
        );

        $query = new EntityQuery($testEnt->getObjType());
        $query->where('entity_id')->equals($testEnt->getEntityId());
        $query->where('last_contacted')->lastNumMonths(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());

        // Month - exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('entity_id')->equals($testEnt->getEntityId());
        $query->where('last_contacted')->lastNumMonths(1);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getEntityId(), $obj->getEntityId());

        // Year - inclusive
        // -------------------------------------------------
        $testEnt->setValue("last_contacted", strtotime("-2 years"));
        $this->account->getServiceManager()->get(EntityDataMapperFactory::class)->save(
            $testEnt,
            $this->account->getSystemUser()
        );

        $query = new EntityQuery($testEnt->getObjType());
        $query->where('entity_id')->equals($testEnt->getEntityId());
        $query->where('last_contacted')->lastNumYears(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());

        // Year - exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('entity_id')->equals($testEnt->getEntityId());
        $query->where('last_contacted')->lastNumYears(1);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getEntityId(), $obj->getEntityId());
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
        $this->account->getServiceManager()->get(EntityDataMapperFactory::class)->save(
            $testEnt,
            $this->account->getSystemUser()
        );

        $query = new EntityQuery($testEnt->getObjType());
        $query->where('entity_id')->equals($testEnt->getEntityId());
        $query->where('last_contacted')->nextNumDays(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getEntityId(), $obj->getEntityId());

        // Day - exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('entity_id')->equals($testEnt->getEntityId());
        $query->where('last_contacted')->nextNumDays(1);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());

        // Week - inclusive
        // -------------------------------------------------
        $testEnt->setValue("last_contacted", strtotime("+2 weeks"));
        $this->account->getServiceManager()->get(EntityDataMapperFactory::class)->save(
            $testEnt,
            $this->account->getSystemUser()
        );

        $query = new EntityQuery($testEnt->getObjType());
        $query->where('entity_id')->equals($testEnt->getEntityId());
        $query->where('last_contacted')->nextNumWeeks(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getEntityId(), $obj->getEntityId());

        // Week - exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('entity_id')->equals($testEnt->getEntityId());
        $query->where('last_contacted')->nextNumWeeks(1);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());

        // Month - inclusive
        // -------------------------------------------------
        $testEnt->setValue("last_contacted", strtotime("+2 months"));
        $this->account->getServiceManager()->get(EntityDataMapperFactory::class)->save(
            $testEnt,
            $this->account->getSystemUser()
        );

        $query = new EntityQuery($testEnt->getObjType());
        $query->where('entity_id')->equals($testEnt->getEntityId());
        $query->where('last_contacted')->nextNumMonths(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getEntityId(), $obj->getEntityId());

        // Month - exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('entity_id')->equals($testEnt->getEntityId());
        $query->where('last_contacted')->nextNumMonths(1);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());

        // Year - inclusive
        // -------------------------------------------------
        $testEnt->setValue("last_contacted", strtotime("+2 years"));
        $this->account->getServiceManager()->get(EntityDataMapperFactory::class)->save(
            $testEnt,
            $this->account->getSystemUser()
        );

        $query = new EntityQuery($testEnt->getObjType());
        $query->where('entity_id')->equals($testEnt->getEntityId());
        $query->where('last_contacted')->nextNumYears(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getEntityId(), $obj->getEntityId());

        // Year - exclusive
        // -------------------------------------------------
        $query = new EntityQuery($testEnt->getObjType());
        $query->where('entity_id')->equals($testEnt->getEntityId());
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
        $dm = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);
        $obj = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::TASK, $this->account->getAccountId());
        $obj->setValue("name", "testSearchDeleted");
        $oid = $dm->save($obj, $this->account->getSystemUser());
        $dm->delete($obj, $this->account->getAuthenticatedUser());

        // First test regular query without f_deleted flag set
        $query = new EntityQuery(ObjectTypes::TASK);
        $query->where('entity_id')->equals($oid);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());

        // Test deleted flag set should return with deleted customer
        $query = new EntityQuery(ObjectTypes::TASK);
        $query->where('entity_id')->equals($oid);
        $query->where('f_deleted')->equals(true);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $ent = $res->getEntity(0);
        $this->assertEquals($oid, $ent->getEntityId());

        // Cleanup
        $dm->delete($obj, $this->account->getAuthenticatedUser());
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
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $dm = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);

        $folder1 = $loader->create(ObjectTypes::FOLDER, $this->account->getAccountId());
        $folder1->setValue("name", "My Test Folder");
        $dm->save($folder1, $this->account->getSystemUser());
        $this->assertNotNull($folder1->getEntityId());

        $folder2 = $loader->create(ObjectTypes::FOLDER, $this->account->getAccountId());
        $folder2->setValue("name", "My Test SubFolder");
        $folder2->setValue("parent_id", $folder1->getEntityId());
        $dm->save($folder2, $this->account->getSystemUser());
        $this->assertNotNull($folder2->getEntityId());

        $children = $index->getHeiarchyDownObj(ObjectTypes::FOLDER, $folder1->getEntityId());
        $this->assertTrue(count($children) > 0);
        $found1 = false;
        $found2 = false;
        foreach ($children as $gid) {
            if ($gid == $folder1->getEntityId()) {
                $found1 = true;
            }
            if ($gid == $folder2->getEntityId()) {
                $found2 = true;
            }
        }
        $this->assertTrue($found1);
        $this->assertTrue($found2);

        // Cleanup
        $dm->delete($folder2, $this->account->getAuthenticatedUser());
        $dm->delete($folder1, $this->account->getAuthenticatedUser());
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
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $dm = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);

        $folder1 = $loader->create(ObjectTypes::FOLDER, $this->account->getAccountId());
        $folder1->setValue("name", "My Test Folder");
        $dm->save($folder1, $this->account->getSystemUser());
        $this->assertNotNull($folder1->getEntityId());

        $folder2 = $loader->create(ObjectTypes::FOLDER, $this->account->getAccountId());
        $folder2->setValue("name", "My Test SubFolder");
        $folder2->setValue("parent_id", $folder1->getEntityId());
        $dm->save($folder2, $this->account->getSystemUser());
        $this->assertNotNull($folder2->getEntityId());

        $children = $index->getHeiarchyUpObj(ObjectTypes::FOLDER, $folder2->getEntityId());
        $this->assertTrue(count($children) > 0);
        $found1 = false;
        $found2 = false;
        foreach ($children as $gid) {
            if ($gid == $folder1->getEntityId()) {
                $found1 = true;
            }
            if ($gid == $folder2->getEntityId()) {
                $found2 = true;
            }
        }
        $this->assertTrue($found1);
        $this->assertTrue($found2);

        // Cleanup
        $dm->delete($folder2, $this->account->getAuthenticatedUser());
        $dm->delete($folder1, $this->account->getAuthenticatedUser());
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

        $property = new \ReflectionProperty(IndexAbstract::class, "pluginsLoaded");
        $property->setAccessible(true);
        $property->setValue($index, [ObjectTypes::CONTACT => $testPlugin]);

        // Query value
        $query = new EntityQuery(ObjectTypes::CONTACT);
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
            break;
        }
    }

    /**
     * Make sure that we are able to query the object reference entity
     */
    public function testQueryObjectReference()
    {
        $dm = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);

        // Create an entity and initialize values
        $customerName = "Test Customer";
        $customer = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::CONTACT, $this->account->getAccountId());
        $customer->setValue("name", $customerName);
        $customer->setValue("owner_id", $this->user->getEntityId());
        $cid = $dm->save($customer, $this->user);

        $customerEntity = $dm->getEntityById($cid);
        $this->assertEquals($customerEntity->getName(), $customerName);

        // Create reminder and set the customer as our object reference
        $customerReminder = "Customer Reminder";
        $reminder = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::REMINDER, $this->account->getAccountId());
        $reminder->setValue("name", $customerReminder);
        $reminder->setValue("obj_reference", $customer->getEntityId());
        $rid = $dm->save($reminder, $this->user);

        // Set the entities so it will be cleaned up properly
        $this->testEntities[] = $customer;
        $this->testEntities[] = $reminder;

        $reminderEntity = $dm->getEntityId($rid);
        $this->assertEquals($reminderEntity->getName(), $customerReminder);
        $this->assertEquals($reminderEntity->getValue("obj_reference"), $customer->getEntityId());
        $this->assertEquals($reminderEntity->getValueName("obj_reference"), $customerName);

        // Now query the customer's reminder using the obj reference used
        $query = new Netric\EntityQuery(ObjectTypes::REMINDER);
        $query->where("obj_reference")->equals($customer->getEntityId());
        $query->where("entity_id")->equals($rid);

        $index = $this->account->getServiceManager()->get(IndexFactory::class);
        // Execute the query
        $res = $index->executeQuery($query);

        $this->assertEquals(1, $res->getTotalNum());

        // This should be the test reminder we created that was associated with the customer
        $resultEntity = $res->getEntity(0);
        $this->assertEquals($rid, $resultEntity->getEntityId());
        $this->assertEquals("Customer Reminder", $resultEntity->getName());
    }

    /**
     * Make sure "OR" and "AND" query conditions will work
     * @group testBooleanOperatorsWithConditions
     */
    public function testBooleanOperatorsWithConditions()
    {
        $dm = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);

        // Create an entity and initialize values
        $customerName1 = "Test Customer 1";
        $customer1 = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::CONTACT, $this->account->getAccountId());
        $customer1->setValue("name", $customerName1);
        $customer1->setValue("owner_id", $this->user->getEntityId());
        $customer1->setValue("type_id", "1");
        $customer1->setValue("city", "new_city");
        $cid1 = $dm->save($customer1, $this->user);

        $customerName2 = "Test Customer 2";
        $customer2 = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::CONTACT, $this->account->getAccountId());
        $customer2->setValue("name", $customerName2);
        $customer2->setValue("owner_id", $this->user->getEntityId());
        $customer2->setValue("type_id", "1");
        $customer2->setValue("city", "old_city");
        $cid2 = $dm->save($customer2, $this->user);

        $customerName3 = "Test Customer 3";
        $customer3 = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::CONTACT, $this->account->getAccountId());
        $customer3->setValue("name", $customerName3);
        $customer3->setValue("owner_id", $this->user->getEntityId());
        $customer3->setValue("type_id", "2");
        $customer3->setValue("city", "new_city");
        $cid3 = $dm->save($customer3, $this->user);

        $customerName4 = "Test Customer 4";
        $customer4 = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::CONTACT, $this->account->getAccountId());
        $customer4->setValue("name", $customerName3);
        $customer4->setValue("owner_id", $this->user->getEntityId());
        $customer4->setValue("type_id", "2");
        $customer4->setValue("city", "old_city");
        $cid4 = $dm->save($customer4, $this->user);

        // Set the entities so it will be cleaned up properly
        $this->testEntities[] = $customer1;
        $this->testEntities[] = $customer2;
        $this->testEntities[] = $customer3;
        $this->testEntities[] = $customer4;

        // Query the customers using and where conditions. This should only query the customer 1
        $query = new Netric\EntityQuery(ObjectTypes::CONTACT);
        $query->where("type_id")->equals(1);
        $query->where("city")->equals("new_city");

        $index = $this->account->getServiceManager()->get(IndexFactory::class);
        // Execute the query
        $res = $index->executeQuery($query);

        $this->assertEquals(1, $res->getTotalNum());
        $resultEntity = $res->getEntity(0);
        $this->assertEquals(1, $resultEntity->getValue("type_id"));
        $this->assertEquals("new_city", $resultEntity->getValue("city"));

        // Query the customers using or where conditions. This should query all the customers
        $query = new Netric\EntityQuery(ObjectTypes::CONTACT);
        $query->orWhere("type_id")->equals(1);
        $query->orWhere("type_id")->equals(2);

        $index = $this->account->getServiceManager()->get(IndexFactory::class);
        // Execute the query
        $res = $index->executeQuery($query);

        // We should be be able to query all 4 customers
        $this->assertEquals(4, $res->getTotalNum());

        // Query the customers using the combination of or/and where conditions.
        $query = new Netric\EntityQuery(ObjectTypes::CONTACT);
        $query->where("type_id")->equals(1);
        $query->orWhere("city")->equals("old_city");

        $index = $this->account->getServiceManager()->get(IndexFactory::class);
        // Execute the query
        $res = $index->executeQuery($query);

        // We should be be able to query all 3 customers
        $this->assertEquals(3, $res->getTotalNum());
    }

    /**
     * Run tests with combination of "and" and "or" conditions
     */
    public function testEntityQueryWithOrderBy()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        $serviceManager = $this->account->getServiceManager();
        $index = $serviceManager->get(IndexFactory::class);
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);

        $customerName1 = "Test Customer 1";
        $customer1 = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::CONTACT, $this->account->getAccountId());
        $customer1->setValue("name", $customerName1);
        $customer1->setValue("owner_id", $this->user->getEntityId());
        $customer1->setValue("type_id", "1");

        $dm = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);
        $cid1 = $dm->save($customer1, $this->user);

        // Set the entities so it will be cleaned up properly
        $this->testEntities[] = $customer1;

        $query = new EntityQuery(ObjectTypes::CONTACT);
        $query->where('type_id')->equals(1);
        $query->orderBy('name');
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $this->assertEquals($cid1, $res->getEntity(0)->getEntityId());
    }

    /**
     * Test aggregations in query
     */
    public function testSettingOfAggregationsInQuery()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        $task = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::TASK, $this->account->getAccountId());
        $task->setValue("name", "Task 1");
        $task->setValue("cost_actual", 1);

        $dm = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);
        $taskId = $dm->save($task, $this->account->getSystemUser());

        // Set the entities so it will be cleaned up properly
        $this->testEntities[] = $task;

        $query = new EntityQuery(ObjectTypes::TASK);
        $query->where('entity_id')->equals($taskId);

        $agg = new Min("test");
        $agg->setField("cost_actual");
        $query->addAggregation($agg);
        $agg = $index->executeQuery($query)->getAggregation("test");
        $this->assertGreaterThanOrEqual($agg, 1);
    }
}

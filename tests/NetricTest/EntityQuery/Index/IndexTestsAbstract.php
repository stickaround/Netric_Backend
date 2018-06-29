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
use Netric\Entity\EntityInterface;
use PHPUnit\Framework\TestCase;
use Netric\Entity\EntityLoaderFactory;

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
        $this->user = $this->account->getUser(\Netric\Entity\ObjType\UserEntity::USER_SYSTEM);
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
         * Test multiple or conditions and 1 "and" blogic
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
        $this->assertEquals($res->getEntity(0)->getValue("type_id"), $organizationTypeId);

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
        $this->assertEquals($res->getEntity(0)->getValue("id"), $customer1->getValue("id"));

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

        /*
         * Test using does not equals
         */
        $query = new EntityQuery($testObjType);
        $query->where('type_id')->doesNotEqual($organizationTypeId);
        $res = $index->executeQuery($query);

        $this->assertGreaterThanOrEqual(2, $res->getTotalNum());
        $this->assertNotEquals($organizationTypeId, $res->getEntity(0)->getValue("type_id"));

        /*
         * Test using does not equals with multiple "and" blogic
         */
        $query = new EntityQuery($testObjType);
        $query->where('type_id')->doesNotEqual($personTypeId);
        $query->andWhere('status_id')->doesNotEqual($customer1->getValue("status_id"));
        $res = $index->executeQuery($query);

        $this->assertGreaterThanOrEqual(2, $res->getTotalNum());
        $this->assertNotEquals($personTypeId, $res->getEntity(0)->getValue("type_id"));
        $this->assertNotEquals($customer1->getValue("status_id"), $res->getEntity(0)->getValue("status_id"));
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

        $groupId = rand();
        $groupId1 = rand();
        $groupId2 = rand();

        // Create a project that only has 1 member
        $projectEntity = $loader->create("project");
        $projectEntity->setValue("name", "Test Project");
        $projectEntity->addMultiValue("members", $memberId, "Member");
        $projectEntity->addMultiValue("groups", $groupId, "First Project");
        $projectEntity->addMultiValue("groups", $groupId2, "Multiple Project");
        $pid = $loader->save($projectEntity);

        // Create a project that has 1 member
        $projectEntity1 = $loader->create("project");
        $projectEntity1->setValue("name", "Test Project 1");
        $projectEntity1->addMultiValue("members", $memberId1, "Member One");
        $projectEntity1->addMultiValue("groups", $groupId, "First Project");
        $pid1 = $loader->save($projectEntity1);

        // Create a project that has 2 members
        $projectEntity2 = $loader->create("project");
        $projectEntity2->setValue("name", "Test Project 2");
        $projectEntity2->addMultiValue("members", $memberId1, "Member One");
        $projectEntity2->addMultiValue("members", $memberId2, "Member Two");
        $projectEntity2->addMultiValue("groups", $groupId1, "Second Project");
        $pid2 = $loader->save($projectEntity2);

        // Create a project that only has 3 members
        $projectEntity3 = $loader->create("project");
        $projectEntity3->setValue("name", "Test Project 3");
        $projectEntity3->addMultiValue("members", $memberId1, "Member One");
        $projectEntity3->addMultiValue("members", $memberId2, "Member Two");
        $projectEntity3->addMultiValue("members", $memberId3, "Member Three");
        $projectEntity3->addMultiValue("groups", $groupId1, "Second Project");
        $projectEntity3->addMultiValue("groups", $groupId2, "Multiple Project");
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
        $query->where("members")->equals($memberId2);
        $query->andWhere("members")->equals($memberId3);
        $res = $index->executeQuery($query);

        // This will have a result of 1 project since both $member and $member3 has one project each
        $this->assertEquals(1, $res->getTotalNum());
        $this->assertEquals($pid3, $res->getEntity(0)->getId());

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
        $res = $index->executeQuery($query);

        // This will have 0 results since $member1 is not a member in Test Project
        $this->assertEquals(0, $res->getTotalNum());

        /*
         * Query the project using 2 multi fields
         */
        $query = new EntityQuery("project");
        $query->where("members")->equals($memberId);
        $query->andWhere("groups")->equals($groupId2);
        $res = $index->executeQuery($query);

        // Should get 1 project since we only have one project that is $memberId and $groupId2
        $this->assertEquals(1, $res->getTotalNum());
        $this->assertEquals($pid, $res->getEntity(0)->getId());

        /*
         * Query the project using 2 multi fields with "and" / "or" blogic
         */
        $query = new EntityQuery("project");
        $query->where("members")->equals($memberId);
        $query->orWhere("groups")->equals($groupId1);
        $res = $index->executeQuery($query);

        // Should get 3 projects since
        $this->assertEquals(3, $res->getTotalNum());
    }
}

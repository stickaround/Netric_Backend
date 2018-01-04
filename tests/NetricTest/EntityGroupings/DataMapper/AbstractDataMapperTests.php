<?php
namespace NetricTest\Entity\DataMapper;

use Netric\Entity\Entity;
use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperInterface;
use PHPUnit\Framework\TestCase;

/**
 * Define common tests that will need to be run with all data mappers.
 *
 * In order to implement the unit tests, a datamapper test case just needs
 * to extend this class and create a getDataMapper class that returns the
 * datamapper to be tested
 */
abstract class AbstractDataMapperTests extends TestCase
{
    /**
     * Tenant account
     *
     * @var \Netric\Account\Account
     */
    protected $account = null;

    /**
     * Administrative user
     *
     * @var \Netric\User
     */
    protected $user = null;

    /**
     * Test entities created that needt to be cleaned up
     *
     * @var EntityGroupingDataMapperInterface
     */
    protected $testEntities = [];

    /**
     * Setup each test
     */
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->user = $this->account->getUser(\Netric\Entity\ObjType\UserEntity::USER_SYSTEM);
    }

    /**
     * Cleanup any test entities we created
     */
    protected function tearDown()
    {
        $dm = $this->getDataMapper();
        foreach ($this->testEntities as $entity) {
            $dm->delete($entity, true);
        }
    }

    /**
     * Setup datamapper for the parent DataMapperTests class
     *
     * @return EntityGroupingDataMapperInterface
     */
    abstract protected function getDataMapper();

    /**
     * Utility function to populate custome entity for testing
     *
     * @return Entity
     */
    protected function createCustomer()
    {
        $customer = $this->account->getServiceManager()->get("EntityLoader")->create("customer");
        // text
        $customer->setValue("name", "Entity_DataMapperTests");
        // bool
        $customer->setValue("f_nocall", true);
        // object
        $customer->setValue("owner_id", $this->user->getId(), $this->user->getName());
        // object_multi
        // timestamp
        $contactedTime = mktime(0, 0, 0, 12, 1, 2013);
        $customer->setValue("last_contacted", $contactedTime);

        return $customer;
    }


    /**
     * Test adding, removing and updating groups
     */
    public function testSaveGroupings()
    {
        $dm = $this->getDataMapper();

        $groupings = $dm->getGroupings("customer", "groups");

        // Save new
        $newGroup = $groupings->create();
        $newGroup->name = "UTTEST DM::testSaveGroupings";
        $groupings->add($newGroup);
        $savedGroupings = $dm->saveGroupings($groupings);
        $group = $savedGroupings->getByName($newGroup->name);
        $this->assertNotEquals($group->id, "");

        // Save existing
        $name2 = "UTTEST DM::testSaveGroupings::edited";
        $group = $savedGroupings->getByName($newGroup->name);
        $group->name = $name2;
        $group->setDirty(true);
        $dm->saveGroupings($savedGroupings);
        $gid = $group->id;

        unset($groupings);
        $groupings = $dm->getGroupings("customer", "groups");
        $group = $groupings->getById($gid);
        $this->assertEquals($name2, $group->name);

        // Test delete
        $groupings->delete($gid);
        $dm->saveGroupings($groupings);
        unset($groupings);
        $groupings = $dm->getGroupings("customer", "groups");
        $this->assertFalse($groupings->getById($gid));
    }

    /**
     * Loading groupings
     */
    public function testGetGroupings()
    {
        $dm = $this->getDataMapper();

        // No filter
        $groupings = $dm->getGroupings("customer", "groups");

        // Delete just in case
        if ($groupings->getByName("UTEST.DM.testGetGroupings"))
        {
            $groupings->delete($groupings->getByName("UTEST.DM.testGetGroupings")->id);
            $dm->saveGroupings($groupings);
        }

        // Save new
        $newGroup = $groupings->create();
        $newGroup->name = "UTEST.DM.testGetGroupings";
        $groupings->add($newGroup);
        $dm->saveGroupings($groupings);
        $groupings = $dm->getGroupings("customer", "groups");
        $group1 = $groupings->getByName($newGroup->name);
        $this->assertEquals($newGroup->name, $group1->name);

        // Add a subgroup
        $newGroup2 = $groupings->create();
        $newGroup2->name = "UTEST.DM.testGetGroupings2";
        $newGroup2->parentId = $group1->id;
        $groupings->add($newGroup2);
        $dm->saveGroupings($groupings);
        unset($groupings);
        $groupings = $dm->getGroupings("customer", "groups");
        $group2 = $groupings->getByPath($newGroup->name . "/" . $newGroup2->name);
        $this->assertEquals($newGroup2->name, $group2->name);

        // Cleanup
        $groupings->delete($group1->id);
        $groupings->delete($group2->id);
        $dm->saveGroupings($groupings);
    }
}


<?php
namespace NetricTest\EntityGroupings\DataMapper;

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
     * Setup each test
     */
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->user = $this->account->getUser(\Netric\Entity\ObjType\UserEntity::USER_SYSTEM);
    }

    /**
     * Setup datamapper for the parent DataMapperTests class
     *
     * @return EntityGroupingDataMapperInterface
     */
    abstract protected function getDataMapper();


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
        $dm->saveGroupings($groupings);
        $group = $groupings->getByName($newGroup->name);
        $this->assertNotEquals($group->id, "");

        // Save existing
        $name2 = "UTTEST DM::testSaveGroupings::edited";
        $group = $groupings->getByName($newGroup->name);
        $group->name = $name2;
        $group->setDirty(true);
        $dm->saveGroupings($groupings);
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
        if ($groupings->getByName("UTEST.DM.testGetGroupings")) {
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

    /**
     * Test entity has moved functionalty
     */
    public function testCommitImcrement()
    {
        $dm = $this->getDataMapper();
        
        // No filter grouping
        $groupings = $dm->getGroupings("customer", "groups");
        
        // Save new
        $newGroup = $groupings->create();
        $newGroup->name = "UTEST.DM.testGetGroupings";
        $groupings->add($newGroup);
        $dm->saveGroupings($groupings);
        $oldCommitId = $groupings->getByName($newGroup->name)->commitId;
        $this->assertNotEquals(0, $oldCommitId);

		// Add another to increment commit id
        $newGroup2 = $groupings->create();
        $newGroup2->name = "UTEST.DM.testGetGroupings2";
        $groupings->add($newGroup2);
        $dm->saveGroupings($groupings);
        $newCommitId = $groupings->getByName($newGroup2->name)->commitId;
        $this->assertNotEquals($oldCommitId, $newCommitId);

        // Reload and double check commitIDs
        $groupings = $dm->getGroupings("customer", "groups");
        $oldCommitId = $groupings->getByName($newGroup->name)->commitId;
        $newCommitId = $groupings->getByName($newGroup2->name)->commitId;
        $this->assertNotEquals($oldCommitId, $newCommitId);

		// Cleanup
        $groupings->delete($newGroup->id);
        $groupings->delete($newGroup2->id);
        $dm->saveGroupings($groupings);
    }
}


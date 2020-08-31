<?php

namespace NetricTest\EntityGroupings;

use PHPUnit\Framework\TestCase;
use Netric\EntityGroupings\EntityGroupings;
use Netric\EntityGroupings\Group;
use Netric\EntityDefinition\ObjectTypes;
use NetricTest\Bootstrap;

class EntityGroupingsTest extends TestCase
{
    /**
     * Handle to account
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
    }

    /**
     * Test the path of grouping
     */
    public function testGroupingPath()
    {
        $groupings = new EntityGroupings(ObjectTypes::CONTACT . "/group", $this->account->getAccountId());
        $this->assertEquals($groupings->path, ObjectTypes::CONTACT . "/group");
        $this->assertEquals($groupings->getObjType(), ObjectTypes::CONTACT);
        $this->assertEquals($groupings->getFieldName(), "group");

        $groupingsWithUserGuid = new EntityGroupings(ObjectTypes::CONTACT . "/group/0000-0000-user-guid", $this->account->getAccountId());
        $this->assertEquals($groupingsWithUserGuid->getUserGuid(), "0000-0000-user-guid");

        // Should throw an exception if we provide an invalid path for entity groupings
        $this->expectExceptionMessage("Entity groupings should at least have 2 parts obj_type/field_name.");
        new EntityGroupings(ObjectTypes::CONTACT, $this->account->getAccountId());
    }

    /**
     * Test adding a grouping
     */
    public function testAdd()
    {
        $groupings = new EntityGroupings("test/group", $this->account->getAccountId());
        $group = new Group();
        $group->setName("My Test");
        $groupings->add($group);

        $ret = $groupings->getAll();
        $this->assertEquals($group->name, $ret[0]->name);
    }

    /**
     * Test adding a grouping
     */
    public function testDelete()
    {
        $groupings = new EntityGroupings("test/group", $this->account->getAccountId());
        $group = new Group();
        $group->setGroupId('910428e6-474f-4cc2-8935-36e84d00771d');
        $group->setName('My Test');
        $groupings->add($group);
        $groupings->delete($group->getGroupId());

        $ret = $groupings->getDeleted();
        $this->assertEquals($group->getGroupId(), $ret[0]->getGroupId());

        $ret = $groupings->getAll();
        $this->assertEquals(0, count($ret));
    }

    /**
     * Test adding a grouping
     */
    public function testGetById()
    {
        $groupings = new EntityGroupings("test/group", $this->account->getAccountId());
        $group = new Group();
        $group->setGroupId('910428e6-474f-4cc2-8935-36e84d00771d');
        $group->setName("My Test");
        $groupings->add($group);

        $ret = $groupings->getByGuid($group->getGroupId());
        $this->assertEquals($group->getGroupId(), $ret->getGroupId());
    }

    /**
     * Test adding a grouping
     */
    public function testGetByName()
    {
        $groupings = new EntityGroupings("test/group", $this->account->getAccountId());
        $group = new Group();
        $group->setGroupId('910428e6-474f-4cc2-8935-36e84d00771d');
        $group->setName("My Test");
        $groupings->add($group);

        $ret = $groupings->getByName("My Test");
        $this->assertEquals($group->getGroupId(), $ret->getGroupId());
    }

    /**
     * Test adding a grouping
     */
    public function testGetByPath()
    {
        $groupings = new EntityGroupings("test/group", $this->account->getAccountId());

        $group = new Group();
        $group->setGroupId('910428e6-474f-4cc2-8935-36e84d00771d');
        $group->setName("My Test");
        $groupings->add($group);

        $group2 = new Group();
        $group->setGroupId('910428e6-474f-4dd2-8935-36e84d00771d');
        $group2->parentId = $group->id;
        $group2->setName("Sub Test");
        $groupings->add($group2);

        $ret = $groupings->getByPath("My Test/Sub Test");
        $this->assertEquals($group2->getGroupId(), $ret->getGroupId());
    }

    public function testCreate()
    {
        $groupings = new EntityGroupings("test/group", $this->account->getAccountId());
        $group = $groupings->create();
        $this->assertInstanceOf(Group::class, $group);
    }
}

<?php

namespace NetricTest\EntityGroupings;

use PHPUnit\Framework\TestCase;
use Netric\EntityGroupings\EntityGroupings;
use Netric\EntityGroupings\Group;
use Netric\EntityDefinition\ObjectTypes;

class EntityGroupingsTest extends TestCase
{
    /**
     * Test the path of grouping
     */
    public function testGroupingPath()
    {
        $groupings = new EntityGroupings(ObjectTypes::CONTACT . "/group");
        $this->assertEquals($groupings->path, ObjectTypes::CONTACT. "/group");
        $this->assertEquals($groupings->getObjType(), ObjectTypes::CONTACT);
        $this->assertEquals($groupings->getFieldName(), "group");
        
        $groupingsWithUserGuid = new EntityGroupings(ObjectTypes::CONTACT . "/group/0000-0000-user-guid");
        $this->assertEquals($groupingsWithUserGuid->getUserGuid(), "0000-0000-user-guid");

        // Should throw an exception if we provide an invalid path for entity groupings
        $this->expectExceptionMessage("Entity groupings should at least have 2 parts obj_type/field_name.");
        new EntityGroupings(ObjectTypes::CONTACT);
    }

    /**
     * Test adding a grouping
     */
    public function testAdd()
    {
        $groupings = new EntityGroupings("test/group");
        $group = new Group();
        $group->name = "My Test";
        $groupings->add($group);

        $ret = $groupings->getAll();
        $this->assertEquals($group->name, $ret[0]->name);
    }

    /**
     * Test adding a grouping
     */
    public function testDelete()
    {
        $groupings = new EntityGroupings("test/group");
        $group = new Group();
        $group->id = 1;
        $group->name = "My Test";
        $groupings->add($group);
        $groupings->delete($group->id);

        $ret = $groupings->getDeleted();
        $this->assertEquals($group->id, $ret[0]->id);

        $ret = $groupings->getAll();
        $this->assertEquals(0, count($ret));
    }

    /**
     * Test adding a grouping
     */
    public function testGetById()
    {
        $groupings = new EntityGroupings("test/group");
        $group = new Group();
        $group->id = 1;
        $group->name = "My Test";
        $groupings->add($group);

        $ret = $groupings->getById($group->id);
        $this->assertEquals($group->id, $ret->id);
    }

    /**
     * Test adding a grouping
     */
    public function testGetByName()
    {
        $groupings = new EntityGroupings("test/group");
        $group = new Group();
        $group->id = 1;
        $group->name = "My Test";
        $groupings->add($group);

        $ret = $groupings->getByName("My Test");
        $this->assertEquals($group->id, $ret->id);
    }

    /**
     * Test adding a grouping
     */
    public function testGetByPath()
    {
        $groupings = new EntityGroupings("test/group");

        $group = new Group();
        $group->id = 1;
        $group->name = "My Test";
        $groupings->add($group);

        $group2 = new Group();
        $group2->id = 2;
        $group2->parentId = $group->id;
        $group2->name = "Sub Test";
        $groupings->add($group2);

        $ret = $groupings->getByPath("My Test/Sub Test");
        $this->assertEquals($group2->id, $ret->id);
    }

    /**
     * Test adding a grouping
     */
    public function testGetPath()
    {
        $groupings = new EntityGroupings("test/group");

        $group = new Group();
        $group->id = 1;
        $group->name = "My Test";
        $groupings->add($group);

        $group2 = new Group();
        $group2->id = 2;
        $group2->parentId = $group->id;
        $group2->name = "Sub Test";
        $groupings->add($group2);

        $ret = $groupings->getPath($group2->id);
        $this->assertEquals("My Test/Sub Test", $ret);
    }

    public function testCreate()
    {
        $groupings = new EntityGroupings("test/group");
        $group = $groupings->create();
        $this->assertInstanceOf(Group::class, $group);
    }
}

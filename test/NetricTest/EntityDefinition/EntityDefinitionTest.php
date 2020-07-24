<?php

/**
 * Test entity definition loader class that is responsible for creating and initializing exisiting definitions
 */

namespace NetricTest\EntityDefinition;

use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\EntityDefinition;
use PHPUnit\Framework\TestCase;
use Netric\Permissions\Dacl;
use NetricTest\Bootstrap;
use Netric\EntityDefinition\ObjectTypes;

class EntityDefinitionTest extends TestCase
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
     * Test loading data into the definition from an array
     */
    public function testGetObjType()
    {
        $entDef = new EntityDefinition(ObjectTypes::CONTACT);

        $this->assertEquals(ObjectTypes::CONTACT, $entDef->getObjType());
    }

    /**
     * Test default fields
     */
    public function testSetDefaultFields()
    {
        // Constructor add default fields
        $entDef = new EntityDefinition(ObjectTypes::CONTACT);

        $field = $entDef->getField("entity_id");
        $this->assertEquals("entity_id", $field->name);
        $this->assertEquals("uuid", $field->type);
        $this->assertEquals(true, $field->system);
    }

    /**
     * Test loading data into the definition from an array
     */
    public function testFromArray()
    {
        $entDef = new EntityDefinition(ObjectTypes::CONTACT);

        // Dacl
        $dacl = new Dacl();
        $dacl->allowGroup(UserEntity::GROUP_USERS);

        $data = [
            "revision" => 10,
            "default_activity_level" => 7,
            "is_private" => true,
            "recur_rules" => [
                "field_time_start" => "ts_start",
                "field_time_end" => "ts_end",
                "field_date_start" => "ts_start",
                "field_date_end" => "ts_end",
                "field_recur_id" => "recurrence_pattern"
            ],
            "inherit_dacl_ref" => "project",
            "parent_field" => "parent",
            "uname_settings" => "parent:name",
            "list_title" => "subject",
            "icon" => "file",
            "fields" => [
                "subject" => [
                    "title" => "Subject",
                    "type" => "text",
                ],
            ],
            "dacl" => $dacl->toArray(),
        ];

        $entDef->fromArray($data);

        // Test values
        $this->assertEquals($entDef->revision, $data['revision']);
        $this->assertEquals($entDef->defaultActivityLevel, $data['default_activity_level']);
        $this->assertEquals($entDef->isPrivate, $data['is_private']);
        $this->assertEquals($entDef->inheritDaclRef, $data['inherit_dacl_ref']);
        $this->assertEquals($entDef->parentField, $data['parent_field']);
        $this->assertEquals($entDef->unameSettings, $data['uname_settings']);
        $this->assertEquals($entDef->listTitle, $data['list_title']);
        $this->assertEquals($entDef->icon, $data['icon']);

        // Test recur array
        $this->assertEquals($entDef->recurRules['field_time_start'], $data['recur_rules']['field_time_start']);
        // The rest of recur should be an array match

        // Test field
        $field = $entDef->getField("subject");
        $this->assertEquals("subject", $field->name);
        $this->assertEquals($data['fields']["subject"]['title'], $field->title);
        $this->assertEquals($data['fields']["subject"]['type'], $field->type);

        // Test default for store revisions
        $this->assertEquals(true, $entDef->storeRevisions);

        // Make sure the dacl was instantiated from the data
        $this->assertTrue(
            $entDef->getDacl()->groupIsAllowed(UserEntity::GROUP_USERS, Dacl::PERM_VIEW)
        );
    }

    /**
     * Test toArray to make sure data is mapping right
     */
    public function testToArray()
    {
        $entDef = new EntityDefinition(ObjectTypes::CONTACT);
        $data = $entDef->toArray();
        $this->assertTrue(is_array($data));
        // TODO: test more abotu the data returned
    }

    /**
     * Test the setter and getter for the title property
     */
    public function testSetAndGetTitle()
    {
        $title = "Test";
        $definition = new EntityDefinition(ObjectTypes::CONTACT);
        $definition->setTitle($title);
        $this->assertEquals($title, $definition->getTitle());
    }

    public function testSetAndGetDacl()
    {
        $dacl = new Dacl();
        $definition = new EntityDefinition(ObjectTypes::CONTACT);
        $definition->setDacl($dacl);
        $this->assertEquals($dacl, $definition->getDacl());
    }
}

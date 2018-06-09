<?php
/**
 * Test entity definition loader class that is responsible for creating and initializing exisiting definitions
 */
namespace NetricTest\EntityDefinition;

use Netric\EntityDefinition\EntityDefinition;
use PHPUnit\Framework\TestCase;
use Netric\Permissions\Dacl;

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
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
    }

    /**
     * Test loading data into the definition from an array
     */
    public function testGetObjType()
    {
        $entDef = new EntityDefinition("customer");
        
        $this->assertEquals("customer", $entDef->getObjType());
    }

    /**
     * Test default fields
     */
    public function testSetDefaultFields()
    {
        // Constructor add default fields
        $entDef = new EntityDefinition("customer");

        $field = $entDef->getField("id");
        $this->assertEquals("id", $field->name);
        $this->assertEquals("number", $field->type);
        $this->assertEquals(true, $field->system);
    }

    /**
     * Test loading data into the definition from an array
     */
    public function testFromArray()
    {
        $entDef = new EntityDefinition("customer");
        
        $data = array(
            "revision" => 10,
            "default_activity_level" => 7,
            "is_private" => true,
            "recur_rules" => array(
                "field_time_start"=>"ts_start",
                "field_time_end"=>"ts_end",
                "field_date_start"=>"ts_start",
                "field_date_end"=>"ts_end",
                "field_recur_id"=>"recurrence_pattern"
            ),
            "inherit_dacl_ref" => "project",
            "parent_field" => "parent",
            "uname_settings" => "parent:name",
            "list_title" => "subject",
            "icon" => "file",
            "fields" => array(
                "subject" => array(
                    "title" => "Subject",
                    "type" => "text",
                ),
            ),
        );

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
    }

    /**
     * Test toArray to make sure data is mapping right
     */
    public function testToArray()
    {
        $entDef = new EntityDefinition("customer");
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
        $definition = new EntityDefinition("customer");
        $definition->setTitle($title);
        $this->assertEquals($title, $definition->getTitle());
    }

    public function testSEtAndGetDacl()
    {
        $dacl = new Dacl();
        $definition = new EntityDefinition("customer");
        $definition->setDacl($dacl);
        $this->assertEquals($dacl, $definition->getDacl());
    }
}

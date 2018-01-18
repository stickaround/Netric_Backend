<?php
/**
 * Define common tests that will need to be run with all data mappers.
 *
 * In order to implement the unit tests, a datamapper test case just needs
 * to extend this class and create a getDataMapper class that returns the
 * datamapper to be tested
 */
namespace NetricTest\EntityDefinition\DataMapper;

use Netric;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\DataMapper\EntityDefinitionDataMapperInterface;
use Netric\Entity\ObjType\UserEntity;
use Netric\Permissions\Dacl;
use PHPUnit\Framework\TestCase;

abstract class DmTestsAbstract extends TestCase
{
    /**
     * Tennant account
     * 
     * @var \Netric\Account\Account
     */
    protected $account = null;

    /**
     * Definitions to cleanup
     *
     * @var EntityDefinition[]
     */
    protected $testDefinitions = [];

    /**
	 * Use this function in all the derived classes to construct the datamapper
	 *
	 * @return EntityDefinitionDataMapperInterface
	 */
	abstract protected function getDataMapper();

    /**
     * Setup each test
     */
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
    }

    /**
     * Cleanup
     */
    protected function tearDown()
    {
        $dm = $this->getDataMapper();
        foreach ($this->testDefinitions as $def) {
            $dm->delete($def);
        }
    }

	/**
	 * Test loading data into the definition from an array
	 */
	public function testFetchByName()
	{
		$dm = $this->getDataMapper();

		$entDef = $dm->fetchByName("customer");

		// Make sure the ID is set
		$this->assertFalse(empty($entDef->id));

		// Make sure revision is not 0 which means uninitialized
		$this->assertTrue($entDef->revision > 0);

		// Field tests
		// ------------------------------------------------
		
		// Verify that we have a name field of type text
		$field = $entDef->getField("name");
		$this->assertEquals("text", $field->type);

		// Test optional values
		$field = $entDef->getField("type_id");
		$this->assertTrue(count($field->optionalValues) > 1);

		// Test fkey_multi
		$field = $entDef->getField("groups");
		$this->assertFalse(empty($field->id));
		$this->assertEquals("parent_id", $field->fkeyTable['parent']);
		$this->assertEquals("fkey_multi", $field->type);
		$this->assertEquals("object_groupings", $field->subtype);
		$this->assertEquals("object_grouping_mem", $field->fkeyTable['ref_table']['table']);
		$this->assertEquals("object_id", $field->fkeyTable['ref_table']['this']);
		$this->assertEquals("grouping_id", $field->fkeyTable['ref_table']['ref']);

		// Test object reference with autocreate
		$field = $entDef->getField("folder_id");
		$this->assertFalse(empty($field->id));
		$this->assertEquals("object", $field->type);
		$this->assertEquals("folder", $field->subtype);
		$this->assertEquals('/System/Customer Files', $field->autocreatebase);
		$this->assertEquals('id', $field->autocreatename);

	}

    /**
     * Make sure we can save definitions
     */
    public function testSave()
    {
        $dataMapper = $this->getDataMapper();

        $def = new EntityDefinition("utest_save");
        $def->setTitle("Unit Test Save");
        $def->setSystem(false);
        $dacl = new Dacl();
        $def->setDacl($dacl);

        // Test inserting with dacl
        $dataMapper->save($def);
        $this->testDefinitions[] = $def;

        // Reload
        $reloadedDef = $dataMapper->fetchByName("utest_save");

        // Were we given an ID?
        $this->assertNotNull($reloadedDef->id);

        // Make sure we got a default field
        $this->assertNotNull($reloadedDef->getField("ts_entered"));
    }

    /**
     * Make sure we can delete a definition
     */
    public function testDelete()
    {
        $dataMapper = $this->getDataMapper();

        $def = new EntityDefinition("utest_delete");
        $def->setTitle("Unit Test Delete");
        $def->setSystem(false);
        $dacl = new Dacl();
        $def->setDacl($dacl);

        // Test inserting with dacl
        $dataMapper->save($def);

        // Delete
        $dataMapper->delete($def);

        // Expect an exception if we try to load this again
        $this->expectException(\RuntimeException::class);

        // Try to reload
        $dataMapper->fetchByName("utest_delete");
    }

    /**
     * Make sure we can delete a definition by name
     */
    public function testDeleteByName()
    {
        $dataMapper = $this->getDataMapper();

        $def = new EntityDefinition("utest_delete_by_name");
        $def->setTitle("Unit Test Delete");
        $def->setSystem(false);
        $dacl = new Dacl();
        $def->setDacl($dacl);

        // Test inserting with dacl
        $dataMapper->save($def);

        // Delete
        $dataMapper->deleteByName('utest_delete_by_name');

        // Expect an exception if we try to load this again
        $this->expectException(\RuntimeException::class);

        // Try to reload
        $dataMapper->fetchByName("utest_delete_by_name");
    }

    /**
     * Make sure the constructed DataMapper can get an account
     */
    public function testGetAccount()
    {
        $dataMapper = $this->getDataMapper();
        $this->assertNotNull($dataMapper->getAccount());
    }

    /**
     * Make sure the DataMapper can get all object types
     */
    public function testGetAllObjectTypes()
    {
        $dataMapper = $this->getDataMapper();
        $this->assertGreaterThan(0, count($dataMapper->getAllObjectTypes()));
    }

    /**
     * Test saving a discretionary access control list (DACL)
     */
	public function testSaveDef_Dacl()
	{
        $dataMapper = $this->getDataMapper();

        $def = new EntityDefinition("utest_save_dacl");
        $def->setTitle("Unit Test Dacl");
        $def->setSystem(false);
        $dacl = new Netric\Permissions\Dacl();
        $def->setDacl($dacl);

        // Test inserting with dacl
        $dataMapper->saveDef($def);
        $this->testDefinitions[] = $def;

        // Reload and check DACL
        $reloadedDef = $dataMapper->fetchByName("utest_save_dacl");
        $this->assertNotNull($reloadedDef->getDacl());

        // Now test updating the dacl
        $daclEdit = $def->getDacl();
        $daclEdit->allowGroup(UserEntity::GROUP_USERS, Dacl::PERM_FULL);
        $id = $dataMapper->saveDef($def);

        // Reload and check DACL
        $reloadedDef = $dataMapper->fetchByName("utest_save_dacl");
        $this->assertNotNull($reloadedDef->getDacl());
        $daclData = $reloadedDef->getDacl()->toArray();
        $this->assertEquals([UserEntity::GROUP_USERS], $daclData['entries'][0]['groups']);
	}

    /**
     * Test unsetting the DACL
     */
	public function testSaveDef_EmptyDacl()
	{
        $dataMapper = $this->getDataMapper();

        $def = new EntityDefinition("utest_save_empty_dacl");
        $def->setTitle("Unit Test Dacl");
        $def->setSystem(false);
        $dacl = new Netric\Permissions\Dacl();
        $def->setDacl($dacl);

        // Test inserting with dacl
        $dataMapper->saveDef($def);
        $this->testDefinitions[] = $def;

        // Reload and check DACL
        $reloadedDef = $dataMapper->fetchByName("utest_save_empty_dacl");
        $this->assertNotNull($reloadedDef->getDacl());

        // Now clear the dacl
        $def->setDacl(null);
        $id = $dataMapper->saveDef($def);

        // Reload
        $reloadedDef = $dataMapper->fetchByName("utest_save_empty_dacl");
        $this->assertNull($reloadedDef->getDacl());
	}

	/**
     * Test getting the latest hash for a system definition
     */
	public function testGetLatestSystemDefinitionHash()
    {
        $this->assertNotNull($this->getDataMapper()->getLatestSystemDefinitionHash('task'));
    }

    /**
     * Make sure non-system definitions do not return a hash
     */
    public function testGetLatestSystemDefinitionHash_Custom()
    {
        $this->assertEmpty($this->getDataMapper()->getLatestSystemDefinitionHash('custom_def'));
    }

    /**
     * Test forcing a system definition to update from system source code
     */
    public function testUpdateSystemDefinition()
    {
        $dataMapper = $this->getDataMapper();

        $taskDefinition = $dataMapper->fetchByName('task');
        $previousRevision = (int)$taskDefinition->revision;

        // Clear out the system hash which will force it to update and increment the revision
        $taskDefinition->systemDefinitionHash = "";
        $dataMapper->updateSystemDefinition($taskDefinition);

        // Make sure we were updated
        $reloadedTaskDefinition = $dataMapper->fetchByName('task');
        $this->assertNotEmpty($reloadedTaskDefinition->systemDefinitionHash);
        $this->assertGreaterThan($previousRevision, $reloadedTaskDefinition->revision);
    }
}

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
use PHPUnit_Framework_TestCase;

abstract class DmTestsAbstract extends PHPUnit_Framework_TestCase 
{
    /**
     * Tennant account
     * 
     * @var \Netric\Account\Account
     */
    protected $account = null;

	/**
	 * Setup each test
	 */
	protected function setUp() 
	{
        $this->account = \NetricTest\Bootstrap::getAccount();
	}

	/**
	 * Use this funciton in all the datamappers to construct the datamapper
	 *
	 * @return EntityDefinition_DataMapperInterface
	 */
	protected function getDataMapper()
	{
		return false;
	}

	/**
	 * Test loading data into the definition from an array
	 */
	public function testFetchByName()
	{
		$dm = $this->getDataMapper();
		if (!$dm)
			return; // skip if no mapper was defined

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
		$this->assertEquals("customer_labels", $field->subtype);
		$this->assertEquals("customer_label_mem", $field->fkeyTable['ref_table']['table']);
		$this->assertEquals("customer_id", $field->fkeyTable['ref_table']['this']);
		$this->assertEquals("label_id", $field->fkeyTable['ref_table']['ref']);

		// Test object reference with autocreate
		$field = $entDef->getField("folder_id");
		$this->assertFalse(empty($field->id));
		$this->assertEquals("object", $field->type);
		$this->assertEquals("folder", $field->subtype);
		$this->assertEquals('/System/Customer Files', $field->autocreatebase);
		$this->assertEquals('id', $field->autocreatename);

	}

	/**
	 * Get groupings
	 */
	public function testGetGroupings()
	{
		$dm = $this->getDataMapper();
		if (!$dm)
			return; // skip if no mapper was defined

		// TODO: needs to be defined
		/*
		$entDef = $dm->fetchByName("customer");

		$groups = $dm->getGroupings($entDef, "groups", array());
		 */
	}
}

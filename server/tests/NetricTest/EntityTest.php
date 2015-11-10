<?php
/**
 * Test entity/object class
 */
namespace NetricTest;

use Netric;
use PHPUnit_Framework_TestCase;

class EntityTest extends PHPUnit_Framework_TestCase 
{
    /**
     * Tennant account
     * 
     * @var \Netric\Account
     */
    private $account = null;
    
    /**
     * Administrative user
     * 
     * @var \Netric\User
     */
    private $user = null;
    

	/**
	 * Setup each test
	 */
	protected function setUp() 
	{
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->user = $this->account->getUser(\Netric\User::USER_ADMINISTRATOR);
	}

	/**
	 * Test default timestamp
	 */
	public function testFieldDefaultTimestamp()
	{
		$cust = $this->account->getServiceManager()->get("EntityLoader")->create("customer");
		$cust->setValue("name", "testFieldDefaultTimestamp");
		$cust->setFieldsDefault('create'); // time_created has a 'now' on 'create' default
		$this->assertTrue(is_numeric($cust->getValue("time_entered")));
	}

	/**
	 * Test default deleted to adjust for some bug with default values resetting f_deleted
	 */
	public function testSetFieldsDefaultBool()
	{
		$cust = $this->account->getServiceManager()->get("EntityLoader")->create("customer");
		$cust->setValue("name", "testFieldDefaultTimestamp");
		$cust->setValue("f_deleted", true);
		$cust->setFieldsDefault('null');
		$this->assertTrue($cust->getValue("f_deleted"));
	}

	/**
	 * Test toArray funciton
	 */
	public function testToArray()
	{
		$cust = $this->account->getServiceManager()->get("EntityLoader")->create("customer");
		$cust->setValue("name", "Entity_DataMapperTests");
		// bool
		$cust->setValue("f_nocall", true);
		// object
		$cust->setValue("owner_id", $this->user->getId(), $this->user->getValue("name"));
		// object_multi
		// fkey  
		// fkey_multi
		// timestamp
		$cust->setValue("last_contacted", time());

		$data = $cust->toArray();
		$this->assertEquals($cust->getValue("name"), $data["name"]);
		$this->assertEquals($cust->getValue("last_contacted"), $data["last_contacted"]);
		$this->assertEquals($cust->getValue("owner_id"), $data["owner_id"]);
		$this->assertEquals($cust->getValueName("owner_id"), $data["owner_id_fval"][$data["owner_id"]]);
		$this->assertEquals($cust->getValue("f_nocall"), $data["f_nocall"]);
	}

	/**
	 * Test loading from an array
	 */
	public function testFromArray()
	{
		$data = array(
			"name" => "testFromArray",
			"last_contacted" => time(),
			"f_nocall" => true,
			"owner_id" => $this->user->getId(),
			"owner_id_fval" => array(
				$this->user->getId() => $this->user->getValue("name")
			),
		);

		// Load data into entity
		$cust = $this->account->getServiceManager()->get("EntityLoader")->create("customer");
		$cust->fromArray($data);

		// Test values
		$this->assertEquals($cust->getValue("name"), $data["name"]);
		$this->assertEquals($cust->getValue("last_contacted"), $data["last_contacted"]);
		$this->assertEquals($cust->getValue("owner_id"), $data["owner_id"]);
		$this->assertEquals($cust->getValueName("owner_id"), $data["owner_id_fval"][$data["owner_id"]]);
		$this->assertEquals($cust->getValue("f_nocall"), $data["f_nocall"]);
	}

	/**
	 * Test processing temp files
	 */
	public function testProcessTempFiles()
	{
		// TODO: set a file field to a temp file and see if it moves
	}
}
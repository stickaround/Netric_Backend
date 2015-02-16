<?php
/**
 * Test entity activity class
 */
namespace NetricTest\Entity;

use Netric\Entity;
use PHPUnit_Framework_TestCase;

class ActivityTest extends PHPUnit_Framework_TestCase 
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
     * Test dynamic factory of entity
     */
    public function testFactory()
    {
        $def = $this->account->getServiceManager()->get("EntityDefinitionLoader")->get("activity");
        $entity = \Netric\Entity::factory($def);
        $this->assertInstanceOf("\Netric\Entity\Activity", $entity);
    }

    public function testOnBeforeSave()
    {
        $def = $this->account->getServiceManager()->get("EntityDefinitionLoader")->get("activity");
        $entity = \Netric\Entity::factory($def);

        // onBeforeSave copies obj_reference to the 'associations' field
        $entity->setValue("obj_reference", "customer:123", "Fake Customer Name");
        $entity->onBeforeSave($this->account->getServiceManager());

        $this->assertEquals("Fake Customer Name", $entity->getValueName("associations", "customer:123"));
    }
}
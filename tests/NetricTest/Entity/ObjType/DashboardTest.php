<?php
/**
 * Test entity dashboard class
 */
namespace NetricTest\Entity\ObjType;

use Netric\Entity;
use Netric\Permissions\DaclLoaderFactory;
use Netric\Entity\ObjType\UserEntity;
use Netric\Permissions\Dacl;
use PHPUnit\Framework\TestCase;

class dashboardTest extends TestCase
{
    /**
     * Tennant account
     * 
     * @var \Netric\Account\Account
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
        $this->user = $this->account->getUser(\Netric\Entity\ObjType\UserEntity::USER_SYSTEM);
	}

    /**
     * Test dynamic factory of entity
     */
    public function testFactory()
    {
        $def = $this->account->getServiceManager()->get("EntityDefinitionLoader")->get("dashboard");
        $entity = $this->account->getServiceManager()->get("EntityFactory")->create("dashboard");
        $this->assertInstanceOf("\\Netric\\Entity\\ObjType\\dashboardEntity", $entity);
    }

    public function testOnBeforeSave()
    {
        $entity = $this->account->getServiceManager()->get("EntityFactory")->create("dashboard");

        // onBeforeSave updates the system-wide dashboard dacl to give permission to everyone
        $entity->setValue("scope", "system");
        $entity->onBeforeSave($this->account->getServiceManager());

        $daclLoader = $this->account->getServiceManager()->get(DaclLoaderFactory::class);
        $dacl = $daclLoader->getForEntity($entity);

        $this->assertTrue($dacl->groupIsAllowed(UserEntity::GROUP_EVERYONE, DACL::PERM_VIEW));
    }
}
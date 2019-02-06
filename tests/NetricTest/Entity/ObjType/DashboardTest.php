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
use NetricTest\Bootstrap;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\DashboardEntity;
use Netric\EntityDefinition\ObjectTypes;

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
    protected function setUp(): void
{
        $this->account = Bootstrap::getAccount();
        $this->user = $this->account->getUser(UserEntity::USER_SYSTEM);
    }

    /**
     * Test dynamic factory of entity
     */
    public function testFactory()
    {
        $def = $this->account->getServiceManager()->get(EntityDefinitionLoader::class)->get(ObjectTypes::DASHBOARD);
        $entity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::DASHBOARD);
        $this->assertInstanceOf(DashboardEntity::class, $entity);
    }

    public function testOnBeforeSave()
    {
        $entity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::DASHBOARD);

        // onBeforeSave updates the system-wide dashboard dacl to give permission to everyone
        $entity->setValue("scope", "system");
        $entity->onBeforeSave($this->account->getServiceManager());

        $daclLoader = $this->account->getServiceManager()->get(DaclLoaderFactory::class);
        $dacl = $daclLoader->getForEntity($entity);

        $this->assertTrue($dacl->groupIsAllowed(UserEntity::GROUP_EVERYONE, DACL::PERM_VIEW));
    }
}

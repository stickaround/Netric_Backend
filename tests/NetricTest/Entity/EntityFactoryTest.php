<?php
/**
 * Test an entity factory
 */
namespace NetricTest\Entity;

use Netric\Entity;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Entity\EntityFactoryFactory;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;

class EntityFactoryTest extends TestCase
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
     * @var \Netric\Entity\EntityFactory
     */
    private $entityFactory = null;


    /**
     * Setup each test
     */
    protected function setUp()
    {
        $this->account = Bootstrap::getAccount();
        $sm = $this->account->getServiceManager();
        $this->entityFactory = $sm->get(EntityFactoryFactory::class);
    }

    /**
     * Make sure we can get an extended object type
     */
    public function testCreateUser()
    {
        $user = $this->entityFactory->create(ObjectTypes::USER);
        $this->assertInstanceOf(UserEntity::class, $user);
    }
}

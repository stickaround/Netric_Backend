<?php
/**
 * Test entity task class
 */
namespace NetricTest\Entity\ObjType;

use Netric\Entity;
use Netric\Entity\ObjType\UserEntity;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\TaskEntity;
use Netric\EntityDefinition\ObjectTypes;

class TaskTest extends TestCase
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
        $def = $this->account->getServiceManager()->get(EntityDefinitionLoaderFactory::class)->get(ObjectTypes::TASK);
        $entity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::TASK);
        $this->assertInstanceOf(TaskEntity::class, $entity);
    }
}

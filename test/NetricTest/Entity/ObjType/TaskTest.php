<?php
/**
 * Test entity task class
 */
namespace NetricTest\Entity\ObjType;

use Netric\Entity\ObjType\UserEntity;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\TaskEntity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\ServiceManager\AccountServiceManagerInterface;

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
        $entity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::TASK);
        $this->assertInstanceOf(TaskEntity::class, $entity);
    }

    public function testOnBeforeSaveFlagsDoneWhenCompleted()
    {
        $task = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::TASK);
        $task->setValue('status_id', 1, TaskEntity::STATUS_COMPLETED);
        $mockServiceManager = $this->getMockBuilder(AccountServiceManagerInterface::class)->getMock();
        $task->onBeforeSave($mockServiceManager);
        $this->assertTrue($task->getValue('done'));
    }
}

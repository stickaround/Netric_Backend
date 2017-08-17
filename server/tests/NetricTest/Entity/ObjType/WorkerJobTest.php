<?php
namespace NetricTest\Entity\ObjType;

use PHPUnit\Framework\TestCase;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\ObjType\ActivityEntity;
use Netric\Account\Account;

class WorkerJobTest extends TestCase
{
    /**
     * Tenant account
     *
     * @var Account
     */
    private $account = null;

    /**
     * System user
     *
     * @var UserEntity
     */
    private $user = null;


    /**
     * Setup each test
     */
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->user = $this->account->getUser(UserEntity::USER_SYSTEM);
    }

    /**
     * Test dynamic factory of entity
     */
    public function testFactory()
    {
        $def = $this->account->getServiceManager()->get("EntityDefinitionLoader")->get("worker_job");
        $entity = $this->account->getServiceManager()->get("EntityFactory")->create("worker_job");
        $this->assertInstanceOf(ActivityEntity::class, $entity);
    }

    /**
     * This entity will execute a worker before save if ts_execute
     * is earlier than now and is null
     */
    public function testOnBeforeSave()
    {
        $entity = $this->account->getServiceManager()->get("EntityFactory")->create("worker_job");

        //$entity->onBeforeSave($this->account->getServiceManager());

        //$this->assertEquals("Fake Customer Name", $entity->getValueName("associations", "customer:123"));
        $this->markTestSkipped("TODO");
    }
}
<?php

namespace NetricTest\Entity\ObjType;

use PHPUnit\Framework\TestCase;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\ObjType\WorkerJobEntity;
use Netric\Account\Account;
use NetricTest\Bootstrap;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;

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
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
        $this->user = $this->account->getUser(null, UserEntity::USER_SYSTEM);
    }

    /**
     * Test dynamic factory of entity
     */
    public function testFactory()
    {
        $def = $this->account->getServiceManager()->get(EntityDefinitionLoaderFactory::class)->get(ObjectTypes::WORKER_JOB);
        $entity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::WORKER_JOB);
        $this->assertInstanceOf(WorkerJobEntity::class, $entity);
    }
}

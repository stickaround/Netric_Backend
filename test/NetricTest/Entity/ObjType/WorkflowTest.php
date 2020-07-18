<?php

/**
 * Test workflow entity
 */

namespace NetricTest\Entity\ObjType;

use Netric\Entity;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\WorkflowEntity;
use Netric\EntityDefinition\ObjectTypes;

class WorkflowTest extends TestCase
{
    /**
     * Tenant account
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
        $this->user = $this->account->getUser(null, UserEntity::USER_SYSTEM);
    }

    /**
     * Test factory
     */
    public function testFactory()
    {
        $entity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::WORKFLOW);
        $this->assertInstanceOf(WorkflowEntity::class, $entity);
    }
}

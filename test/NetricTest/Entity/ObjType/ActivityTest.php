<?php

/**
 * Test entity activity class
 */

namespace NetricTest\Entity\ObjType;

use Netric\Entity;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\ObjType\ActivityEntity;

class ActivityTest extends TestCase
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
        $this->user = $this->account->getUser(null, UserEntity::USER_SYSTEM);
    }

    /**
     * Test dynamic factory of entity
     */
    public function testFactory()
    {
        $def = $this->account->getServiceManager()->get(EntityDefinitionLoaderFactory::class)->get(ObjectTypes::ACTIVITY);
        $entity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::ACTIVITY, $this->account->getAccountId());
        $this->assertInstanceOf(ActivityEntity::class, $entity);
    }

    public function testOnBeforeSave()
    {
        $entity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::ACTIVITY, $this->account->getAccountId());

        // onBeforeSave copies obj_reference to the 'associations' field
        $entity->setValue("subject", "user:123", "Fake User");
        $entity->setValue("verb", 'create');
        $entity->setValue("obj_reference", "customer:123", "Fake Customer Name");
        $entity->onBeforeSave($this->account->getServiceManager());

        $this->assertEquals("Fake Customer Name", $entity->getValueName("associations", "customer:123"));
    }
}

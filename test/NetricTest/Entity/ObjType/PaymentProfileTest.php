<?php

namespace NetricTest\Entity\ObjType;

use PHPUnit\Framework\TestCase;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\PaymentProfileEntity;
use NetricTest\Bootstrap;
use Netric\EntityDefinition\ObjectTypes;

class PaymentProfileTest extends TestCase
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
        $entity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::SALES_PAYMENT_PROFILE, $this->account->getAccountId());
        $this->assertInstanceOf(PaymentProfileEntity::class, $entity);
    }
}

<?php
namespace NetricTest\Entity\ObjType;

use PHPUnit\Framework\TestCase;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\EntityFactoryFactory;
use Netric\Entity\ObjType\PaymentProfileEntity;

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
        $entity = $this->account->getServiceManager()->get(EntityFactoryFactory::class)->create("payment_profile");
        $this->assertInstanceOf(PaymentProfileEntity::class, $entity);
    }
}

<?php
/**
 * Test entity email class
 */
namespace NetricTest\Entity\ObjType;

use Netric\Entity;
use PHPUnit_Framework_TestCase;

class EmailMessageTest extends PHPUnit_Framework_TestCase
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
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->user = $this->account->getUser(\Netric\Entity\ObjType\UserEntity::USER_ADMINISTRATOR);
    }

    /**
     * Test dynamic factory of entity
     */
    public function testFactory()
    {
        $entity = $this->account->getServiceManager()->get("EntityFactory")->create("email_message");
        $this->assertInstanceOf("\\Netric\\Entity\\ObjType\\EmailMessageEntity", $entity);
    }

    /**
     * Make sure we can parse , or ; separated message lists
     */
    public function testGetAddressListFromString()
    {
        $method = new \ReflectionMethod('Netric\Entity\ObjType\EmailMessageEntity', 'getAddressListFromString');
        $method->setAccessible(true);

        $entityFactory = $this->account->getServiceManager()->get("EntityFactory");
        $emailEntity = $entityFactory->create("email_message");
        $addresses = "\"Test\" <test@test.com>, test@test2.com";
        $addressList = $method->invoke($emailEntity, $addresses);
        $this->assertEquals(2, $addressList->count());

        $addresses2 = "\"Test\" <test@test.com>;, test@test2.com";
        $addressList = $method->invoke($emailEntity, $addresses2);
        $this->assertEquals(2, $addressList->count());
    }
}
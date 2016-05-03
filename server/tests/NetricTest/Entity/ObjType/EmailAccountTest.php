<?php
/**
 * Test EmailAccount entity
 */
namespace NetricTest\Entity\ObjType;

use Netric\Entity;
use PHPUnit_Framework_TestCase;

class EmailAccountEntityTest extends PHPUnit_Framework_TestCase
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
     * Test factory
     */
    public function testFactory()
    {
        $entity = $this->account->getServiceManager()->get("EntityFactory")->create("email_account");
        $this->assertInstanceOf("\\Netric\\Entity\\ObjType\\EmailAccountEntity", $entity);
    }
}

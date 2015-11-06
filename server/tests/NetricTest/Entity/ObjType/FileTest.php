<?php
/**
 * Test entity activity class
 */
namespace NetricTest\Entity\ObjType;

use Netric\Entity;
use PHPUnit_Framework_TestCase;

class FileTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tennant account
     *
     * @var \Netric\Account
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
        $this->user = $this->account->getUser(\Netric\User::USER_ADMINISTRATOR);
    }

    /**
     * Test dynamic factory of entity
     */
    public function testFactory()
    {
        $entity = $this->account->getServiceManager()->get("EntityFactory")->create("file");
        $this->assertInstanceOf('\Netric\Entity\ObjType\File', $entity);
    }
}
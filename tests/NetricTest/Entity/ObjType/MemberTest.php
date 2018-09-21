<?php
/**
 * Test entity member class
 */
namespace NetricTest\Entity\ObjType;

use Netric\Entity;
use Netric\Entity\ObjType\UserEntity;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\MemberEntity;
use Netric\EntityDefinition\ObjectTypes;

class MemberTest extends TestCase
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
    protected function setUp()
    {
        $this->account = Bootstrap::getAccount();
        $this->user = $this->account->getUser(UserEntity::USER_SYSTEM);
    }

    /**
     * Test dynamic factory of entity
     */
    public function testFactory()
    {
        $def = $this->account->getServiceManager()->get(EntityDefinitionLoader::class)->get(ObjectTypes::MEMBER);
        $entity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::MEMBER);
        $this->assertInstanceOf(MemberEntity::class, $entity);
    }
}

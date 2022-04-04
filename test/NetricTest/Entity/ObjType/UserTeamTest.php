<?php

/**
 * Test entity activity class
 */

namespace NetricTest\Entity\ObjType;

use Netric\Entity;
use Netric\EntityQuery\EntityQuery;
use Netric\EntityQuery\Index\IndexFactory;
use PHPUnit\Framework\TestCase;
use Netric\Entity\ObjType\UserTeamEntity;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;
use NetricTest\Bootstrap;

class UserTeamTest extends TestCase
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
        $entity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::USER_TEAM, $this->account->getAccountId());
        $this->assertInstanceOf(UserTeamEntity::class, $entity);
    }
}

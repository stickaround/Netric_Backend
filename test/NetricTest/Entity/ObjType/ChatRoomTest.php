<?php

/**
 * Test entity activity class
 */

namespace NetricTest\Entity\ObjType;

use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\ObjType\ChatRoomEntity;

class ChatRoomTest extends TestCase
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
        $entity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::CHAT_ROOM, $this->account->getAccountId());
        $this->assertInstanceOf(ChatRoomEntity::class, $entity);
    }
}

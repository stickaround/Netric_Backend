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
use Netric\Permissions\Dacl;

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

    /**
     * Chat rooms have compliacted permissions that have to be set
     *
     * @return void
     */
    public function testOnBeforeSave(): void
    {
        // Create a chat room and add someone to it
        $chatRoom = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::CHAT_ROOM, $this->account->getAccountId());
        $secondUserId = "9a29e9c0-5965-46c7-8f91-53e9b7e87cb6";
        $chatRoom->addMultiValue('members', $secondUserId, 'Test');

        // Call on before save and make sure that the second user has permission
        $chatRoom->onBeforeSave($this->account->getServiceManager(), $this->user);
        $daclData = json_decode($chatRoom->getValue('dacl'), true);
        $dacl = new Dacl($daclData);
        $testUser = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::USER, $this->account->getAccountId());
        $testUser->setValue('entity_id', $secondUserId);
        $this->assertTrue($dacl->isAllowed($testUser, Dacl::PERM_VIEW));
    }
}

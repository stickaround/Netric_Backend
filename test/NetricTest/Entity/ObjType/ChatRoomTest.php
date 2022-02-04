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

        // Owner of the chat room should also in the members
        $this->assertEquals($chatRoom->getValue("members")[1], $this->user->getEntityId());
    }

    /**
     * Test getting the applied name for chat room entity
     */
    public function testOnGetAppliedName()
    {
        $dataChannelRoom = [
            "name" => "testAppliedName",
            "subject" => "Room Channel",
            "scope" => ChatRoomEntity::ROOM_CHANNEL,
            "members" => []
        ];

        // Load room channel data into entity
        $entityChannelRoom = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::CHAT_ROOM, $this->account->getAccountId());
        $entityChannelRoom->fromArray($dataChannelRoom);
        $entityChannelRoom->addMultiValue("members", $this->user->getEntityId(), $this->user->getName());

        $this->assertEquals($entityChannelRoom->getAppliedName($this->user), $dataChannelRoom["subject"]);

        // Now add members in the channel room and set the subject to empty string
        $entityChannelRoom->setValue("subject", "");        
        $entityChannelRoom->addMultiValue("members", "member-01", "Member 01");
        $entityChannelRoom->addMultiValue("members", "member-02", "Member 02");
        $this->assertEquals($entityChannelRoom->getAppliedName($this->user), "Member 01, Member 02");

        // Now let's test the direct message
        $dataDirectMessage = [
            "name" => "testAppliedName",
            "subject" => "Room Direct",
            "scope" => ChatRoomEntity::ROOM_DIRECT,
            "members" => []
        ];
        $entityDirectMessage = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::CHAT_ROOM, $this->account->getAccountId());
        $entityDirectMessage->fromArray($dataDirectMessage);
        $entityDirectMessage->addMultiValue("members", $this->user->getEntityId(), $this->user->getName());

        $this->assertEquals($entityDirectMessage->getAppliedName($this->user), "<Empty Room>");

        // Now add other members in the direct message
        $entityDirectMessage->addMultiValue("members", "member-00", "Member 00");
        $this->assertEquals($entityDirectMessage->getAppliedName($this->user), "Member 00");
    }
}

<?php

declare(strict_types=1);

namespace NetricTest\Handler;

use Netric\Account\AccountContainer;
use PHPUnit\Framework\TestCase;
use Netric\Entity\Entity;
use Netric\Entity\EntityLoader;
use Netric\Entity\Notifier\Notifier;
use Netric\Entity\ObjType\ActivityEntity;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\Field;
use Netric\EntityGroupings\GroupingLoader;
use Netric\Handler\ChatHandler;

/**
 * @group integration
 */
class ChatHandlerTest extends TestCase
{
    /**
     * Initialized Handler with mock dependencies
     */
    private ChatHandler $chatHandler;

    /**
     * Dependency mocks
     */
    private EntityLoader $mockEntityLoader;
    private Notifier $mockNotifier;

    protected function setUp(): void
    {
        $this->mockEntityLoader = $this->createMock(EntityLoader::class);
        $this->mockNotifier = $this->createMock(Notifier::class);

        // Create the handler with mocks
        $this->chatHandler = new ChatHandler(
            $this->mockEntityLoader,
            $this->mockNotifier
        );
    }

    /**
     * Test the checking in of the user
     */
    public function testNotifyAbsentOfNewMessage()
    {
        // Create some test UUIDs
        $messageId = 'fe682cf2-a31b-4d0e-93d0-f87c7aa01dd4';
        $roomId = '717fae77-1ee3-4676-95d6-a1cac0051f89';
        $userOneId = '9e90f619-94f6-4f4b-82c0-3aeba561222c';
        $userTwoId = '6e5f2198-fa64-4af4-98da-5070584d41bf';
        $accountId = '9aaff4c1-ec2b-4513-b82f-7b4ce9c2241c';

        // Setup a room entity with both users as a member
        $roomDefinition = new EntityDefinition('chat_room', $accountId);
        $field = new Field('members');
        $field->type = Field::TYPE_OBJECT_MULTI;
        $roomDefinition->addField($field);
        $room = new Entity($roomDefinition, $this->mockEntityLoader);
        $room->addMultiValue('members', $userOneId, 'user1');
        $room->addMultiValue('members', $userTwoId, 'user2');

        // Setup a message entity that was only seen by user 1
        $messageDefinition = new EntityDefinition('chat_message', $accountId);
        $seenByField = new Field('seen_by');
        $seenByField->type = Field::TYPE_OBJECT_MULTI;
        $messageDefinition->addField($seenByField);
        $chatRoomField = new Field('chat_room');
        $chatRoomField->type = Field::TYPE_OBJECT;
        $messageDefinition->addField($chatRoomField);
        $bodyField = new Field('body');
        $bodyField->type = Field::TYPE_TEXT;
        $messageDefinition->addField($bodyField);
        $message = new Entity($messageDefinition, $this->mockEntityLoader);
        $message->addMultiValue('seen_by', $userOneId, 'user1');
        $message->setValue('chat_room', $roomId);
        $message->setValue('body', 'My Test Message');

        // Setup user entity for user 2
        $userDefinition = new EntityDefinition('user', $accountId);
        $nameField = new Field('name');
        $nameField->type = Field::TYPE_TEXT;
        $userDefinition->addField($nameField);
        $userTwo = new UserEntity(
            $userDefinition,
            $this->mockEntityLoader,
            $this->createMock(GroupingLoader::class),
            $this->createMock(AccountContainer::class)
        );
        $userTwo->setValue('name', 'user2');

        // Mock getEntityById for task and user, the last param in the map is the return value
        $this->mockEntityLoader->method('getEntityById')->will($this->returnValueMap([
            [$messageId, $accountId, $message],
            [$roomId, $accountId, $room],
            [$userTwoId, $accountId, $userTwo],
        ]));

        // Mock notifier send
        $this->mockNotifier->expects($this->once())->method('send')->with(
            $this->equalTo($room),
            $this->equalTo(ActivityEntity::VERB_SENT),
            $this->equalTo($userTwo),
            $this->equalTo($message->getValue('body')),
        );

        // Call the handler
        $this->chatHandler->notifyAbsentOfNewMessage($messageId, $accountId);
    }
}

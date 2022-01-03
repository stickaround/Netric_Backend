<?php

declare(strict_types=1);

namespace Netric\Handler;

use Netric\Entity\EntityEvents;
use Netric\Entity\EntityLoader;
use Netric\Entity\Notifier\Notifier;
use Netric\Entity\ObjType\ActivityEntity;
use NetricApi\ChatIf;
use NetricApi\InvalidArgument;

class ChatHandler implements ChatIf
{
    /**
     * Loader used for getting and saving an entity
     */
    private EntityLoader $entityLoader;

    /**
     * Notifier used to send notifications
     */
    private Notifier $notifier;

    /**
     * Handler constructor
     *
     * @param EntityLoader $entityLoader
     */
    public function __construct(EntityLoader $entityLoader, Notifier $notifier)
    {
        $this->entityLoader = $entityLoader;
        $this->notifier = $notifier;
    }

    /**
     * Send a notifiation to any users that are a member of a chat room but did not see the message
     *
     * @param string $messageId
     * @param string $accountId
     * @return void
     */
    public function notifyAbsentOfNewMessage($messageId, $accountId)
    {
        $chatMessage = $this->entityLoader->getEntityById($messageId, $accountId);
        if (!$chatMessage) {
            throw new InvalidArgument(
                "ChatHandler->notifyAbsentOfNewMessage: " .
                    "Chat message - $messageId - not found"
            );
        }

        $chatRoomId = $chatMessage->getValue('chat_room');
        if (!$chatRoomId) {
            throw new InvalidArgument(
                "ChatHandler->notifyAbsentOfNewMessage: " .
                    "Chat room - $chatRoomId - not found"
            );
        }

        $chatRoom = $this->entityLoader->getEntityById($chatRoomId, $accountId);
        $members = $chatRoom->getValue('members');
        foreach ($members as $userId) {
            if (!in_array($userId, $chatMessage->getValue('seen_by'))) {
                // Send a notice to the user in question
                $user = $this->entityLoader->getEntityById($userId, $accountId);
                $this->notifier->send(
                    $chatRoom,
                    EntityEvents::EVENT_CREATE,
                    $user,
                    $chatMessage->getValue('body')
                );
            }
        }
    }
}

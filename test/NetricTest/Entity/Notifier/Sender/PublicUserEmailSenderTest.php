<?php

declare(strict_types=1);

/**
 * Test the NotifierFactory factory
 */

namespace NetricTest\Entity\Notifier\Sender;

use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\Notifier\Sender\PublicUserEmailSender;
use Netric\Entity\ObjType\NotificationEntity;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Mail\MailSystemInterface;
use Netric\Mail\SenderService;
use PHPUnit\Framework\TestCase;

class PublicUserEmailSenderTest extends TestCase
{
    const TEST_ACCOUNT_ID = "86fb5a63-7c32-4b00-b94c-7089d9d5556c";

    /**
     * In production usually default domains will be [account].netric.com
     */
    const TEST_DEFAULT_DOMAIN = 'test.example.com';

    private EntityLoader $mockEntityLoader;
    private SenderService $mockMailSender;
    private MailSystemInterface $mockMailSystem;
    private PublicUserEmailSender $sender;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockEntityLoader = $this->createMock(EntityLoader::class);
        $this->mockMailSender = $this->createMock(SenderService::class);
        $this->mockMailSystem = $this->createMock(MailSystemInterface::class);
        $this->mockMailSystem->method('getDefaultDomain')->willReturn(self::TEST_DEFAULT_DOMAIN);

        $this->sender = new PublicUserEmailSender(
            $this->mockEntityLoader,
            $this->mockMailSender,
            $this->mockMailSystem
        );
    }

    /**
     * Test happy path sending
     *
     * @return void
     */
    public function testSendNotification(): void
    {
        // Setup some test IDs and values
        $targetUserId = "400364fe-dfc6-403d-b808-8c1fe3174d91";
        $targetUserEmail = "user@example.com";
        $ticketId = "a2118268-8b70-461b-9240-60ba79c5ce16";
        $channelId = "5ab31f05-d74f-493b-8461-c85cc0898ee7";
        $emailAccountId = "99ab9af8-101c-4f0b-b7e9-81a4d62e2de4";
        $notificationId = "8329498b-d4c4-4c7d-a539-d17ad7c581c0";

        // Create mock notification
        $notification = $this->createMock(NotificationEntity::class);
        $notification->method('getEntityId')->willReturn($notificationId);
        $notification->method('getValue')->will(
            $this->returnValueMap(
                [
                    ['owner_id', $targetUserId],
                    ['obj_reference', $ticketId],
                    ['description', 'Comment Body'],
                    ['name', 'Added comment']
                ]
            )
        );

        // Create a mock user we are directing this email at
        $mockTargetUser = $this->createStub(UserEntity::class);
        $mockTargetUser->method('getValue')->will(
            $this->returnValueMap(
                [
                    ['email', $targetUserEmail],
                    ['full_name', 'Test Customer'],
                ]
            )
        );

        // Create a mock ticket channel that the ticket belongs to
        $mockChannel = $this->createMock(EntityInterface::class);
        $mockChannel->method('getValue')->with('email_account_id')->willReturn($emailAccountId);

        // Create a mock ticket that would have been commented on to create the trigger
        $mockTicket = $this->createMock(EntityInterface::class);
        $mockTicket->method('getEntityId')->willReturn($ticketId);
        $mockTicket->method('getObjType')->willReturn(ObjectTypes::TICKET);
        $mockTicket->method('getValue')->with('channel_id')->willReturn($channelId);

        // Mock email address that routes emails to the ticket channel
        $mockEmailAccount = $this->createMock(EntityInterface::class);
        $mockEmailAccount->method('getValue')->will(
            $this->returnValueMap(
                [
                    ['address', 'support@netric.com'],
                    ['name', 'Support Dropbox'],
                ]
            )
        );

        // Return all the referenced mock entities
        $this->mockEntityLoader->method('getEntityById')->withConsecutive(
            // First call gets the target user
            [$targetUserId, self::TEST_ACCOUNT_ID],
            // Second call gets the obj_refrence
            [$ticketId, self::TEST_ACCOUNT_ID],
            // Third call gets the channel for the ticket
            [$channelId, self::TEST_ACCOUNT_ID],
            // Fourth call gets the email account for the channel
            [$emailAccountId, self::TEST_ACCOUNT_ID],
        )->willReturnOnConsecutiveCalls(
            $mockTargetUser,
            $mockTicket,
            $mockChannel,
            $mockEmailAccount
        );

        $sendingUser = $this->createStub(UserEntity::class);
        $sendingUser->method('getAccountId')->willReturn(self::TEST_ACCOUNT_ID);

        // Make sure that send is called with the right params
        $this->mockMailSender->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo($targetUserEmail),
                $this->equalTo('Test Customer'),
                $this->equalTo('support@netric.com'),
                $this->equalTo('Support Dropbox'),
                $this->equalTo('Added comment'),
                $this->equalTo('Comment Body'),
                $this->equalTo(['message-id' => $ticketId . "." . $notificationId . "@" . self::TEST_DEFAULT_DOMAIN])
            )->willReturn(true);

        $this->assertTrue($this->sender->sendNotification($notification, $sendingUser));
    }
}

<?php

/**
 * Test the notifier class
 */

namespace NetricTest\Entity\Notifier;

use Netric\EntityQuery\EntityQuery;
use Netric\EntityQuery\Index\IndexInterface;
use PHPUnit\Framework\TestCase;
use Netric\Entity\Notifier\Notifier;
use Netric\Entity\EntityLoader;
use Netric\Entity\EntityLoaderFactory;
use Netric\Account\Account;
use Netric\Entity\ObjType\ActivityEntity;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\EntityInterface;
use Netric\Entity\Notifier\Sender\PublicUserEmailSender;
use NetricTest\Bootstrap;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityQuery\Index\IndexFactory;
use NotificationPusherSdk\NotificationPusherClientInterface;

/**
 * Test the notifier service
 */
class NotifierTest extends TestCase
{
    /**
     * Tenant account
     *
     * @var Account
     */
    private $account = null;

    /**
     * Administrative user
     *
     * @var \Netric\User
     */
    private $user = null;

    /**
     * EntityLoader
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * List of test entities to cleanup
     *
     * @var EntityInterface[]
     */
    private $testEntities = [];

    /**
     * Notifier to test
     *
     * @var Notifier
     */
    private $notifier = null;

    /**
     * Test user to notify
     *
     * @var UserEntity
     */
    private $testUser = null;

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
        $this->entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $index = $this->account->getServiceManager()->get(IndexFactory::class);

        //$entityLoaderMock = $this->createMock(EntityLoader::class);
        //$indexMock = $this->createMock(IndexInterface::class);
        $pusherClientMock = $this->createMock(NotificationPusherClientInterface::class);
        $publicEmailSenderMock = $this->createMock(PublicUserEmailSender::class);
        $this->notifier = new Notifier($this->entityLoader, $index, $pusherClientMock, $publicEmailSenderMock);


        // Make sure test user does not exist from previous failed query

        $query = new EntityQuery(ObjectTypes::USER, $this->account->getAccountId());
        $query->where("name")->equals("notifiertest");
        $result = $index->executeQuery($query);
        for ($i = 0; $i < $result->getNum(); $i++) {
            $this->entityLoader->delete($result->getEntity($i), $this->account->getAuthenticatedUser());
        }

        // Create a test user to assign a task and notification to
        $this->testUser = $this->entityLoader->create(ObjectTypes::USER, $this->account->getAccountId());
        $this->testUser->setValue("name", "notifiertest");
        $this->entityLoader->save($this->testUser, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $this->testUser;
    }

    /**
     * Cleanup after each test
     */
    protected function tearDown(): void
    {
        // Make sure any test entities created are deleted
        foreach ($this->testEntities as $entity) {
            // Second param is a 'hard' delete which actually purges the data
            $this->entityLoader->delete($entity, $this->account->getAuthenticatedUser());
        }
    }

    /**
     * Test creating new notifications and sending them to followers of an entity
     */
    public function testSend()
    {
        // Create a test task entity and assign it to $this->testUser
        $task = $this->entityLoader->create(ObjectTypes::TASK, $this->account->getAccountId());
        $task->setValue("owner_id", $this->testUser->getEntityId());
        $task->setValue("name", "test task");
        $this->entityLoader->save($task, $this->account->getSystemUser());
        $this->testEntities[] = $task;

        // Saving created notices automatically, mark them all as read for the test
        $this->notifier->markNotificationsSeen($task, $this->testUser);

        // Update task value so we can re-create notifications
        $task->setValue("name", "updated task name");

        // Now re-create notifications
        $notificationIds = $this->notifier->send(
            $task,
            ActivityEntity::VERB_CREATED,
            $this->account->getAuthenticatedUser()
        );

        // Exactly one notification should have been created for the test user
        $this->assertEquals(1, count($notificationIds));

        // Check that the test notification has the right values
        $notification = $this->entityLoader->getEntityById($notificationIds[0], $this->account->getAccountId());
        $this->testEntities[] = $notification;

        // Make sure we created a notice for the test user
        $this->assertEquals($this->testUser->getEntityId(), $notification->getValue("owner_id"));

        // Test private getNameFromEventVerb
        $this->assertEquals("Added Task", $notification->getValue("name"));

        /*
         * Test private getNotification by re-creating entities,
         * this should just reuse the unseen notices created above.
         */
        $newNotificationIds = $this->notifier->send(
            $task,
            ActivityEntity::VERB_CREATED,
            $this->account->getAuthenticatedUser()
        );
        $this->assertEquals($notificationIds, $newNotificationIds);
    }

    /**
     * Test the creating of comment and check if notification entity has the description of the comment
     */
    public function testCreateComment()
    {
        // Create a test task entity and assign it to $this->testUser
        $task = $this->entityLoader->create(ObjectTypes::TASK, $this->account->getAccountId());
        $task->setValue("owner_id", $this->testUser->getEntityId());
        $task->setValue("name", "test task");
        $this->entityLoader->save($task, $this->account->getSystemUser());
        $this->testEntities[] = $task;

        // Saving created notices automatically, mark them all as read for the test
        $this->notifier->markNotificationsSeen($task, $this->testUser);

        // Create a test user that will create the notification
        $user = $this->entityLoader->create(ObjectTypes::USER, $this->account->getAccountId());
        $user->setValue("name", "Comment User");
        $this->entityLoader->save($user, $this->account->getSystemUser());
        $this->testEntities[] = $user;

        // Create a test comment entity and set its object reference to the test task
        $comment = $this->entityLoader->create(ObjectTypes::COMMENT, $this->account->getAccountId());
        $comment->setValue("comment", "Test Comment");
        $comment->setValue("obj_reference", $task->getEntityId());
        $comment->setValue("owner_id", $user->getEntityId());
        $this->entityLoader->save($comment, $this->account->getSystemUser());
        $this->testEntities[] = $comment;

        // Now re-create notifications
        $notificationIds = $this->notifier->send($comment, ActivityEntity::VERB_CREATED, $this->account->getAuthenticatedUser());

        // Exactly two notification should have been created for the test user. One if for creating the comment, second is for updating the task.
        $this->assertEquals(2, count($notificationIds));

        // Check that the test notification has the right values
        $notification = $this->entityLoader->getEntityById($notificationIds[0], $this->account->getAccountId());
        $this->testEntities[] = $notification;

        // Make sure that the notification included the comment in the description
        $this->assertEquals($notification->getValue("name"), $user->getName() . " added comment");
        $this->assertEquals($notification->getValue("description"), $comment->getValue("comment"));
    }

    /**
     * Test the creating of comment and check if notification entity has the description of the comment
     */
    public function testUserCallout()
    {
        // Create a test task entity and assign it to $this->testUser
        $task = $this->entityLoader->create(ObjectTypes::TASK, $this->account->getAccountId());
        $task->setValue("owner_id", $this->testUser->getEntityId());
        $task->setValue("name", "test task");
        $this->entityLoader->save($task, $this->account->getSystemUser());
        $this->testEntities[] = $task;

        // Update task value so we can re-create notifications
        $task->setValue("name", "updated task name");

        // Saving created notices automatically, mark them all as read for the test
        $this->notifier->markNotificationsSeen($task, $this->testUser);

        // Create a test user that will create the notification
        $user = $this->entityLoader->create(ObjectTypes::USER, $this->account->getAccountId());
        $user->setValue("name", "Comment User");
        $this->entityLoader->save($user, $this->account->getSystemUser());
        $this->testEntities[] = $user;

        // Create a test user that will will be called out
        $userCallout = $this->entityLoader->create(ObjectTypes::USER, $this->account->getAccountId());
        $userCallout->setValue("name", "calledoutUser");
        $this->entityLoader->save($userCallout, $this->account->getSystemUser());
        $this->testEntities[] = $userCallout;

        // Create a test comment entity and set its object reference to the test task
        $comment = $this->entityLoader->create(ObjectTypes::COMMENT, $this->account->getAccountId());
        $comment->setValue("comment", "@calledoutUser Check this comment.");
        $comment->setValue("obj_reference", $task->getEntityId());
        $comment->setValue("owner_id", $user->getEntityId());
        $comment->setValue("followers", [$userCallout->getEntityId()]);
        $this->entityLoader->save($comment, $this->account->getSystemUser());
        $this->testEntities[] = $comment;

        // Now re-create notifications
        $notificationIds = $this->notifier->send($comment, ActivityEntity::VERB_CREATED, $this->account->getAuthenticatedUser());

        /**
         * Exactly three notification should have been created for the test user.
         * One if for creating the comment.
         * Second is for updating the task.
         * Third is the user being called out.
         */
        $this->assertEquals(3, count($notificationIds));

        // Check that the test notification has the right values
        $notification = $this->entityLoader->getEntityById($notificationIds[0], $this->account->getAccountId());
        $this->testEntities[] = $notification;

        // Make sure that the notification included the comment in the description
        $this->assertEquals($notification->getValue("name"), $user->getName() . " directed a comment at you");
        $this->assertEquals($notification->getValue("description"), $comment->getValue("comment"));
    }

    /**
     * Make sure we can mark all unseen notifications as ween
     */
    public function testMarkNotificationsSeen()
    {
        // Index for querying entities
        $entityIndex = $this->account->getServiceManager()->get(IndexFactory::class);

        // Create a test task entity and assign it to $this->testUser
        $task = $this->entityLoader->create(ObjectTypes::TASK, $this->account->getAccountId());
        $task->setValue("owner_id", $this->testUser->getEntityId());
        $task->setValue("name", "test task");
        $this->entityLoader->save($task, $this->account->getSystemUser());
        $this->testEntities[] = $task;

        // Update task value so we can re-create notifications
        $task->setValue("name", "updated task name");

        // Now re-create notifications
        $this->notifier->send($task, ActivityEntity::VERB_CREATED, $this->account->getAuthenticatedUser());

        // Query to make sure we have an unseen notification for the test user
        $query = new EntityQuery(ObjectTypes::NOTIFICATION, $this->account->getAccountId());
        $query->where("owner_id")->equals($this->testUser->getEntityId());
        $query->andWhere("obj_reference")->equals($task->getEntityId());
        $query->andWhere("f_seen")->equals(false);
        $result = $entityIndex->executeQuery($query);
        $this->assertEquals(1, $result->getNum());

        // Mark them all as seen for the test user
        $this->notifier->markNotificationsSeen($task, $this->testUser);

        // Query to make sure no unseen entities exist for the current user
        $query = new EntityQuery(ObjectTypes::NOTIFICATION, $this->account->getAccountId());
        $query->where("owner_id")->equals($this->testUser->getEntityId());
        $query->andWhere("obj_reference")->equals("task:" . $task->getEntityId());
        $query->andWhere("f_seen")->equals(false);
        $result = $entityIndex->executeQuery($query);

        // Make sure none were found
        $this->assertEquals(0, $result->getNum());
    }

    /**
     * Check that we can subscribe a user to the notificationpusher
     */
    public function testSubscribeToPush(): void
    {
        $entityLoaderMock = $this->createMock(EntityLoader::class);
        $indexMock = $this->createMock(IndexInterface::class);
        $pusherClientMock = $this->createMock(NotificationPusherClientInterface::class);
        $pusherClientMock->expects($this->once())->method('subscribe')->willReturn(true);
        $mailSenderMock = $this->createMock(PublicUserEmailSender::class);

        $notifier = new Notifier($entityLoaderMock, $indexMock, $pusherClientMock, $mailSenderMock);
        $this->assertTrue($notifier->subscribeToPush(
            'TEST-UUID',
            NotificationPusherClientInterface::CHANNEL_APNS,
            ['token' => 'fake-token']
        ));
    }

    /**
     * Test that a email is sent
     */
    public function testSendPushNotification()
    {
        $entityLoaderMock = $this->createMock(EntityLoader::class);
        $indexMock = $this->createMock(IndexInterface::class);
        $pusherClientMock = $this->createMock(NotificationPusherClientInterface::class);
        $pusherClientMock->expects($this->once())->method('send')->willReturn(true);
        $mailSenderMock = $this->createMock(PublicUserEmailSender::class);

        $notifier = new Notifier($entityLoaderMock, $indexMock, $pusherClientMock, $mailSenderMock);

        // Setup a test notification
        $notification = $this->entityLoader->create(ObjectTypes::NOTIFICATION, $this->account->getAccountId());
        $notification->setValue("name", "New Comment");
        $notification->setValue("description", "Sky said xxx");
        $notification->setValue("owner_id", $this->testUser->getEntityId());
        $notification->setValue("obj_reference", 'UUID-FAKE', 'Some Mock Task');

        $this->assertTrue($notifier->sendNotificationPush($notification, $this->testUser));
    }
}

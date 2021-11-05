<?php

/**
 * Test entity email thread class
 */

namespace NetricTest\Entity\ObjType;

use Netric\Entity;
use Netric\Entity\EntityInterface;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\EmailThreadEntity;
use Netric\EntityDefinition\ObjectTypes;

/**
 * @group integration
 */
class EmailThreadTest extends TestCase
{
    /**
     * Tenant account
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
     * Test entities to delete
     *
     * @var EntityInterface[]
     */
    private $testEntities = [];

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
        $this->user = $this->account->getUser(null, UserEntity::USER_SYSTEM);
    }

    /**
     * Cleanup
     */
    protected function tearDown(): void
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        foreach ($this->testEntities as $entity) {
            $entityLoader->delete($entity, $this->account->getAuthenticatedUser());
        }
    }

    /**
     * Test dynamic factory of entity
     */
    public function testFactory()
    {
        $entity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::EMAIL_THREAD, $this->account->getAccountId());
        $this->assertInstanceOf(EmailThreadEntity::class, $entity);
    }

    /**
     * When we soft-delete a thread, it should remove all messages
     */
    public function testOnAfterSave_Archive()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        // Create a thread and an email message for testing
        $thread = $entityLoader->create(ObjectTypes::EMAIL_THREAD, $this->account->getAccountId());
        $thread->setValue("subject", "My New test Thread");
        $tid = $entityLoader->save($thread, $this->user);
        $this->testEntities[] = $thread;

        $message = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE, $this->account->getAccountId());
        $message->setValue("thread", $thread->getEntityId());
        $eid = $entityLoader->save($message, $this->user);
        $this->testEntities[] = $message;

        // archive the thread
        $entityLoader->archive($thread, $this->account->getAuthenticatedUser());

        // Check to make sure the message was soft-deleted as well
        $reloadedMessage = $entityLoader->getEntityById($message->getEntityId(), $this->account->getAccountId());
        $this->assertTrue($reloadedMessage->getValue("f_deleted"));
    }

    /**
     * When we undelete a thread, it should restore any deleted messages
     */
    public function testOnAfterSave_Undelete()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        // Create a thread and an email message for testing
        $thread = $entityLoader->create(ObjectTypes::EMAIL_THREAD, $this->account->getAccountId());
        $thread->setValue("subject", "My New test Thread");
        $tid = $entityLoader->save($thread, $this->user);
        $this->testEntities[] = $thread;

        $message = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE, $this->account->getAccountId());
        $message->setValue("thread", $thread->getEntityId());
        $eid = $entityLoader->save($message, $this->user);
        $this->testEntities[] = $message;

        // Soft delete the thread which will also soft delete the message (see testOnAfterSave_Remove)
        $entityLoader->archive($thread, $this->account->getAuthenticatedUser());

        // Now undelete the thread which should undelete the messsage
        $thread->setValue("f_deleted", false);
        $entityLoader->save($thread, $this->user);

        // Check to make sure the message was soft-deleted as well
        $reloadedMessage = $entityLoader->getEntityById($eid, $this->account->getAccountId());
        $this->assertFalse($reloadedMessage->getValue("f_deleted"));
    }

    /**
     * Make sure that on a hard delete, all messages are purged
     */
    public function testOnAfterDeleteHard()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        // Create a thread and an email message for testing
        $thread = $entityLoader->create(ObjectTypes::EMAIL_THREAD, $this->account->getAccountId());
        $thread->setValue("subject", "My New test Thread");
        $tid = $entityLoader->save($thread, $this->user);
        $this->testEntities[] = $thread;

        $message = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE, $this->account->getAccountId());
        $message->setValue("thread", $thread->getEntityId());
        $eid = $entityLoader->save($message, $this->user);
        $this->testEntities[] = $message;

        // Remove the thread
        $entityLoader->delete($thread, $this->account->getAuthenticatedUser());

        // Make sure message was also purged
        $this->assertNull($entityLoader->getEntityById($eid, $this->account->getAccountId()));
    }

    public function testAddToSenders()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $thread = $entityLoader->create(ObjectTypes::EMAIL_THREAD, $this->account->getAccountId());
        $thread->addToSenders("test1@myaereus.com, test2@myaereus.com");
        $this->assertEquals("test1@myaereus.com,test2@myaereus.com", $thread->getValue("senders"));

        // Re-order by adding test2 again and appending test3
        $thread->addToSenders("test2@myaereus.com, test3@myaereus.com");
        $this->assertEquals(
            "test2@myaereus.com,test3@myaereus.com,test1@myaereus.com",
            $thread->getValue("senders")
        );
    }


    public function testAddToReceivers()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $thread = $entityLoader->create(ObjectTypes::EMAIL_THREAD, $this->account->getAccountId());
        $thread->addToSenders("test1@myaereus.com, test2@myaereus.com");
        $this->assertEquals("test1@myaereus.com,test2@myaereus.com", $thread->getValue("senders"));

        // Re-order by adding test2 again and appending test3
        $thread->addToSenders("test2@myaereus.com, test3@myaereus.com");
        $this->assertEquals(
            "test2@myaereus.com,test3@myaereus.com,test1@myaereus.com",
            $thread->getValue("senders")
        );
    }
}

<?php

/**
 * Test entity email thread class
 */
namespace NetricTest\Entity\ObjType;

use Netric\Entity;
use Netric\Entity\EntityInterface;
use Netric\Mime;
use Netric\Mail;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\EntityDefinitionLoader;
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
        $this->user = $this->account->getUser(UserEntity::USER_SYSTEM);
    }

    /**
     * Cleanup
     */
    protected function tearDown(): void
{
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        foreach ($this->testEntities as $entity) {
            $entityLoader->delete($entity, true);
        }
    }

    /**
     * Test dynamic factory of entity
     */
    public function testFactory()
    {
        $entity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::EMAIL_THREAD);
        $this->assertInstanceOf(EmailThreadEntity::class, $entity);
    }

    /**
     * When we soft-delete a thread, it should remove all messages
     */
    public function testOnAfterSave_Remove()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        // Create a thread and an email message for testing
        $thread = $entityLoader->create(ObjectTypes::EMAIL_THREAD);
        $thread->setValue("subject", "My New test Thread");
        $tid = $entityLoader->save($thread);
        $this->testEntities[] = $thread;

        $message = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE);
        $message->setValue("thread", $thread->getGuid());
        $eid = $entityLoader->save($message);
        $this->testEntities[] = $message;

        // Remove the thread
        $entityLoader->delete($thread);

        // Check to make sure the message was soft-deleted as well
        $reloadedMessage = $entityLoader->getByGuid($message->getGuid());
        $this->assertTrue($reloadedMessage->getValue("f_deleted"));
    }

    /**
     * When we undelete a thread, it should restore any deleted messages
     */
    public function testOnAfterSave_Undelete()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        // Create a thread and an email message for testing
        $thread = $entityLoader->create(ObjectTypes::EMAIL_THREAD);
        $thread->setValue("subject", "My New test Thread");
        $tid = $entityLoader->save($thread);
        $this->testEntities[] = $thread;

        $message = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE);
        $message->setValue("thread", $thread->getGuid());
        $eid = $entityLoader->save($message);
        $this->testEntities[] = $message;

        // Soft delete the thread which will also soft delete the message (see testOnAfterSave_Remove)
        $entityLoader->delete($thread);

        // Now undelete the thread which should undelete the messsage
        $thread->setValue("f_deleted", false);
        $entityLoader->save($thread);

        // Check to make sure the message was soft-deleted as well
        $reloadedMessage = $entityLoader->get(ObjectTypes::EMAIL_MESSAGE, $eid);
        $this->assertFalse($reloadedMessage->getValue("f_deleted"));
    }

    /**
     * Make sure that on a hard delete, all messages are purged
     */
    public function testOnAfterDeleteHard()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        // Create a thread and an email message for testing
        $thread = $entityLoader->create(ObjectTypes::EMAIL_THREAD);
        $thread->setValue("subject", "My New test Thread");
        $tid = $entityLoader->save($thread);
        $this->testEntities[] = $thread;

        $message = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE);
        $message->setValue("thread", $thread->getGuid());
        $eid = $entityLoader->save($message);
        $this->testEntities[] = $message;

        // Remove the thread
        $entityLoader->delete($thread, true);

        // Make sure message was also purged
        $this->assertNull($entityLoader->get(ObjectTypes::EMAIL_MESSAGE, $eid));
    }

    public function testAddToSenders()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $thread = $entityLoader->create(ObjectTypes::EMAIL_THREAD);
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
        $thread = $entityLoader->create(ObjectTypes::EMAIL_THREAD);
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

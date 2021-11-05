<?php

namespace NetricTest\Entity\ObjType;

use Netric\Entity\ObjType\EmailMessageEntity;
use Netric\Entity\EntityInterface;
use Netric\FileSystem\FileSystemFactory;
// use Netric\Mime;
// use Netric\Mail;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;

class EmailMessageTest extends TestCase
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
        $entity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::EMAIL_MESSAGE, $this->account->getAccountId());
        $this->assertInstanceOf(EmailMessageEntity::class, $entity);
    }

    // /**
    //  * Make sure we can parse , or ; separated message lists
    //  */
    // public function testGetAddressListFromString()
    // {
    //     $method = new \ReflectionMethod(EmailMessageEntity::class, 'getAddressListFromString');
    //     $method->setAccessible(true);

    //     $entityFactory = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
    //     $emailEntity = $entityFactory->create(ObjectTypes::EMAIL_MESSAGE, $this->account->getAccountId());
    //     $addresses = "\"Test\" <test@test.com>, test@test2.com";
    //     $addressList = $method->invoke($emailEntity, $addresses);
    //     $this->assertEquals(2, $addressList->count());

    //     $addresses2 = "\"Test\" <test@test.com>;, test@test2.com";
    //     $addressList = $method->invoke($emailEntity, $addresses2);
    //     $this->assertEquals(2, $addressList->count());
    // }

    // /**
    //  * Test convert a EmailMessageEntity into a Mail/Message that is mime encoded
    //  */
    // public function testToMailMimeMessage()
    // {
    //     $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
    //     $emailMessage = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE, $this->account->getAccountId());
    //     $emailMessage->setValue("subject", "My Test Message");
    //     $emailMessage->setValue("body", "<p>My Body</p>");
    //     $emailMessage->setValue("sent_from", "Test User <test@myaereuscom>");
    //     $emailMessage->setValue("send_to", "Another User <test2@myaereuscom>");
    //     $emailMessage->setValue("cc", "Copy User <test3@myaereuscom>");
    //     $emailMessage->setValue("bcc", "Blind User <test4@myaereuscom>");

    //     $mailMessage = $emailMessage->toMailMessage();

    //     // Test headers
    //     $headers = $mailMessage->getHeaders();
    //     $this->assertTrue($headers->has("subject"));
    //     $this->assertTrue($headers->has("to"));
    //     $this->assertTrue($headers->has("cc"));
    //     $this->assertTrue($headers->has("bcc"));

    //     // Test body
    //     $body = $mailMessage->getBody();
    //     $parts = $body->getParts();
    //     $this->assertStringContainsString("My Body", $parts[0]->getContent());
    //     $this->assertStringContainsString("<p>My Body</p>", $parts[0]->getContent());
    // }

    // /**
    //  * Test convert a EmailMessageEntity into a Mail/Message that is mime encoded
    //  */
    // public function testToMailMimeMessageAttachment()
    // {
    //     $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
    //     $emailMessage = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE, $this->account->getAccountId());
    //     $emailMessage->setValue("subject", "My Test Message");
    //     $emailMessage->setValue("body", "<p>My Body</p>");
    //     $emailMessage->setValue("sent_from", "Test User <test@myaereuscom>");
    //     $emailMessage->setValue("send_to", "Another User <test2@myaereuscom>");

    //     // Add an attachment
    //     $fileSystem = $this->account->getServiceManager()->get(FileSystemFactory::class);
    //     $file = $fileSystem->createFile("%tmp%", "testfile.txt", $this->account->getAuthenticatedUser(), true);
    //     $fileSystem->writeFile($file, "Textual Data", $this->user);
    //     $this->testEntities[] = $file;
    //     $emailMessage->addMultiValue("attachments", $file->getEntityId(), $file->getName());


    //     $mailMessage = $emailMessage->toMailMessage();

    //     // Test attachments
    //     $body = $mailMessage->getBody();
    //     $parts = $body->getParts();
    //     $this->assertStringContainsString(
    //         "Textual Data",
    //         // 0 = body, 1 = file
    //         $parts[1]->getRawContent()
    //     );
    //     $this->assertEquals("testfile.txt", $parts[1]->getFileName());
    //     $this->assertEquals("application/octet-stream", $parts[1]->getType());
    //     $this->assertEquals(Mime\Mime::ENCODING_BASE64, $parts[1]->getEncoding());
    // }

    // /**
    //  * Test importing a simple text Mail\Message into an entity
    //  */
    // public function testFromMailMessage_Plain()
    // {
    //     $message = new Mail\Message();
    //     $message->setEncoding('UTF-8');
    //     $message->setSubject("Test Email");
    //     $message->addFrom("test@myaereus.com");
    //     $message->addTo("test2@myaereus.com");
    //     $message->getHeaders()->addHeaderLine("content-type", Mime\Mime::TYPE_TEXT);

    //     // Add the message to the mail/Message and return
    //     $message->setBody("My Body");

    //     // Now import this message into entity
    //     $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
    //     $emailMessage = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE, $this->account->getAccountId());
    //     $emailMessage->fromMailMessage($message);

    //     // Test values
    //     $this->assertEquals("Test Email", $emailMessage->getValue("subject"));
    //     $this->assertEquals("<test2@myaereus.com>", $emailMessage->getValue("send_to"));
    //     $this->assertEquals("My Body", $emailMessage->getValue("body"));
    //     $this->assertEquals("plain", $emailMessage->getValue("body_type"));
    // }

    // /**
    //  * Test importing a simple text Mail\Message into an entity
    //  */
    // public function testFromMailMessage_Html()
    // {
    //     $message = new Mail\Message();
    //     $message->setEncoding('UTF-8');
    //     $message->setSubject("Test Email");
    //     $message->addFrom("test@myaereus.com");
    //     $message->addTo("test2@myaereus.com");
    //     $message->getHeaders()->addHeaderLine("content-type", Mime\Mime::TYPE_HTML);

    //     // Add the message to the mail/Message and return
    //     $message->setBody("<p>My Body</p>");

    //     // Now import this message into entity
    //     $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
    //     $emailMessage = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE, $this->account->getAccountId());
    //     $emailMessage->fromMailMessage($message);

    //     // Test values
    //     $this->assertEquals("Test Email", $emailMessage->getValue("subject"));
    //     $this->assertEquals("<test2@myaereus.com>", $emailMessage->getValue("send_to"));
    //     $this->assertEquals("<p>My Body</p>", $emailMessage->getValue("body"));
    //     $this->assertEquals("html", $emailMessage->getValue("body_type"));
    // }

    public function testDiscoverThread()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        // Create first message - this makes a new thread
        $email1 = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE, $this->account->getAccountId());
        $email1->setValue("message_id", "utest-" . rand());
        $email1->setValue("subject", "test message 1");
        $email1->setValue("owner_id", $this->user->getEntityId());
        $entityLoader->save($email1, $this->user);
        $this->testEntities[] = $email1;

        // Make sure we created a new thread
        $this->assertNotEmpty($email1->getValue("thread"));

        // Now create a second message, simulating a reply to
        $email2 = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE, $this->account->getAccountId());
        $email2->setValue("in_reply_to", $email1->getValue("message_id"));
        $email2->setValue("subject", "test message 2");
        $email2->setValue("owner_id", $this->user->getEntityId());
        $entityLoader->save($email2, $this->user);
        $this->testEntities[] = $email2;

        // Make sure it discovered the thread
        $this->assertEquals($email1->getValue("thread"), $email2->getValue("thread"));
    }

    public function testOnBeforeSave()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        // Create first message - this makes a new thread
        $email = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE, $this->account->getAccountId());

        // Set the onwer since it is required for the onBeforeSave function
        $email->setValue('owner_id', $this->account->getAuthenticatedUser()->getEntityId());

        // Run through onBeforeSave and make sure it worked
        $email->onBeforeSave($this->account->getServiceManager(), $this->account->getSystemUser());

        // Should have generated a message Id
        $this->assertNotEmpty($email->getValue("message_id"));

        // Should have created a new thread
        $this->assertNotEmpty($email->getValue("thread"));

        // Should have set num_attachments to 0
        $this->assertEquals(0, $email->getValue("num_attachments"));

        // cleanup thread
        $thread = $entityLoader->getEntityById($email->getValue("thread"), $this->account->getAccountId());
        $this->testEntities[] = $thread;
    }

    public function testOnAfterSave_Delete()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        // Create first message - this makes a new thread
        $email1 = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE, $this->account->getAccountId());
        $email1->setValue("message_id", "utest-" . rand());
        $email1->setValue("owner_id", $this->user->getEntityId());
        $entityLoader->save($email1, $this->user);
        $this->testEntities[] = $email1;

        // Now create a second message, simulating a reply to and attach
        $email2 = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE, $this->account->getAccountId());
        $email2->setValue("in_reply_to", $email1->getValue("message_id"));
        $email2->setValue("owner_id", $this->user->getEntityId());
        $entityLoader->save($email2, $this->user);
        $this->testEntities[] = $email2;

        $entityLoader->clearCacheByGuid($email1->getValue("thread"));
        $thread = $entityLoader->getEntityById($email1->getValue("thread"), $this->account->getAccountId());

        // Should have 2 messages in the queue
        $this->assertEquals(2, $thread->getValue("num_messages"));

        // Delete one of the messages
        $entityLoader->archive($email2, $this->account->getAuthenticatedUser());

        // Should have decremented num_messages but not deleted the thread
        $entityLoader->clearCacheByGuid($email1->getValue("thread"));
        $thread = $entityLoader->getEntityById($email1->getValue("thread"), $this->account->getAccountId());
        $this->assertEquals(1, $thread->getValue("num_messages"));
        $this->assertFalse($thread->isArchived());

        // Delete the last message
        $entityLoader->archive($email1, $this->account->getAuthenticatedUser());

        // Should have decremented num_messages but not deleted the thread
        // $entityLoader->clearCacheByGuid($email1->getValue("thread"));
        // $thread = $entityLoader->getEntityById($email1->getValue("thread"), $this->account->getAccountId());
        // $this->assertTrue($thread->isArchived());
    }

    public function testGetHtmlBody()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $emailMessage = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE, $this->account->getAccountId());

        // Add a plain text message
        $emailMessage->setValue("body_type", EmailMessageEntity::BODY_TYPE_PLAIN);
        $emailMessage->setValue("body", "my\nmessage");

        // Test
        $expected = "my<br />\nmessage";
        $this->assertEquals($expected, $emailMessage->getHtmlBody());
    }

    public function testGetPlainBody()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $emailMessage = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE, $this->account->getAccountId());

        // Add a plain text message
        $emailMessage->setValue("body_type", EmailMessageEntity::BODY_TYPE_HTML);
        $emailMessage->setValue("body", "<style>.test{padding:0;}</style>my<br />message");

        // Test
        $expected = "my\nmessage";
        $this->assertEquals($expected, $emailMessage->getPlainBody());
    }
}

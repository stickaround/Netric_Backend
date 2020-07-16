<?php

namespace NetricTest\Mail;

use Netric\Entity\EntityInterface;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\ObjType\EmailAccountEntity;
use Netric\Account\Account;
use Netric\EntityGroupings\Group;
use PHPUnit\Framework\TestCase;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Mail\DeliveryServiceFactory;

class DeliveryServiceTest extends TestCase
{
    /**
     * Email address we'll use for testing in this class
     */
    const TEST_EMAIL = 'test@deliveryservice.com';

    /**
     * The user that owns the email account
     *
     * @var UserEntity
     */
    private $user = null;

    /**
     * Current user before test was run
     *
     * @var UserEntity
     */
    private $origCurrentUser = null;

    /**
     * Test email account for receiving local messages
     *
     * @var EmailAccountEntity
     */
    private $emailAccount = null;

    /**
     * Active test account
     *
     * @var Account
     */
    private $account = null;

    /**
     * Any test entities created
     *
     * @var EntityInterface[]
     */
    private $testEntities = [];

    /**
     * Inbox grouping
     *
     * @var Group
     */
    private $inbox = null;

    /**
     * Setup the service
     */
    protected function setUp(): void
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        // Create a temporary user
        $this->origCurrentUser = $this->account->getUser();
        $this->user = $entityLoader->create(ObjectTypes::USER);
        $this->user->setValue("name", "utest-email-receiver-" . rand());
        $this->user->setValue('email', self::TEST_EMAIL);
        $entityLoader->save($this->user);
        $this->testEntities[] = $this->user;
        $this->account->setCurrentUser($this->user);

        // If it does not exist, create an inbox for the user
        $groupingsLoader = $this->account->getServiceManager()->get(GroupingLoaderFactory::class);
        $groupings = $groupingsLoader->get(ObjectTypes::EMAIL_MESSAGE . "/mailbox_id/" . $this->user->getEntityId());
        $inbox = new Group();
        $inbox->name = "Inbox";
        $inbox->isSystem = true;
        $inbox->user_id = $this->user->getEntityId();
        $groupings->add($inbox);
        $groupingsLoader->save($groupings);
        $this->inbox = $groupings->getByPath("Inbox");

        // Create a new test email account
        $this->emailAccount = $entityLoader->create(ObjectTypes::EMAIL_ACCOUNT);
        $this->emailAccount->setValue("type", "imap");
        $this->emailAccount->setValue('address', self::TEST_EMAIL);
        $this->emailAccount->setValue('owner_id', $this->user->getEntityId());
        $this->emailAccount->setValue("name", "test-imap");
        $this->emailAccount->setValue("host", getenv('TESTS_NETRIC_MAIL_HOST'));
        $this->emailAccount->setValue("username", getenv('TESTS_NETRIC_MAIL_USER'));
        $this->emailAccount->setValue("password", getenv('TESTS_NETRIC_MAIL_PASSWORD'));
        $entityLoader->save($this->emailAccount);
        $this->testEntities[] = $this->emailAccount;
    }

    protected function tearDown(): void
    {
        $serviceLocator = $this->account->getServiceManager();
        // Delete the inbox
        $groupingsLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        $groupings = $groupingsLoader->get(ObjectTypes::EMAIL_MESSAGE . "/mailbox_id/" . $this->user->getEntityId());
        $groupings->delete($this->inbox->guid);
        $groupingsLoader->save($groupings);

        // Delete any test entities
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        foreach ($this->testEntities as $entity) {
            $entityLoader->delete($entity, true);
        }

        // Restore original current user
        $this->account->setCurrentUser($this->origCurrentUser);
    }

    /**
     * Test that a complex mime message can be delivered by passing in a file
     *
     * multipart/related
     *  multipart/alternative
     *    text/plain
     *    test/html
     *
     * The multipart/related (first part) is basically useless, but we need to handle it.
     */
    public function testDeliverMessageFromFileComplex()
    {
        $deliveryService = $this->account->getServiceManager()->get(DeliveryServiceFactory::class);
        $messageGuid = $deliveryService->deliverMessageFromFile(
            self::TEST_EMAIL,
            __DIR__ . '/_files/m6.complex.mime.unseen'
        );

        $this->assertNotNull($messageGuid);

        $emailMessage = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->getByGuid($messageGuid);
        $this->testEntities[] = $emailMessage;

        // Check some snippets of text that should be in the hrml body
        $this->assertStringContainsString("$2,399", $emailMessage->getValue("body"));
        // Make sure the body which is quoted-printable was decoded
        $this->assertStringContainsString(
            "td style=\"font-weight: bold; padding-top: 10px; padding-left: 12px;\"",
            $emailMessage->getValue("body")
        );
    }
}

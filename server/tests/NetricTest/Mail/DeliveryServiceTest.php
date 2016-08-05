<?php
namespace NetricTest\Mail;

use Netric\EntityQuery;
use Netric\Mail\Storage;
use Netric\Mail\Storage\Imap;
use Netric\Entity\EntityInterface;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\ObjType\EmailAccountEntity;
use Netric\Account\Account;
use Netric\EntityGroupings\Group;
use PHPUnit_Framework_TestCase;

class DeliveryServiceTest extends PHPUnit_Framework_TestCase
{
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
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $entityLoader = $this->account->getServiceManager()->get("EntityLoader");

        // Create a temporary user
        $this->origCurrentUser = $this->account->getUser();
        $this->user = $entityLoader->create("user");
        $this->user->setValue("name", "utest-email-receiver-" . rand());
        $entityLoader->save($this->user);
        $this->testEntities[] = $this->user;
        $this->account->setCurrentUser($this->user);

        // If it does not exist, create an inbox for the user
        $groupingsLoader = $this->account->getServiceManager()->get("Netric/EntityGroupings/Loader");
        $groupings = $groupingsLoader->get(
            "email_message", "mailbox_id", ["user_id" => $this->user->getId()]
        );
        $inbox = new Group();
        $inbox->name = "Inbox";
        $inbox->isSystem = true;
        $inbox->user_id = $this->user->getId();
        $groupings->add($inbox);
        $groupingsLoader->save($groupings);
        $this->inbox = $groupings->getByPath("Inbox");

        // Create a new test email account
        $this->emailAccount = $entityLoader->create("email_account");
        $this->emailAccount->setValue("type", "imap");
        $this->emailAccount->setValue("name", "test-imap");
        $this->emailAccount->setValue("host", getenv('TESTS_NETRIC_MAIL_HOST'));
        $this->emailAccount->setValue("username", getenv('TESTS_NETRIC_MAIL_USER'));
        $this->emailAccount->setValue("password", getenv('TESTS_NETRIC_MAIL_PASSWORD'));
        $entityLoader->save($this->emailAccount);
        $this->testEntities[] = $this->emailAccount;

        $this->setupMessages();
    }

    protected function tearDown()
    {
        $serviceLocator = $this->account->getServiceManager();
        // Delete the inbox
        $groupingsLoader = $serviceLocator->get("Netric/EntityGroupings/Loader");
        $groupings = $groupingsLoader->get(
            "email_message", "mailbox_id", ["user_id" => $this->user->getId()]
        );
        $groupings->delete($this->inbox->id);
        $groupingsLoader->save($groupings);

        // Delete any test entities
        $entityLoader = $serviceLocator->get("EntityLoader");
        foreach ($this->testEntities as $entity) {
            $entityLoader->delete($entity, true);
        }

        // Restore original current user
        $this->account->setCurrentUser($this->origCurrentUser);
    }

    private function setupMessages()
    {
        // Append test messages
        $testFilesRoot = __DIR__ . '/_files/';

    }
    
    public function testDeliverComplex()
    {
        $deliveryService = $this->account->getServiceManager()->get("Netric/Mail/DeliveryService");
        $storageMessage = new Storage\Message(['file'=>__DIR__ . '/_files/m6.complex.mime.unseen']);
        $fakeUniqueId = "1234"; // Does not really matter
        $messageId = $deliveryService->deliverMessage(
            $this->user,
            $fakeUniqueId,
            $storageMessage,
            $this->emailAccount,
            $this->inbox->id
        );

        $this->assertNotEquals(0, $messageId);
        $this->assertNotEquals(-1, $messageId);
    }
}

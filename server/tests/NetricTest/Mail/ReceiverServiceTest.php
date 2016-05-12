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

class ReceiverServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * The user that owns the email account
     *
     * @var UserEntity
     */
    private $user = null;

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
        $this->user = $entityLoader->create("user");
        $this->user->setValue("name", "utest-email-receiver-" . rand());
        $entityLoader->save($this->user);
        $this->testEntities[] = $this->user;
        $this->account->setCurrentUser($this->user);

        // If it does not exist, create an inbox for the user
        $groupingsLoader = $this->account->getServiceManager()->get("Netric/EntityGroupings/Loader");
        $groupings = $groupingsLoader->get(
            "email_message", "mailbox_id", ["user_id"=>$this->user->getId()]
        );
        $inbox = new Group();
        $inbox->name = "Inbox";
        $inbox->isSystem = true;
        $inbox->user_id = $this->user->getId();
        $groupings->add($inbox);
        $groupingsLoader->save($groupings);
        $this->inbox = $groupings->getByPath("Inbox");

        // Create a new test email account with params above
        $this->emailAccount = $entityLoader->create("email_account");
        $this->emailAccount->setValue("type", "imap");
        $this->emailAccount->setValue("name", "test-imap");
        $this->emailAccount->setValue("host", getenv('TESTS_NETRIC_MAIL_IMAP_HOST'));
        $this->emailAccount->setValue("username", getenv('TESTS_NETRIC_MAIL_IMAP_USER'));
        $this->emailAccount->setValue("password", getenv('TESTS_NETRIC_MAIL_IMAP_PASSWORD'));
        $entityLoader->save($this->emailAccount);
        $this->testEntities[] = $this->emailAccount;

        if (!getenv('TESTS_NETRIC_MAIL_IMAP_ENABLED')) {
            $this->markTestSkipped('Netric Mail IMAP tests are not enabled');
        }

        if (getenv('TESTS_NETRIC_MAIL_SERVER_TESTDIR') && getenv('TESTS_NETRIC_MAIL_SERVER_TESTDIR')) {

            $this->cleanDir(getenv('TESTS_NETRIC_MAIL_SERVER_TESTDIR'));
            $this->copyDir(
                __DIR__ . '/_files/test.' . getenv('TESTS_NETRIC_MAIL_SERVER_FORMAT'),
                getenv('TESTS_NETRIC_MAIL_SERVER_TESTDIR')
            );
        }

    }

    protected function tearDown()
    {
        $serviceLocator = $this->account->getServiceManager();
        // Delete the inbox
        $groupingsLoader = $serviceLocator->get("Netric/EntityGroupings/Loader");
        $groupings = $groupingsLoader->get(
            "email_message", "mailbox_id", ["user_id"=>$this->user->getId()]
        );
        $groupings->delete($this->inbox->id);
        $groupingsLoader->save($groupings);

        // Delete any test entities
        $entityLoader = $serviceLocator->get("EntityLoader");
        foreach ($this->testEntities as $entity) {
            $entityLoader->delete($entity, true);
        }
    }

    protected function cleanDir($dir)
    {
        $dh = opendir($dir);
        while (($entry = readdir($dh)) !== false) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            $fullname = $dir . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($fullname)) {
                $this->cleanDir($fullname);
                rmdir($fullname);
            } else {
                unlink($fullname);
            }
        }
        closedir($dh);
    }

    protected function copyDir($dir, $dest)
    {
        $dh = opendir($dir);
        while (($entry = readdir($dh)) !== false) {
            if ($entry == '.' || $entry == '..' ) {
                continue;
            }
            $fullname = $dir  . DIRECTORY_SEPARATOR . $entry;
            $destname = $dest . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($fullname)) {
                mkdir($destname);
                $this->copyDir($fullname, $destname);
            } else {
                copy($fullname, $destname);
            }
        }
        closedir($dh);
    }

    public function testSyncMailbox_Download()
    {
        $receiver = $this->account->getServiceManager()->get("Netric/Mail/ReceiverService");

        $this->assertTrue($receiver->syncMailbox($this->inbox->id, $this->emailAccount));

        // Check if we imported 7 messages - the number that got copied
        $query = new EntityQuery("email_message");
        $query->where("mailbox_id")->equals($this->inbox->id);
        $query->andWhere("owner_id")->equals($this->user->getId());
        $query->andWhere("email_account")->equals($this->emailAccount->getId());
        $index = $this->account->getServiceManager()->get("EntityQuery_Index");
        $results = $index->executeQuery($query);
        $this->assertEquals(7, $results->getTotalNum());

        // Add imported to queue for cleanup
        for ($i = 0; $i < $results->getTotalNum(); $i++) {
            $this->testEntities[] = $results->getEntity($i);
        }
    }

    public function testSyncMailbox_DownloadDelete()
    {
        $receiver = $this->account->getServiceManager()->get("Netric/Mail/ReceiverService");

        // Import 7 sample messages from the copied files in the setUp
        $this->assertTrue($receiver->syncMailbox($this->inbox->id, $this->emailAccount));

        // Delete the message on the remote server
        $imap = new Imap([
            'host'     => $this->emailAccount->getValue("host"),
            'user'     => $this->emailAccount->getValue("username"),
            'password' => $this->emailAccount->getValue("password")
        ]);
        // Delete the first message
        foreach ($imap as $msgNo=>$message) {
            $imap->removeMessage($msgNo);
            break;
        }

        // Sync again which should delete a local message
        $this->assertTrue($receiver->syncMailbox($this->inbox->id, $this->emailAccount));

        // Check if one message got deleted
        $query = new EntityQuery("email_message");
        $query->where("mailbox_id")->equals($this->inbox->id);
        $query->andWhere("owner_id")->equals($this->user->getId());
        $query->andWhere("email_account")->equals($this->emailAccount->getId());
        $index = $this->account->getServiceManager()->get("EntityQuery_Index");
        $results = $index->executeQuery($query);
        $this->assertEquals(6, $results->getTotalNum());

        // Add imported to queue for cleanup
        for ($i = 0; $i < $results->getTotalNum(); $i++) {
            $this->testEntities[] = $results->getEntity($i);
        }
    }

    public function testSyncMailbox_UploadChange()
    {
        $receiver = $this->account->getServiceManager()->get("Netric/Mail/ReceiverService");
        $entityLoader = $this->account->getServiceManager()->get("EntityLoader");

        // Import 7 sample messages from the copied files in the setUp
        $this->assertTrue($receiver->syncMailbox($this->inbox->id, $this->emailAccount));

        // Delete one of the messages locally
        $query = new EntityQuery("email_message");
        $query->where("mailbox_id")->equals($this->inbox->id);
        $query->andWhere("owner_id")->equals($this->user->getId());
        $query->andWhere("email_account")->equals($this->emailAccount->getId());
        $index = $this->account->getServiceManager()->get("EntityQuery_Index");
        $results = $index->executeQuery($query);

        // Change the first entity
        $entity = $results->getEntity(0);
        $entity->setValue('flag_seen', true);
        $entity->setValue('flag_flagged', true);
        $entityLoader->save($entity);

        // Synchronize which should update the flags on the server
        $this->assertTrue($receiver->syncMailbox($this->inbox->id, $this->emailAccount));

        // Make sure messages were updated on the server
        $imap = new Imap([
            'host'     => $this->emailAccount->getValue("host"),
            'user'     => $this->emailAccount->getValue("username"),
            'password' => $this->emailAccount->getValue("password")
        ]);
        $msgNum = $imap->getNumberByUniqueId($entity->getValue("message_uid"));
        $message = $imap->getMessage($msgNum);

        $this->assertEquals(false, $message->hasFlag(Storage::FLAG_UNSEEN));
        $this->assertEquals(true, $message->hasFlag(Storage::FLAG_FLAGGED));

        // Queue all the messages for cleanup
        for ($i = 0; $i < $results->getTotalNum(); $i++) {
            $this->testEntities[] = $results->getEntity($i);
        }
    }

    public function testSyncMailbox_UploadDelete()
    {
        $receiver = $this->account->getServiceManager()->get("Netric/Mail/ReceiverService");
        $entityLoader = $this->account->getServiceManager()->get("EntityLoader");

        // Import 7 sample messages from the copied files in the setUp
        $this->assertTrue($receiver->syncMailbox($this->inbox->id, $this->emailAccount));

        // Delete one of the messages locally
        $query = new EntityQuery("email_message");
        $query->where("mailbox_id")->equals($this->inbox->id);
        $query->andWhere("owner_id")->equals($this->user->getId());
        $query->andWhere("email_account")->equals($this->emailAccount->getId());
        $index = $this->account->getServiceManager()->get("EntityQuery_Index");
        $results = $index->executeQuery($query);
        $entityLoader->delete($results->getEntity(0));

        // Synchronize which should delete the message on the server
        $this->assertTrue($receiver->syncMailbox($this->inbox->id, $this->emailAccount));

        // Delete the message on the remote server
        $imap = new Imap([
            'host'     => $this->emailAccount->getValue("host"),
            'user'     => $this->emailAccount->getValue("username"),
            'password' => $this->emailAccount->getValue("password")
        ]);
        $this->assertEquals(6, $imap->countMessages());

        // Queue all the messages for cleanup
        for ($i = 0; $i < $results->getTotalNum(); $i++) {
            $this->testEntities[] = $results->getEntity($i);
        }
    }
}

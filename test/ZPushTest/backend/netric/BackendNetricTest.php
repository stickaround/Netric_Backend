<?php

/**
 * Test the the custom netric backend for ActiveSync
 */

namespace ZPushTest\backend\netric;

use PHPUnit\Framework\TestCase;
use Netric\Mail\Transport\InMemory;
use Netric\Mail\SenderService;
use NetricTest\Bootstrap;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\FileSystem\FileSystemFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\Log\LogFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityQuery;
use Netric\Log\LogInterface;

// Add all z-push required files
require_once("z-push.includes.php");

// Include config
require_once(dirname(__FILE__) . '/../../../../config/zpush.config.php');

// Include backend classes
require_once('backend/netric/netric.php');
require_once('backend/netric/entityprovider.php');

class BackendNetricTest extends TestCase
{
    /**
     * Handle to account
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Test user
     *
     * @var \Netric\Entity\ObjType\UserEntity
     */
    private $user = null;

    /**
     * Common constants used
     *
     * @cons string
     */
    const TEST_USER = "test_auth";
    const TEST_USER_PASS = "testpass";
    const TEST_USER_FULL_NAME = "Test User";
    const TEST_USER_EMAIL = "test@test.com";

    /**
     * Netric backend for zpush
     *
     * @var BackendNetric
     */
    private $backend = null;

    /**
     * Test entities to cleanup
     *
     * @var \Netric\Entity\EntityInterface[]
     */
    private $testEntities = [];

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();

        // Setup entity datamapper for handling users
        $dm = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);

        // Make sure old test user does not exist
        $query = new EntityQuery(ObjectTypes::USER);
        $query->where('name')->equals(self::TEST_USER);
        $index = $this->account->getServiceManager()->get(IndexFactory::class);
        $res = $index->executeQuery($query);
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $user = $res->getEntity($i);
            $dm->delete($user, $this->account->getAuthenticatedUser());
        }

        // Create a test user
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $user = $loader->create(ObjectTypes::USER);
        $user->setValue("name", self::TEST_USER);
        $user->setValue("full_name", self::TEST_USER_FULL_NAME);
        $user->setValue("email", self::TEST_USER_EMAIL);
        $user->setValue("password", self::TEST_USER_PASS);
        $user->setValue("active", true);
        $user->setValue('account_id', $this->account->getAccountId());
        $dm->save($user, $this->account->getSystemUser());
        $this->user = $user;
        $this->testEntities[] = $user; // cleanup automatically

        // Initialize zpush - copied from zpush index file
        if (!defined('REAL_BASE_PATH')) {
            \ZPush::CheckConfig();
        }

        // Setup the banckend service
        $this->backend = new \BackendNetric();

        // Mock the log so we are not printing to stderr
        $log = $this->getMockBuilder(LogInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backend->setLog($log);

        // Set deviceId so we do not need to utilize the $_GET['device_id'] param
        $this->backend->setDeviceId("UNIT_TEST_FAKE_DEVICE");

        // Authenticate the test user
        $this->backend->Logon(
            self::TEST_USER,
            $this->account->getName(),
            self::TEST_USER_PASS
        );
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

    public function testGetSupportedASVersion()
    {
        $this->assertNotEmpty($this->backend->GetSupportedASVersion());
    }

    /**
     * Make sure we can log in via the backend
     */
    public function testLogin()
    {
        // First logoff to clear account and user
        $this->backend->Logoff();

        // Now try to authenticate and check to results
        $ret = $this->backend->Logon(
            self::TEST_USER,
            $this->account->getName(),
            self::TEST_USER_PASS
        );
        $this->assertTrue($ret);
    }

    public function testSetup()
    {
        $ret = $this->backend->Logon(
            self::TEST_USER,
            $this->account->getName(),
            self::TEST_USER_PASS
        );

        $this->assertTrue($this->backend->Setup($this->account->getName() . "/" . self::TEST_USER));
    }

    public function testLogoff()
    {
        $ret = $this->backend->Logon(
            self::TEST_USER,
            $this->account->getName(),
            self::TEST_USER_PASS
        );
        $this->assertTrue($this->backend->Logoff());
    }

    /**
     * Check getting the folder hierarchy
     */
    public function testGetHierarchy()
    {
        // Get folder hierarchy
        $folders = $this->backend->GetHierarchy();

        // Most of this is tested in EntityProviderTest, here we just make sure it is working
        $this->assertTrue(is_array($folders));
        $this->assertGreaterThan(0, count($folders));
    }

    /**
     * Make sure we can fetch an entity
     *
     * Specifics for different kinds of entities are tested more thoroughly in EntityProviderTest
     */
    public function testFetch()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $task = $entityLoader->create(ObjectTypes::TASK);
        $task->setValue("name", "My Unit Test Task");
        $task->setValue("owner_id", $this->user->getEntityId());
        $task->setValue("start_date", date("m/d/Y"));
        $task->setValue("date_completed", date("m/d/Y"));
        $task->setValue("deadline", date("m/d/Y"));
        $tid = $entityLoader->save($task, $this->user);

        // Queue for cleanup
        $this->testEntities[] = $task;

        $syncTask = $this->backend->Fetch(
            \EntityProvider::FOLDER_TYPE_TASK,
            $tid,
            new \ContentParameters()
        );

        $this->assertEquals($syncTask->subject, $task->getValue("name"));
        $this->assertEquals($syncTask->startdate, $task->getValue('start_date'));
        $this->assertEquals($syncTask->datecompleted, $task->getValue('date_completed'));
        $this->assertEquals($syncTask->duedate, $task->getValue('deadline'));
    }

    public function testGetUserDetails()
    {
        $this->assertEquals(
            ['emailaddress' => self::TEST_USER_EMAIL, 'fullname' => self::TEST_USER_FULL_NAME],
            $this->backend->GetUserDetails(self::TEST_USER)
        );
    }

    public function testGetCurrentUsername()
    {
        $this->assertEquals(self::TEST_USER, $this->backend->GetCurrentUsername());
    }

    public function testGetAttachmentData()
    {
        // Create a test file
        $testData = "test data";
        $fileSystem = $this->account->getServiceManager()->get(FileSystemFactory::class);
        $file = $fileSystem->createFile("%tmp%", "testZPushAttachment.txt", true);
        $fileSystem->writeFile($file, $testData);
        $this->testEntities[] = $file;

        // Get the file attachment and check the contents of the stream
        $addObj = $this->backend->GetAttachmentData($file->getEntityId());
        $buf = fread($addObj->data, strlen($testData));

        // Check results
        $this->assertEquals($testData, $buf);
    }

    public function testSendMail()
    {
        // Setup a test sender service to use
        $transport = new InMemory();
        $log = $this->account->getServiceManager()->get(LogFactory::class);
        $senderService = new SenderService(
            $transport,
            $transport,
            $log
        );
        $this->backend->setSenderService($senderService);

        // Attempt to send a message
        $syncSendMail = new \SyncSendMail();
        $syncSendMail->mime = file_get_contents(dirname(__FILE__) . '/TestAssets/mail.txt');
        $this->backend->SendMail($syncSendMail);

        // Test the sent message with valies from ./TestAssets/mail.txt
        $this->assertTrue(
            $transport->getLastMessage()->getTo()->has('foo@example.com'),
            var_export($transport->getLastMessage()->getTo(), true)
        );
        $this->assertEquals(
            'multipart',
            $transport->getLastMessage()->getSubject()
        );
        $this->assertTrue(
            $transport->getLastMessage()->getFrom()->has('test@test.com'),
            var_export($transport->getLastMessage()->getFrom(), true)
        );
    }

    /**
     * Test the folder changes sync using objectsync collections
     */
    public function testChangesSink()
    {
        // Get inbox - it was created in $this->setUp
        $groupingsLoader = $this->account->getServiceManager()->get(GroupingLoaderFactory::class);
        $groupings = $groupingsLoader->get(ObjectTypes::EMAIL_MESSAGE . "/mailbox_id/" . $this->user->getEntityId());
        $group = $groupings->create('test-' . rand());
        $group->user_id = $this->user->getEntityId();
        $groupings->add($group);
        $groupingsLoader->save($groupings);

        // Set the folder id to use
        $folderId = \EntityProvider::FOLDER_TYPE_EMAIL . ":" . $group->getGroupId();
        $this->backend->ChangesSinkInitialize($folderId);

        // Fast forward the collection as if was initialized
        $refIm = new \ReflectionObject($this->backend);
        $getSyncCollection = $refIm->getMethod("getSyncCollection");
        $getSyncCollection->setAccessible(true);
        $collection = $getSyncCollection->invoke($this->backend, $folderId);
        $collection->fastForwardToHead();

        // Get changes for Inbox - should be 0 because we reset above
        $changedFolders = $this->backend->ChangesSink(1);
        $this->assertEquals(0, count($changedFolders), var_export($changedFolders, true));

        // Create a dummy email message which will add the to stats
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $email = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE);
        $email->setValue("subject", "Test message");
        $email->setValue("flag_seen", 'f');
        $email->setValue("owner_id", $this->user->getEntityId());
        $email->setValue("mailbox_id", $group->getGroupId());
        $entityLoader->save($email, $this->user);
        $this->testEntities[] = $email;

        // Get changes for Inbox - should be 1
        $changedFolders = $this->backend->ChangesSink();
        $this->assertEquals(1, count($changedFolders));

        // Test synchronizing and make sure this goes back to 0
        $exporter = $this->backend->GetExporter($folderId);

        // Create an in-memory importer and initialize the exporter
        $importer = new \ChangesMemoryWrapper();
        $exporter->InitializeExporter($importer);
        $exporter->ConfigContentParameters(new \ContentParameters());

        $this->assertEquals(1, $exporter->GetChangeCount());

        // Synchronize which should clear the collection because it saves to importer
        $exporter->Synchronize();

        // Get changes for Inbox - should be 0 because we reset above
        $changedFolders = $this->backend->ChangesSink(1);
        $this->assertEquals(0, count($changedFolders), var_export($changedFolders, true));

        // Cleanup
        $groupings->delete($group->getGroupId());
    }

    /**
     * Make sure we can get a sync collection for tasks
     */
    public function testGetSyncCollection_Task()
    {
        $getSyncCollection = new \ReflectionMethod('\BackendNetric', 'getSyncCollection');
        $getSyncCollection->setAccessible(true);
        $folderId = \EntityProvider::FOLDER_TYPE_TASK . ":my";
        $collection = $getSyncCollection->invokeArgs($this->backend, [$folderId]);
        $this->assertNotNull($collection);
    }

    /**
     * Make sure we can get a sync collection for contacts
     */
    public function testGetSyncCollection_Contact()
    {
        $getSyncCollection = new \ReflectionMethod('\BackendNetric', 'getSyncCollection');
        $getSyncCollection->setAccessible(true);
        $folderId = \EntityProvider::FOLDER_TYPE_CONTACT . ":my";
        $collection = $getSyncCollection->invokeArgs($this->backend, [$folderId]);
        $this->assertNotNull($collection);
    }

    /**
     * Make sure we can get a sync collection for notes
     */
    public function testGetSyncCollection_Note()
    {
        $getSyncCollection = new \ReflectionMethod('\BackendNetric', 'getSyncCollection');
        $getSyncCollection->setAccessible(true);
        $folderId = \EntityProvider::FOLDER_TYPE_NOTE . "-all";
        $collection = $getSyncCollection->invokeArgs($this->backend, [$folderId]);
        $this->assertNotNull($collection);
    }
}

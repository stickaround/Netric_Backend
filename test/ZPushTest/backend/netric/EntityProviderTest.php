<?php

/**
 * Test the the custom netric backend for ActiveSync
 */

namespace ZPushTest\backend\netric;

use Netric\Account\Account;
use Netric\Entity\Recurrence\RecurrencePattern;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\FileSystem\FileSystemFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\Log\LogFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityQuery\EntityQuery;
use Netric\Log\LogInterface;

// Add all z-push required files
require_once("z-push.includes.php");

// Include config
require_once(dirname(__FILE__) . '/../../../../config/zpush.config.php');

// Include backend classes
require_once('backend/netric/netric.php');
require_once('backend/netric/entityprovider.php');

/**
 * @group integration
 */
class EntityProviderTest extends TestCase
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

    /**
     * Entity provider for converting entities to and from SyncObjects
     *
     * @var \EntityProvider
     */
    private $provider = null;

    /**
     * Test entities to cleanup
     *
     * @var \Netric\Entity\EntityInterface[]
     */
    private $testEntities = [];

    /**
     * Loader for opening, saving, and deleting entities
     *
     * @var \Netric\EntityLoader
     */
    private $entityLoader = null;

    /**
     * Test calendar
     *
     * @var \Netric\Entity\Entity
     */
    private $testCalendar = null;

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();

        // Setup entity datamapper for handling users
        $dm = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);

        // Make sure old test user does not exist
        $query = new EntityQuery(ObjectTypes::USER, $this->account->getAccountId());
        $query->where('name')->equals(self::TEST_USER);
        $index = $this->account->getServiceManager()->get(IndexFactory::class);
        $res = $index->executeQuery($query);
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $user = $res->getEntity($i);
            $dm->delete($user, $this->account->getAuthenticatedUser());
        }

        // Create a test user
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $user = $loader->create(ObjectTypes::USER, $this->account->getAccountId());
        $user->setValue("name", self::TEST_USER);
        $user->setValue("password", self::TEST_USER_PASS);
        $user->setValue("active", true);
        $dm->save($user, $this->account->getSystemUser());
        $this->user = $user;
        $this->testEntities[] = $user; // cleanup automatically

        // Get the entityLoader
        $this->entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        // Create inbox mailbox for testing
        $groupingsLoader = $this->account->getServiceManager()->get(GroupingLoaderFactory::class);
        $groupings = $groupingsLoader->get(ObjectTypes::EMAIL_MESSAGE . "/mailbox_id/" . $user->getEntityId());
        if (!$groupings->getByName("Inbox")) {
            $inbox = $groupings->create("Inbox");
            $inbox->user_id = $user->getEntityId();
            $groupings->add($inbox);
            $groupingsLoader->save($groupings);
        }

        // Create a calendar for the user to test
        $calendar = $this->entityLoader->create(ObjectTypes::CALENDAR, $this->account->getAccountId());
        $calendar->setValue("name", "UTest provider");
        $calendar->setValue("owner_id", $this->user->getEntityId());
        $this->entityLoader->save($calendar, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $calendar;
        $this->testCalendar = $calendar;

        // Initialize zpush - copied from zpush index file
        if (!defined('REAL_BASE_PATH')) {
            \ZPush::CheckConfig();
        }

        // Setup the provider service
        $this->provider = new \EntityProvider($this->account, $this->user);
    }

    /**
     * Cleanup
     */
    protected function tearDown(): void
    {
        foreach ($this->testEntities as $entity) {
            $this->entityLoader->delete($entity, $this->account->getAuthenticatedUser());
        }
    }

    /**
     * Make sure we get the contact folder
     */
    public function testGetContactFolders()
    {
        // Get folder hierarchy
        $folders = $this->provider->getContactFolders();

        $found = false;

        foreach ($folders as $folder) {
            if ($folder->serverid == \EntityProvider::FOLDER_TYPE_CONTACT . ':' . "my") {
                $found = true;
            }
        }

        // Make sure our folders existed
        $this->assertTrue($found);
    }

    /**
     * Check if we can get task folders
     */
    public function testGetTaskFolders()
    {
        // Get folder hierarchy
        $folders = $this->provider->getTaskFolders();

        $found = false;

        foreach ($folders as $folder) {
            if ($folder->serverid == \EntityProvider::FOLDER_TYPE_TASK . ':' . "my") {
                $found = true;
            }
        }

        // Make sure our folders existed
        $this->assertTrue($found);
    }

    /**
     * Check that we export calendars as SyncFolders
     */
    public function testGetCalendarFolders()
    {
        // Add a calendar for the user
        $entityLoader = $this->entityLoader;
        $calendar = $entityLoader->create(ObjectTypes::CALENDAR, $this->account->getAccountId());
        $calendar->setValue("name", "a test calendar");
        $calendar->setValue("owner_id", $this->user->getEntityId());
        $entityLoader->save($calendar, $this->account->getAuthenticatedUser());

        // Queue for cleanup
        $this->testEntities[] = $calendar;

        // Get calendars for this user
        $folders = $this->provider->getCalendarFolders();

        $found = false;
        foreach ($folders as $folder) {
            if ($folder->serverid == \EntityProvider::FOLDER_TYPE_CALENDAR . ':' . $calendar->getEntityId()) {
                $found = true;
            }
        }

        // Test result
        $this->assertTrue($found);
    }

    /**
     * Test getting email folder_id groupings as folders
     */
    public function testGetEmailFolders()
    {
        // Add a mail folder for the user
        $sm = $this->account->getServiceManager();
        $entityGroupingsLoader = $sm->get(GroupingLoaderFactory::class);
        $groupings = $entityGroupingsLoader->get(ObjectTypes::EMAIL_MESSAGE . "/mailbox_id/" . $this->user->getEntityId());

        /*
         * TODO: We have removed the ability to have multiple folders and just return the inbox
        $newGroup = $groupings->create();
        $newGroup->name = "utttest mailbox";
        $newGroup->user_id = $this->user->getEntityId();
        $groupings->add($newGroup);
        $entityGroupingsLoader->save($groupings);
        $savedGroup = $groupings->getByName("utttest mailbox");
        */

        // Get groupings as folders
        $folders = $this->provider->getEmailFolders();

        // Cleanup first
        /*
         * TODO: We have removed the ability to have multiple folders and just return the inbox
        // There should be two folders - one for the Inbox made is $this->setUp and the one created above
        $this->assertEquals(2, count($folders));
        */
        // We only return the inbox
        $this->assertEquals(1, count($folders));


        /*
         * TODO: We have removed the ability to have multiple folders and just return the inbox
        $found = false;
        foreach ($folders as $folder) {
            if ($folder->serverid == \EntityProvider::FOLDER_TYPE_EMAIL . ':' . $savedGroup->getGroupId()) {
                $found = true;
            }
        }

        // Cleanup first
        $groupings->delete($savedGroup->getGroupId());

        // Test result
        $this->assertTrue($found);
        */
    }

    /**
     * Test getting note groupings as folders
     */
    public function testGetNoteFolders()
    {
        // Get folder hierarchy
        $folders = $this->provider->getNoteFolders();

        $found = false;

        foreach ($folders as $folder) {
            if ($folder->serverid == \EntityProvider::FOLDER_TYPE_NOTE . ':' . "my") {
                $found = true;
            }
        }

        // Make sure our folders existed
        $this->assertTrue($found);
    }

    /**
     * Make sure we can get a task
     */
    public function testGetSyncObject_Task()
    {
        $task = $this->entityLoader->create(ObjectTypes::TASK, $this->account->getAccountId());
        $task->setValue("name", "My Unit Test Task");
        $task->setValue("owner_id", $this->user->getEntityId());
        $task->setValue("start_date", date("m/d/Y"));
        $task->setValue("date_completed", date("m/d/Y"));
        $task->setValue("deadline", date("m/d/Y"));
        $tid = $this->entityLoader->save($task, $this->account->getAuthenticatedUser());

        // Queue for cleanup
        $this->testEntities[] = $task;

        $syncTask = $this->provider->getSyncObject(
            \EntityProvider::FOLDER_TYPE_TASK,
            $tid,
            new \ContentParameters()
        );

        $this->assertEquals($syncTask->subject, $task->getValue("name"));
        $this->assertEquals($syncTask->startdate, $task->getValue('start_date'));
        $this->assertEquals($syncTask->datecompleted, $task->getValue('date_completed'));
        $this->assertEquals($syncTask->duedate, $task->getValue('deadline'));
    }

    /**
     * Make sure we can get a contact
     */
    public function testGetSyncObject_Contact()
    {
        $contact = $this->entityLoader->create(ObjectTypes::CONTACT_PERSONAL, $this->account->getAccountId());
        $contact->setValue("first_name", "John");
        $contact->setValue("last_name", "Doe");
        $contact->setValue("owner_id", $this->user->getEntityId());
        $cid = $this->entityLoader->save($contact, $this->account->getAuthenticatedUser());

        // Queue for cleanup
        $this->testEntities[] = $contact;

        $syncContact = $this->provider->getSyncObject(
            \EntityProvider::FOLDER_TYPE_CONTACT,
            $cid,
            new \ContentParameters()
        );

        $this->assertEquals($syncContact->firstname, $contact->getValue("first_name"));
        $this->assertEquals($syncContact->lastname, $contact->getValue('last_name'));
    }

    /**
     * Make sure we can get a calendar
     */
    public function testGetSyncObject_Appointment()
    {
        // Create a new calendar for this event
        $calendar = $this->entityLoader->create(ObjectTypes::CALENDAR, $this->account->getAccountId());
        $calendar->setValue("name", "UT_TEST_CALENDAR");
        $calid = $this->entityLoader->save($calendar, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $calendar;

        // Create an event
        $event = $this->entityLoader->create(ObjectTypes::CALENDAR_EVENT, $this->account->getAccountId());
        $event->setValue("name", "UnitTest Event");
        $event->setValue("ts_start", "10/8/2011 2:30 PM");
        $event->setValue("ts_end", "10/8/2011 3:30 PM");
        $event->setValue(ObjectTypes::CALENDAR, $calid);
        $cid = $this->entityLoader->save($event, $this->account->getAuthenticatedUser());

        // Queue for cleanup
        $this->testEntities[] = $event;

        $syncEvent = $this->provider->getSyncObject(
            \EntityProvider::FOLDER_TYPE_CALENDAR . ':' . $calid,
            $cid,
            new \ContentParameters()
        );

        $this->assertEquals($syncEvent->subject, $event->getValue("name"));
    }

    /**
     * Make sure we can get an email
     */
    public function testGetSyncObject_Email()
    {
        $email = $this->entityLoader->create(ObjectTypes::EMAIL_MESSAGE, $this->account->getAccountId());
        $email->setValue("subject", "A test message");
        $email->setValue("sent_from", "sky@stebnicki.net");
        $eid = $this->entityLoader->save($email, $this->account->getAuthenticatedUser());

        // Queue for cleanup
        $this->testEntities[] = $email;

        $syncMessage = $this->provider->getSyncObject(
            \EntityProvider::FOLDER_TYPE_EMAIL,
            $eid,
            new \ContentParameters()
        );

        $this->assertEquals($email->getValue("subject"), $syncMessage->subject);
        $this->assertEquals($email->getValue('sent_from'), $syncMessage->from);
    }

    public function testSaveSyncObject_Task()
    {
        $task = new \SyncTask();
        $task->subject = "UnitTest TaskName";
        $task->startdate = strtotime("11/17/2016");
        $task->duedate = strtotime("11/18/2016");
        $id = $this->provider->saveSyncObject(\EntityProvider::FOLDER_TYPE_TASK . ":my", null, $task);
        $this->assertNotNull($id);

        // Open and check the data
        $entity = $this->entityLoader->getEntityById($id, $this->account->getAccountId());
        $this->testEntities[] = $entity;
        $this->assertEquals($task->subject, $entity->getValue("name"));
        $this->assertNotNull($entity->getValue("owner_id"));
        $this->assertEquals(date("Y-m-d", $task->startdate), date("Y-m-d", $entity->getValue("start_date")));

        // Save changes to existing
        $task->subject = "UnitTest TaskName - edited";
        $this->provider->saveSyncObject(\EntityProvider::FOLDER_TYPE_TASK . ":my", $id, $task);

        // Test the new value
        $openedEntity = $this->entityLoader->getEntityById($id, $this->account->getAccountId());
        $this->assertEquals($task->subject, $openedEntity->getValue("name"));
    }

    public function testSaveSyncObject_Email()
    {
        $emailMailboxes = $this->provider->getEmailFolders();

        $mail = new \SyncMail();
        $mail->subject = "test";
        $id = $this->provider->saveSyncObject($emailMailboxes[0]->serverid, null, $mail);
        $this->assertNotNull($id);

        // Open and check the data
        $entity = $this->entityLoader->getEntityById($id, $this->account->getAccountId());
        $this->testEntities[] = $entity;
        $this->assertEquals($mail->subject, $entity->getValue("subject"));
        $this->assertNotNull($entity->getValue("owner_id"));

        // Save changes to existing
        $mail->subject = "test - edited";
        $this->provider->saveSyncObject($emailMailboxes[0]->serverid, $id, $mail);

        // Test the new value
        $openedEntity = $this->entityLoader->getEntityById($id, $this->account->getAccountId());
        $this->assertEquals($mail->subject, $openedEntity->getValue("subject"));
    }

    public function testSaveSyncObjectAppointment()
    {
        $folderIds = $this->provider->getCalendarFolders();

        // Play with timezones to make sure it is working as designed
        // $cur_tz = date_default_timezone_get();
        // date_default_timezone_set('UTC');

        $app = new \SyncAppointment();
        $app->timezone = base64_encode(\TimezoneUtil::GetSyncBlobFromTZ(\TimezoneUtil::GetFullTZ()));
        $app->starttime = strtotime("1/1/2011 10:11 PM");
        $app->endtime = strtotime("1/1/2011 11:11 PM");
        $app->subject = "New async unit test event";
        $app->uid = 'unittestevnt1';
        $app->location = 'My House';
        $app->recurrence = new \SyncRecurrence();
        $app->alldayevent = null;
        //$app->reminder = null;
        //$app->attendees = null;
        $app->body = "Notes here";
        //$app->exceptions = null;
        $app->recurrence->type = 1; // weekly
        $app->recurrence->interval = 1; // Every week
        $app->recurrence->dayofweek = $app->recurrence->dayofweek | RecurrencePattern::WEEKDAY_WEDNESDAY;
        $app->recurrence->until = strtotime("3/1/2011");

        $eid = $this->provider->saveSyncObject($folderIds[0]->serverid, null, $app);
        $this->assertNotNull($eid);

        // // Test timezone by making the local timezone New York -5 hours
        // date_default_timezone_set('America/New_York');

        // Open and check the data
        $entity = $this->entityLoader->getEntityById($eid, $this->account->getAccountId());
        $this->testEntities[] = $entity;
        $this->assertEquals($entity->getValue("name"), $app->subject);
        // Because we changed timezones, the times should be -5 hours  in EST
        $this->assertEquals($app->starttime, $entity->getValue("ts_start"));
        $this->assertEquals(date("Y-m-d h:i a T", $app->starttime), date("Y-m-d h:i a T", $entity->getValue("ts_start")));
        $this->assertEquals(date("Y-m-d h:i a T", $app->endtime), date("Y-m-d h:i a T", $entity->getValue("ts_end")));

        // Check recurrence
        $recur = $entity->getRecurrencePattern();
        $this->assertNotNull($recur);
        $this->assertEquals($recur->getRecurType(), RecurrencePattern::RECUR_WEEKLY);
        $this->assertEquals($recur->getDateEnd()->getTimestamp(), strtotime("3/1/2011"));
        $this->assertEquals($recur->getDayOfWeekMask(), RecurrencePattern::WEEKDAY_WEDNESDAY);

        // // Cleanup
        // date_default_timezone_set($cur_tz);
    }

    public function testSaveSyncObject_Contact()
    {
        $contact = new \SyncContact();
        $contact->firstname = "test";
        $contact->lastname = "contact";
        $id = $this->provider->saveSyncObject(\EntityProvider::FOLDER_TYPE_CONTACT . ":my", null, $contact);
        $this->assertNotNull($id);

        // Open and check the data
        $entity = $this->entityLoader->getEntityById($id, $this->account->getAccountId());
        $this->testEntities[] = $entity;
        $this->assertEquals($contact->firstname, $entity->getValue("first_name"));
        $this->assertEquals($contact->lastname, $entity->getValue("last_name"));
        $this->assertNotNull($entity->getValue("owner_id"));

        // Save changes to existing
        $contact->firstname = "test - edited";
        $this->provider->saveSyncObject(\EntityProvider::FOLDER_TYPE_CONTACT . ":my", $id, $contact);

        // Test the new value
        $openedEntity = $this->entityLoader->getEntityById($id, $this->account->getAccountId());
        $this->assertEquals($contact->firstname, $openedEntity->getValue("first_name"));
    }

    public function testSaveSyncObject_Note()
    {
        // Add a grouping to use
        $sm = $this->account->getServiceManager();
        $entityGroupingsLoader = $sm->get(GroupingLoaderFactory::class);
        $groupings = $entityGroupingsLoader->get(ObjectTypes::NOTE . "/groups/" . $this->user->getEntityId());
        $newGroup = $groupings->create();
        $newGroup->name = "utttest";
        $newGroup->user_id = $this->user->getEntityId();
        $groupings->add($newGroup);
        $entityGroupingsLoader->save($groupings);
        $savedGroup = $groupings->getByName("utttest");

        $note = new \SyncNote();
        $note->subject = "A Unit Test Note";
        $note->asbody = new \SyncBaseBody();
        $note->asbody->type = SYNC_BODYPREFERENCE_HTML;
        $note->asbody->data = \StringStreamWrapper::Open("<p>My Body</p>");
        $note->categories = [$savedGroup->name];
        $id = $this->provider->saveSyncObject(\EntityProvider::FOLDER_TYPE_NOTE . ":my", null, $note);
        $this->assertNotNull($id);

        // Open and check the data
        $entity = $this->entityLoader->getEntityById($id, $this->account->getAccountId());
        $this->testEntities[] = $entity;

        // Cleanup before testing
        $groupings->delete($savedGroup->getGroupId());

        // Test values
        $this->assertNotEmpty($entity->getValue("owner_id"));
        $this->assertEquals('html', $entity->getValue("body_type"));
        $originalBody = stream_get_contents($note->asbody->data, -1, 0);
        $this->assertEquals($originalBody, $entity->getValue("body"));
        $this->assertEquals($note->categories, ["utttest"]);

        // Save changes without setting body type and meta data for legacy active sync
        $note->asbody = "<p>My Edited Body</p>";
        $this->provider->saveSyncObject(\EntityProvider::FOLDER_TYPE_NOTE . ":my", $id, $note);

        // Test the new value
        $openedEntity = $this->entityLoader->getEntityById($id, $this->account->getAccountId());
        $this->assertEquals('plain', $entity->getValue("body_type"));
        $this->assertEquals($note->asbody, $entity->getValue("body"));
    }

    public function testMoveEntity_Email()
    {
        // Create drafts mailbox for testing - Inbox is already added in $this->setUp
        $groupingsLoader = $this->account->getServiceManager()->get(GroupingLoaderFactory::class);
        $groupings = $groupingsLoader->get(ObjectTypes::EMAIL_MESSAGE . "/mailbox_id/" . $this->user->getEntityId());
        if (!$groupings->getByName("Drafts")) {
            $inbox = $groupings->create("Drafts");
            $inbox->user_id = $this->user->getEntityId();
            $groupings->add($inbox);
            $groupingsLoader->save($groupings);
        }

        $grpInbox = $groupings->getByName("Inbox");
        $grpDrafts = $groupings->getByName("Drafts");

        $entity = $this->entityLoader->create(ObjectTypes::EMAIL_MESSAGE, $this->account->getAccountId());
        $entity->setValue("body", "unit tests provider");
        $entity->setValue("mailbox_id", $grpDrafts->guid);
        $entity->setValue("owner_id", $this->user->getEntityId());
        $entityId = $this->entityLoader->save($entity, $this->user);
        $this->testEntities[] = $entity;

        $ret = $this->provider->moveEntity(
            $entityId,
            \EntityProvider::FOLDER_TYPE_EMAIL . ':' . $grpDrafts->getGroupId(),
            \EntityProvider::FOLDER_TYPE_EMAIL . ':' . $grpInbox->getGroupId()
        );
        $this->assertTrue($ret);

        $loadedEntity = $this->entityLoader->getEntityById($entityId, $this->account->getAccountId());
        $this->assertEquals($grpInbox->getGroupId(), $loadedEntity->getValue("mailbox_id"));
    }

    public function testMoveEntity_Appointment()
    {
        $calendar1 = $this->testCalendar;

        // Create a second calendar - first is created in setUp
        $calendar2 = $this->entityLoader->create(ObjectTypes::CALENDAR, $this->account->getAccountId());
        $calendar2->setValue("name", "UTest provider 2");
        $calendar2->setValue("owner_id", $this->user->getEntityId());
        $this->entityLoader->save($calendar2, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $calendar2;

        $entity = $this->entityLoader->create(ObjectTypes::CALENDAR_EVENT, $this->account->getAccountId());
        $entity->setValue(ObjectTypes::CALENDAR, $calendar1->getEntityId());
        $id = $this->entityLoader->save($entity, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $entity;

        $ret = $this->provider->moveEntity(
            $id,
            \EntityProvider::FOLDER_TYPE_CALENDAR . ':' . $calendar1->getEntityId(),
            \EntityProvider::FOLDER_TYPE_CALENDAR . ':' . $calendar2->getEntityId()
        );
        $this->assertTrue($ret);

        $loadedEntity = $this->entityLoader->getEntityById($id, $this->account->getAccountId());
        $this->assertEquals($calendar2->getEntityId(), $loadedEntity->getValue(ObjectTypes::CALENDAR));
    }

    /**
     * Make sure entities that do not support moves are not moved
     */
    public function testMoveEntity_Unsupported()
    {
        $this->assertFalse(
            $this->provider->moveEntity(
                1,
                \EntityProvider::FOLDER_TYPE_NOTE . ":my",
                \EntityProvider::FOLDER_TYPE_NOTE . ":new"
            )
        );

        $this->assertFalse(
            $this->provider->moveEntity(
                1,
                \EntityProvider::FOLDER_TYPE_CONTACT . ":my",
                \EntityProvider::FOLDER_TYPE_CONTACT . ":new"
            )
        );

        $this->assertFalse(
            $this->provider->moveEntity(
                1,
                \EntityProvider::FOLDER_TYPE_TASK . ":my",
                \EntityProvider::FOLDER_TYPE_TASK . ":new"
            )
        );
    }

    public function testGetEntityStat()
    {
        $entity = $this->entityLoader->create(ObjectTypes::CALENDAR_EVENT, $this->account->getAccountId());
        $entity->setValue("name", "test event for stats");
        $entity->setValue(ObjectTypes::CALENDAR, $this->testCalendar->getEntityId());
        $id = $this->entityLoader->save($entity, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $entity;

        $stat = $this->provider->getEntityStat(
            \EntityProvider::FOLDER_TYPE_CALENDAR . ':' . $this->testCalendar->getEntityId(),
            $id
        );

        $this->assertEquals($id, $stat['id']);
        $this->assertGreaterThan(1, $stat['mod']);
    }

    public function testMarkEntitySeen()
    {
        // Get folders, at least one will be there because we created Inbox in $this->setUp
        $emailFolders = $this->provider->getEmailFolders();
        // Mailboxes are stored in '[obj_type]-[id]' format so get the id beflow
        $folderParts = explode(':', $emailFolders[0]->serverid);
        $mailboxId = $folderParts[1];

        $entity = $this->entityLoader->create(ObjectTypes::EMAIL_MESSAGE, $this->account->getAccountId());
        $entity->setValue("flag_seen", false);
        $entity->setValue("mailbox_id", $mailboxId);
        $id = $this->entityLoader->save($entity, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $entity;

        $ret = $this->provider->markEntitySeen(
            $emailFolders[0]->serverid,
            $id,
            true
        );
        $this->assertTrue($ret);

        $loadedEntity = $this->entityLoader->getEntityById($id, $this->account->getAccountId());
        $this->assertTrue($loadedEntity->getValue("flag_seen"));
    }

    public function testDeleteEntity()
    {
        // Get folders, at least one will be there because we created Inbox in $this->setUp
        $taskFolders = $this->provider->getTaskFolders();

        // Mailboxes are stored in '[obj_type]-[id]' format so get the id beflow
        $folderParts = explode(':', $taskFolders[0]->serverid);
        $mailboxId = $folderParts[1];

        $entity = $this->entityLoader->create(ObjectTypes::TASK, $this->account->getAccountId());
        $entity->setValue("name", "testDeleteEntity in provider");
        $entityId = $this->entityLoader->save($entity, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $entity;

        $ret = $this->provider->deleteEntity(
            $taskFolders[0]->serverid,
            $entityId
        );
        $this->assertTrue($ret);

        $loadedEntity = $this->entityLoader->getEntityById($entityId, $this->account->getAccountId());
        $this->assertTrue($loadedEntity->getValue("f_deleted"));
    }

    public function testDeleteFolder()
    {
        // Create a grouping to delete
        $groupingsLoader = $this->account->getServiceManager()->get(GroupingLoaderFactory::class);
        $groupings = $groupingsLoader->get(ObjectTypes::EMAIL_MESSAGE . "/mailbox_id/" . $this->user->getEntityId());
        $group = $groupings->getByName("Test");
        if (!$group) {
            $group = $groupings->create("Test");
            $group->user_id = $this->user->getEntityId();
            $groupings->add($group);
            $groupingsLoader->save($groupings);
        }

        $ret = $this->provider->deleteFolder(\EntityProvider::FOLDER_TYPE_EMAIL . ':' . $group->getGroupId());
        $this->assertTrue($ret);
    }

    public function testGetFolder()
    {
        // Get folders, at least one will be there because we created Inbox in $this->setUp
        $emailFolders = $this->provider->getEmailFolders();
        $first = $this->provider->getFolder($emailFolders[0]->serverid);
        $this->assertEquals($first, $emailFolders[0]);

        // Try with 'my' static folder
        $folderId = \EntityProvider::FOLDER_TYPE_TASK . ':my';
        $this->assertEquals(
            $folderId,
            $this->provider->getFolder($folderId)->serverid
        );
    }

    public function testGetAllFolders()
    {
        // Get folder hierarchy
        $folders = $this->provider->getAllFolders();

        $foundNote = false;
        $foundTask = false;
        $foundContact = false;
        $foundCalendar = false;
        $foundEmail = false;

        foreach ($folders as $folder) {
            if ($folder->serverid == \EntityProvider::FOLDER_TYPE_TASK . ':' . "my") {
                $foundTask = true;
            }
            if ($folder->serverid == \EntityProvider::FOLDER_TYPE_CONTACT . ':' . "my") {
                $foundContact = true;
            } else {
                // Test all other types that do not have static folders
                $parts = explode(':', $folder->serverid);
                switch ($parts[0]) {
                    case \EntityProvider::FOLDER_TYPE_EMAIL:
                        $foundEmail = true;
                        break;
                    case \EntityProvider::FOLDER_TYPE_CALENDAR:
                        $foundCalendar = true;
                        break;
                    case \EntityProvider::FOLDER_TYPE_NOTE:
                        $foundNote = true;
                        break;
                }
            }
        }

        // Make sure our folders existed
        $this->assertTrue($foundNote);
        $this->assertTrue($foundTask);
        $this->assertTrue($foundContact);
        $this->assertTrue($foundCalendar);
        $this->assertTrue($foundEmail);
    }
}

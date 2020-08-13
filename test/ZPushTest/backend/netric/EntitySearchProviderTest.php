<?php

/**
 * Test searching entities
 */

namespace ZPushTest\backend\netric;

use PHPUnit\Framework\TestCase;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityQuery\EntityQuery;
use NetricTest\Bootstrap;

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
class EntitySearchProviderTest extends TestCase
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
     * @var \EntitySearchProvider
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
     * Inbox
     *
     * @var \Netric\EntityGroupings\Group
     */
    private $groupInbox = null;


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
        $user->setValue("full_name", "Test User");
        $user->setValue("active", true);
        $user->setValue("email", "test@test.com");
        $dm->save($user, $this->account->getAuthenticatedUser());
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
        $this->groupInbox = $groupings->getByName("Inbox");

        // Create a calendar for the user to test
        $calendar = $this->entityLoader->create(ObjectTypes::CALENDAR, $this->account->getAccountId());
        $calendar->setValue("name", "UTest provider");
        $calendar->setValue("user_id", $this->user->getEntityId());
        $this->entityLoader->save($calendar, $this->user);
        $this->testEntities[] = $calendar;
        $this->testCalendar = $calendar;

        // Initialize zpush - copied from zpush index file
        if (!defined('REAL_BASE_PATH')) {
            \ZPush::CheckConfig();
        }

        // Setup the provider service
        $this->provider = new \EntitySearchProvider($this->account, $this->user);
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

    public function testGetGalSearchResults()
    {
        $items = $this->provider->GetGALSearchResults(self::TEST_USER, "0-100");
        $this->assertTrue(isset($items['range']));
        $this->assertGreaterThan(0, (int) $items['searchtotal']);

        $foundItem = null;

        foreach ($items as $item) {
            if (is_array($item) && $item[SYNC_GAL_DISPLAYNAME] == self::TEST_USER) {
                $foundItem = $item;
            }
        }
        $this->assertNotNull($foundItem);
        $this->assertEquals("Test", $foundItem[SYNC_GAL_FIRSTNAME]);
        $this->assertEquals("User", $foundItem[SYNC_GAL_LASTNAME]);
        $this->assertEquals($this->user->getValue("email"), $foundItem[SYNC_GAL_EMAILADDRESS]);
    }

    public function testGetMailboxSearchResults()
    {
        // Add test email message to inbox
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $email = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE, $this->account->getAccountId());
        $email->setValue("subject", "test message");
        $email->setValue("owner_id", $this->user->getEntityId());
        $email->setValue("mailbox_id", $this->groupInbox->getGroupId());
        $entityLoader->save($email, $this->user);
        $this->testEntities[] = $email;

        // Create content params object
        $cpo = new \ContentParameters();
        $cpo->SetSearchFreeText("test");
        $cpo->SetSearchRange("0-10");
        $cpo->GetSearchFolderid(\EntityProvider::FOLDER_TYPE_EMAIL . ":" . $this->groupInbox->getGroupId());

        // Run the search
        $items = $this->provider->GetMailboxSearchResults($cpo);

        $this->assertTrue(isset($items['range']));
        $this->assertGreaterThan(0, (int) $items['searchtotal']);

        $foundItem = null;

        foreach ($items as $item) {
            if (is_array($item) && $item['longid'] == $email->getEntityId()) {
                $foundItem = $item;
            }
        }
        $this->assertNotNull($foundItem);
    }

    public function testDisconnect()
    {
        $this->assertTrue($this->provider->Disconnect());
    }

    public function testTerminateSearch()
    {
        $this->assertTrue($this->provider->TerminateSearch(1));
    }
}

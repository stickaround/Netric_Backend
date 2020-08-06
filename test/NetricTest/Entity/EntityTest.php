<?php

/**
 * Test entity/object class
 */

namespace NetricTest\Entity;

use Netric\Entity\Entity;
use Netric\FileSystem\FileSystemFactory;
use PHPUnit\Framework\TestCase;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
use Netric\Entity\ObjType\UserEntity;
use NetricTest\Bootstrap;
use Netric\EntityDefinition\ObjectTypes;
use Ramsey\Uuid\Uuid;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityQuery;
use Netric\EntityQuery\Where;

class EntityTest extends TestCase
{
    /**
     * Tennant account
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
     * Test on create default timestamp
     */
    public function testOnCreateSetFieldDefaultTimestamp()
    {
        $cust = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::CONTACT, $this->account->getAccountId());
        $cust->setValue("name", "testFieldDefaultTimestamp");
        $cust->setFieldsDefault('create'); // ts_entered has a 'now' on 'create' default
        $this->assertTrue(is_numeric($cust->getValue("ts_entered")));
    }

    /**
     * Test on update default timestamp
     */
    public function testOnUpdateSetFieldDefaultTimestamp()
    {
        $cust = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::CONTACT, $this->account->getAccountId());
        $cust->setValue("name", "testFieldDefaultTimestamp");
        $cust->setFieldsDefault('update'); // ts_updated has a 'now' on 'update' default
        $this->assertTrue(is_numeric($cust->getValue("ts_updated")));
    }

    /**
     * Test default deleted to adjust for some bug with default values resetting f_deleted
     */
    public function testSetFieldsDefaultBool()
    {
        $cust = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::CONTACT, $this->account->getAccountId());
        $cust->setValue("name", "testFieldDefaultTimestamp");
        $cust->setValue("f_deleted", true);
        $cust->setFieldsDefault('null');
        $this->assertTrue($cust->getValue("f_deleted"));
    }

    /**
     * Test toArray funciton
     */
    public function testToArray()
    {
        $cust = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::CONTACT, $this->account->getAccountId());
        $cust->setValue("name", "Entity_DataMapperTests");
        // bool
        $cust->setValue("f_nocall", true);
        // object
        $cust->setValue("owner_id", $this->user->getEntityId(), $this->user->getValue("name"));
        // object_multi
        // fkey
        // fkey_multi
        // timestamp
        $cust->setValue("last_contacted", time());

        $data = $cust->toArray();
        $this->assertEquals($cust->getValue("name"), $data["name"]);
        $this->assertEquals($cust->getValue("last_contacted"), strtotime($data["last_contacted"]));
        $this->assertEquals($cust->getValue("owner_id"), $data["owner_id"]);
        $this->assertEquals($cust->getValueName("owner_id"), $data["owner_id_fval"][$data["owner_id"]]);
        $this->assertEquals($cust->getValue("f_nocall"), $data["f_nocall"]);
    }

    /**
     * Test loading from an array
     */
    public function testFromArray()
    {
        $data = [
            "name" => "testFromArray",
            "last_contacted" => time(),
            "f_nocall" => true,
            "company" => "test company",
            "owner_id" => $this->user->getEntityId(),
            "owner_id_fval" => [
                $this->user->getEntityId() => $this->user->getValue("name")
            ],
        ];

        // Load data into entity
        $cust = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::CONTACT, $this->account->getAccountId());
        $cust->fromArray($data);

        // Test values
        $this->assertEquals($cust->getValue("name"), $data["name"]);
        $this->assertEquals($cust->getValue("last_contacted"), $data["last_contacted"]);
        $this->assertEquals($cust->getValue("owner_id"), $data["owner_id"]);
        $this->assertEquals($cust->getValueName("owner_id"), $data["owner_id_fval"][$data["owner_id"]]);
        $this->assertEquals($cust->getValue("f_nocall"), $data["f_nocall"]);
        $this->assertEquals($cust->getValue("company"), $data["company"]);

        // Let's save $cust entity and try using ::fromArray() with an existing entity
        $dataMapper = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);
        $dataMapper->save($cust, $this->account->getAuthenticatedUser());

        // Now let's test the updating of entity with only specific fields
        $updatedData = [
            "name" => "Updated Customer From Array",
            "owner_id" => 5,
            "owner_id_fval" => [
                5 => "Updated Customer Owner"
            ]
        ];

        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $existingCust = $entityLoader->getEntityById($cust->getEntityId(), $this->account->getAccountId());

        // Load the updated data into the entity
        $existingCust->fromArray($updatedData, true);

        // It should store the updated data from the updated fields provided
        $this->assertEquals($existingCust->getValue("name"), $updatedData["name"]);
        $this->assertEquals($existingCust->getValue("owner_id"), $updatedData["owner_id"]);
        $this->assertEquals($existingCust->getValueName("owner_id"), $updatedData["owner_id_fval"][$updatedData["owner_id"]]);

        // Other fields should be the same and were not affected
        $this->assertEquals($cust->getValue("company"), $data["company"]);
    }

    /**
     * Make sure we can empty a multi-value field when loading from an array
     */
    public function testFromArrayEmptyMval()
    {
        $cust = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::CONTACT, $this->account->getAccountId());

        // Preset attachments
        $cust->addMultiValue("attachments", 1, "fakefile.txt");

        // This should unset the attachments property set above
        $data = [
            "attachments" => [],
            "attachments_fval" => [],
        ];

        // Load data into entity
        $cust->fromArray($data);

        // Test values
        $this->assertEquals(0, count($cust->getValue("attachments")));
    }

    /**
     * Test processing temp files
     */
    public function testProcessTempFiles()
    {
        $sm = $this->account->getServiceManager();

        $fileSystem = $sm->get(FileSystemFactory::class);
        $entityLoader = $sm->get(EntityLoaderFactory::class);
        $dataMapper = $sm->get(EntityDataMapperFactory::class);

        // Temp file
        $file = $fileSystem->createFile("%tmp%", "testfile.txt", true);
        $tempFolderId = $file->getValue("folder_id");

        // Create a customer
        $cust = $entityLoader->create(ObjectTypes::CONTACT, $this->account->getAccountId());
        $cust->setValue("name", "Aereus Corp");
        $cust->addMultiValue("attachments", $file->getEntityId(), $file->getValue("name"));
        $dataMapper->save($cust, $this->account->getAuthenticatedUser());

        // Test to see if file was moved
        $testFile = $fileSystem->openFileById($file->getEntityId());
        $this->assertNotEquals($tempFolderId, $testFile->getValue("folder_id"));

        // Cleanup
        $fileSystem->deleteFile($file, true);
    }

    /**
     * Test shallow cloning an entity
     */
    public function testCloneTo()
    {
        $cust = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::CONTACT, $this->account->getAccountId());
        $cust->setValue("name", "Entity_DataMapperTests");

        // bool
        $cust->setValue("f_nocall", true);

        // object
        $cust->setValue("owner_id", $this->user->getEntityId(), $this->user->getValue("name"));

        // TODO: object_multi
        // TODO: fkey
        // TODO: fkey_multi

        // timestamp
        $cust->setValue("last_contacted", time());
        // Set a fake id just to make sure it does not get copied
        $cust->setValue('entity_id', '82d264a2-8070-11e8-adc0-fa7ae01bbebc');

        // Clone it
        $cloned = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::CONTACT, $this->account->getAccountId());
        $cust->cloneTo($cloned);

        $this->assertEmpty($cloned->getEntityId());
        $this->assertEquals($cust->getValue("name"), $cloned->getValue("name"));
        $this->assertEquals($cust->getValue("f_nocall"), $cloned->getValue("f_nocall"));
        $this->assertEquals($cust->getValue("owner_id"), $cloned->getValue("owner_id"));
        $this->assertEquals($cust->getValueName("owner_id"), $cloned->getValueName("owner_id"));
        $this->assertEquals($cust->getValue("last_contacted"), $cloned->getValue("last_contacted"));
    }

    /**
     * Test the comments counter for an entity
     */
    public function testSetHasComments()
    {
        $cust = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::CONTACT, $this->account->getAccountId());

        // Should have incremented 'num_comments' to 1
        $cust->setHasComments();
        $this->assertEquals(1, $cust->getValue("num_comments"));

        // The first param will decrement the counter
        $cust->setHasComments(false);
        $this->assertEquals(0, $cust->getValue("num_comments"));
    }

    /**
     * Test getting tagged object references in text
     */
    public function testGetTaggedObjRef()
    {
        $uuid1 = "1680d153-600b-44f2-8143-540c2d91276f";
        $uuid2 = "b0c297c0-d6e7-489c-9e45-7f16d652dfb7";

        $test1 = "Hey [user:$uuid1:Sky] this is my test";
        $taggedReferences = Entity::getTaggedObjRef($test1);
        $this->assertEquals(1, count($taggedReferences));
        $this->assertEquals(["obj_type" => "user", "entity_id" => $uuid1, "name" => "Sky"], $taggedReferences[0]);

        $test2 = "This would test multiple [user:$uuid1:Sky] and [user:$uuid2:John]";
        $taggedReferences = Entity::getTaggedObjRef($test2);
        $this->assertEquals(2, count($taggedReferences));
        $this->assertEquals(["obj_type" => "user", "entity_id" => $uuid1, "name" => "Sky"], $taggedReferences[0]);
        $this->assertEquals(["obj_type" => "user", "entity_id" => $uuid2, "name" => "John"], $taggedReferences[1]);

        // Test unicode = John in Chinese
        $test1 = "Hey [user:$uuid1:约翰·] this is my test";
        $taggedReferences = Entity::getTaggedObjRef($test1);
        $this->assertEquals(1, count($taggedReferences));
        $this->assertEquals(["obj_type" => "user", "entity_id" => $uuid1, "name" => "约翰·"], $taggedReferences[0]);
    }

    /**
     * Test update followers
     *
     * This is a private function but because it is so fundamental in its use, we test
     * it separately from any public interface via a Reflection object. While this is
     * generally not a good idea to always test functions this way, it makes sense
     * in places like this that are small largely autonomous functions
     * used to control critical functionality.
     */
    public function testUpdateFollowers()
    {
        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $user1 = $entityLoader->create(ObjectTypes::USER, $this->account->getAccountId());
        $user1->setValue("name", "John");
        $entityLoader->save($user1, $this->account->getAuthenticatedUser());

        $user2 = $entityLoader->create(ObjectTypes::USER, $this->account->getAccountId());
        $entityLoader->save($user2, $this->account->getAuthenticatedUser());

        $this->testEntities[] = $user1;
        $this->testEntities[] = $user2;

        $userGuid1 = $user1->getEntityId();
        $userGuid2 = $user2->getEntityId();

        $entity = $entityLoader->create(ObjectTypes::TASK, $this->account->getAccountId());
        $entity->setValue("owner_id", $userGuid1, $user1->getName());
        $entity->setValue("notes", "Hey [user:$userGuid2:Dave], check this out please. [user:0:invalidId] should not add [user:abc:nonNumericId]");

        // Saving this entity will call the Entity::beforeSagetNameve() which will update the followers
        $entityLoader->save($entity, $this->account->getAuthenticatedUser());
        $this->testEntities[] = $entity;

        // Now make sure followers were set to the two references above
        $followers = $entity->getValue("followers");
        sort($followers);
        $this->assertTrue(in_array($userGuid1, $followers));
        $this->assertTrue(in_array($userGuid2, $followers));

        // Should only have 2 followers
        // Since the other 2 followers ([user:0:invalidId] and [user:abc:nonNumericId]) are invalid
        $this->assertEquals(3, count($followers));
    }

    /**
     * Test synchronize followers between two entities
     */
    public function testSyncFollowers()
    {
        // Add some fake users to a test task
        $task1 = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::TASK, $this->account->getAccountId());
        $task1->addMultiValue("followers", Uuid::uuid4()->toString(), "John");
        $task1->addMultiValue("followers", Uuid::uuid4()->toString(), "Dave");

        // Create a second task and synchronize
        $task2 = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::TASK, $this->account->getAccountId());
        $task2->syncFollowers($task1);

        $this->assertEquals(2, count($task1->getValue("followers")));
        $this->assertEquals($task1->getValue("followers"), $task2->getValue("followers"));
    }

    /**
     * Test sync followers with invalid followers data
     */
    public function testSyncFollowersWithInvalid()
    {
        // Add some fake users to a test task
        $task1 = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::TASK, $this->account->getAccountId());
        $johnGuid = Uuid::uuid4()->toString();
        $daveGuid = Uuid::uuid4()->toString();
        $task1->addMultiValue("followers", $johnGuid, "John");
        $task1->addMultiValue("followers", $daveGuid, "Dave");
        $task1->addMultiValue("followers", "testId", "invlid non-numeric id");
        $task1->addMultiValue("followers", null, "invalid null id");

        // Create a second task and synchronize
        $task2 = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::TASK, $this->account->getAccountId());
        $task2->syncFollowers($task1);

        // It will count 4 followers here since we added additional 2 invalid followers
        $this->assertEquals(4, count($task1->getValue("followers")));

        /*
         * The $task1 and $task2 will not have the same followers
         * Since $task1 has invalid followers while when syncing the followers into $task2
         *  will remove the invalid followers
         */
        $this->assertNotEquals($task1->getValue("followers"), $task2->getValue("followers"));

        // $task2 will only have 2 followers, since the other 2 is invalid
        $this->assertEquals(2, count($task2->getValue("followers")));
    }

    /**
     * Make sure that setting an object reference name works
     *
     * @return void
     */
    public function testSetValueObjectWithName()
    {
        $sm = $this->account->getServiceManager();
        $task = $sm->get(EntityLoaderFactory::class)->create(ObjectTypes::TASK, $this->account->getAccountId());
        $task->setValue('owner_id', 123, 'fakeusername');
        $this->assertEquals('fakeusername', $task->getValueName('owner_id'));
    }

    /**
     * Test setting the name of an object_mulit value id
     *
     * If setValue is called on a fkey_multi or object_multi field both
     * the id and the valueName should be arrays, but if they are not
     * then the entity needs to handle it correctly
     *
     * @return void
     */
    public function testSetValueObjectMultiWithName()
    {
        $username = 'fakeusername';
        $userid = Uuid::uuid4()->toString();
        $sm = $this->account->getServiceManager();
        $task = $sm->get(EntityLoaderFactory::class)->create(ObjectTypes::TASK, $this->account->getAccountId());
        $task->setValue('followers', $userid, $username);
        $this->assertEquals($username, $task->getValueName('followers'));
        $this->assertEquals([$userid], $task->getValue('followers'));
    }

    /**
     * Make sure the owner of an entity is correctly returned
     */
    public function testGetOwnerGuid()
    {
        $sm = $this->account->getServiceManager();
        $task = $sm->get(EntityLoaderFactory::class)->create(ObjectTypes::TASK, $this->account->getAccountId());

        $userGuid = Uuid::uuid4()->toString();
        $task->setValue('owner_id', $userGuid);
        $this->assertEquals($userGuid, $task->getOwnerId());
    }

    /**
     * Make sure the owner of an entity is correctly if owner_id is not set but creator_id is
     */
    public function testGetOwnerGuidCreatorId()
    {
        $sm = $this->account->getServiceManager();
        $task = $sm->get(EntityLoaderFactory::class)->create(ObjectTypes::TASK, $this->account->getAccountId());

        $userGuid = Uuid::uuid4()->toString();
        $task->setValue('creator_id', $userGuid);
        $this->assertEquals($userGuid, $task->getOwnerId());
    }

    /**
     * Make sure the owner of an entity is correctly if owner_id is not set but owner_id is
     */
    public function testGetOwnerGuidUserId()
    {
        $sm = $this->account->getServiceManager();
        // Activity has a owner_id field
        $activity = $sm->get(EntityLoaderFactory::class)->create(ObjectTypes::ACTIVITY, $this->account->getAccountId());

        $userGuid = Uuid::uuid4()->toString();
        $activity->setValue('owner_id', $userGuid);
        $this->assertEquals($userGuid, $activity->getOwnerId());
    }

    /**
     * Make sure that we can retrieve ic documents when filtering the is_rootspace field
     */
    public function testICDocumentIsRootspaceField()
    {
        $sm = $this->account->getServiceManager();
        $entityLoader = $sm->get(EntityLoaderFactory::class);
        $index = $sm->get(IndexFactory::class);
        $document = $entityLoader->create(ObjectTypes::DOCUMENT, $this->account->getAccountId());

        $document->setValue('is_rootspace', true);
        $entityLoader->save($document, $this->account->getAuthenticatedUser());

        $this->testEntities[] = $document;

        // We will now create a query to get document with is_rootspace set to true
        $query = new EntityQuery(ObjectTypes::DOCUMENT);
        $query->where('is_rootspace')->equals(true);
        $query->where('entity_id')->equals($document->getEntityId());
        $res = $index->executeQuery($query);

        // This should return 1 result since we have created a document with is_rootspace field set to true
        $this->assertEquals(1, $res->getTotalNum());
    }
}

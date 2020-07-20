<?php

/**
 * Test entity sync partners
 */

namespace NetricTest\EntitySync;

use Netric\EntitySync;
use Netric\EntitySync\Partner;
use PHPUnit\Framework\TestCase;
use Netric\Entity\ObjType\UserEntity;
use NetricTest\Bootstrap;
use Netric\EntitySync\DataMapperFactory;
use Netric\EntitySync\Collection\CollectionInterface;
use Netric\EntitySync\Collection\EntityCollection;
use Netric\EntityDefinition\ObjectTypes;

/**
 * Class PartnerTest
 * @package NetricTest\EntitySync
 */
class PartnerTest extends TestCase
{
    /**
     * Tennant accountAbstractCollectionTests
     *
     * @var \Netric\Account\Account
     */
    protected $account = null;

    /**
     * Administrative user
     *
     * @var \Netric\Entity\ObjType\UserEntity
     */
    protected $user = null;

    /**
     * Test partner
     *
     * @var \Netric\EntitySync\Partner
     */
    protected $partner = null;


    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
        $this->user = $this->account->getUser(null, UserEntity::USER_SYSTEM);

        $partnerId = "PartnerTest";
        $dm = $this->account->getServiceManager()->get(DataMapperFactory::class);
        $this->partner = new Partner($dm);
    }

    /**
     * Make sure we can construct this partner
     */
    public function testConstruct()
    {
        $this->assertInstanceOf(Partner::class, $this->partner);
    }

    /**
     * Test set and get id
     */
    public function testSetAndGetId()
    {
        $this->partner->setId(123);
        $this->assertEquals(123, $this->partner->getId());
    }

    /**
     * Test set and get id
     */
    public function testSetAndGetPartnerId()
    {
        $this->partner->setRemotePartnerId("123");
        $this->assertEquals("123", $this->partner->getRemotePartnerId());
    }

    /**
     * Test set and get owner
     */
    public function testSetAndGetOwnerId()
    {
        $this->partner->setId(123);
        $this->assertEquals(123, $this->partner->getId());
    }

    /**
     * Test set and get last sync
     */
    public function testSetAndGetLastSync()
    {
        $now = new \DateTime();
        $this->partner->setLastSync($now);
        $this->assertEquals($now, $this->partner->getLastSync());
    }

    public function testSetAndGetLastSync_Formatted()
    {
        $now = new \DateTime();
        $this->partner->setLastSync($now);
        $this->assertEquals($now->format("Y-m-d H:i:s"), $this->partner->getLastSync("Y-m-d H:i:s"));
    }

    /**
     * Check to make sure that getting the last sync on an unset property works as expected
     */
    public function testGetLastSyncNull()
    {
        $this->assertNull($this->partner->getLastSync());
        $this->assertNull($this->partner->getLastSync("Y-m-d H:i:s"));
    }

    /**
     * Adding and getting collections
     */
    public function testAddAndGetCollection()
    {
        // Create a mock collection
        $collection = $this->getMockBuilder(CollectionInterface::class)
            ->getMock();
        // Configure the type to be entity.
        $collection->method('getType')->willReturn(1);


        $this->partner->addCollection($collection);
        $this->assertEquals(1, count($this->partner->getCollections()));
    }

    /**
     * Removing a collection
     */
    public function testRemoveCollection()
    {
        // Create a mock collection
        $collection = $this->getMockBuilder(CollectionInterface::class)
            ->getMock();
        // Configure the type to be entity.
        $collection->method('getType')->willReturn(1);
        // Make site it returns an id so remove will know to store it in a removed array for saving
        $collection->method('getCollectionId')->willReturn(1001);

        // Add the colleciton
        $this->partner->addCollection($collection);
        $this->assertEquals(1, count($this->partner->getCollections()));

        // Remove it and make sure it is logged
        $this->partner->removeCollection($collection->getCollectionId());
        $this->assertEquals(0, count($this->partner->getCollections()));
        $this->assertEquals(1, count($this->partner->getRemovedCollections()));
        $removedArray = $this->partner->getRemovedCollections();
        $this->assertEquals($collection->getCollectionId(), $removedArray[0]);
    }

    public function testGetCollection()
    {
        $conditions = array(
            array(
                "blogic" => "and",
                "field" => "type_id",
                "operator" => "is_equal",
                "condValue" => 1, // person
            ),
        );

        // Create a mock collection
        $collection = $this->getMockBuilder(EntityCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection->method('getType')->willReturn(1);
        $collection->method('getObjType')->willReturn(ObjectTypes::CONTACT);
        $collection->method('getConditions')->willReturn(array());

        // Add the collection
        $this->partner->addCollection($collection);

        // Setup save colleciton reflection object
        $refIm = new \ReflectionObject($this->partner);
        $getCollection = $refIm->getMethod("getCollection");
        $getCollection->setAccessible(true);

        /*
         * Verify that the collectin is not returned when conditions are passed
         */
        $gotColl = $getCollection->invoke($this->partner, ObjectTypes::CONTACT, $conditions);
        $this->assertNull($gotColl);

        /*
         * Verify that collections are correctly gathered with no conditions
         */
        $gotColl = $getCollection->invoke($this->partner, ObjectTypes::CONTACT);
        $this->assertInstanceOf(CollectionInterface::class, $gotColl);
    }

    public function testGetCollectionWithCondition()
    {
        $conditions = array(
            array(
                "blogic" => "and",
                "field" => "type_id",
                "operator" => "is_equal",
                "condValue" => 1, // person
            ),
            array(
                "blogic" => "and",
                "field" => "name",
                "operator" => "is_equal",
                "condValue" => "john",
            ),
        );

        $conditions2 = array(
            array(
                "blogic" => "and",
                "field" => "type_id",
                "operator" => "is_equal",
                "condValue" => 1,
            ),
            array(
                "blogic" => "and",
                "field" => "name",
                "operator" => "is_equal",
                "condValue" => "sky",
            ),
        );

        // Create two mock collections
        $collection = $this->getMockBuilder(EntityCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection->method('getType')->willReturn(1);
        $collection->method('getObjType')->willReturn(ObjectTypes::CONTACT);
        $collection->method('getConditions')->willReturn($conditions);
        $this->partner->addCollection($collection);

        $collection2 = $this->getMockBuilder(EntityCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection2->method('getType')->willReturn(1);
        $collection2->method('getObjType')->willReturn(ObjectTypes::CONTACT);
        $collection2->method('getConditions')->willReturn($conditions2);
        $this->partner->addCollection($collection2);

        // Setup get collection reflection object
        $refIm = new \ReflectionObject($this->partner);
        $getCollection = $refIm->getMethod("getCollection");
        $getCollection->setAccessible(true);

        /*
         * Test with conditions
         */
        $gotColl = $getCollection->invoke($this->partner, ObjectTypes::CONTACT, null, $conditions);
        $this->assertInstanceOf(CollectionInterface::class, $gotColl);

        /*
         * Try same object type with conditions that do not match
         */
        $noMatchConditions = array(
            array(
                "blogic" => "and",
                "field" => "type_id",
                "operator" => "is_equal",
                "condValue" => 2, // account - should not match because the collection is only for type=person
            ),
            array(
                "blogic" => "and",
                "field" => "name",
                "operator" => "is_equal",
                "condValue" => "john",
            ),
        );
        $gotColl = $getCollection->invoke($this->partner, ObjectTypes::CONTACT, null, $noMatchConditions);
        $this->assertNull($gotColl);

        /**
         * Make sure other types of collections do not make a false positive match
         */
        $this->assertNull($getCollection->invoke($this->partner, null, null, $conditions));
        $this->assertNull($getCollection->invoke($this->partner, ObjectTypes::CONTACT, "badfiled", $conditions));
    }
}

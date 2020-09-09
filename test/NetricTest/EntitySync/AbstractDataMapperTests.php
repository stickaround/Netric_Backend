<?php

/**
 * Define common tests that will need to be run with all data mappers.
 *
 * In order to implement the unit tests, a datamapper test case just needs
 * to extend this class and create a getDataMapper class that returns the
 * datamapper to be tested
 */

namespace NetricTest\EntitySync;

use Netric\EntitySync;
use PHPUnit\Framework\TestCase;
use Netric\Entity\ObjType\UserEntity;
use NetricTest\Bootstrap;
use Netric\EntitySync\Partner;
use Netric\EntityDefinition\ObjectTypes;

/**
 * @group integration
 */
abstract class AbstractDataMapperTests extends TestCase
{
    /**
     * Tennant account
     *
     * @var \Netric\Account\Account
     */
    protected $account = null;

    /**
     * Administrative user
     *
     * @var \Netric\User
     */
    protected $user = null;


    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
        $this->user = $this->account->getUser(null, UserEntity::USER_SYSTEM);
    }

    /**
     * Setup datamapper for the parent DataMapperTests class
     *
     * @return DataMapperInterface
     */
    abstract protected function getDataMapper();

    /**
     * Test saving and loading partners
     */
    public function testSaveAndLoadPartner()
    {
        $partnerId = "UTEST-DEVICE-SAVEANDLOAD";
        $dm = $this->getDataMapper();

        // Save the partner
        $partner = new Partner($dm);
        $partner->setRemotePartnerId($partnerId);
        $partner->setOwnerId($this->user->getEntityId());
        $ret = $dm->savePartner($partner, $this->account->getAccountId());
        $this->assertTrue($ret, $dm->getLastError());
        $this->assertNotNull($partner->getId());

        // Load the partner in another object and test
        $partner2 = $dm->getPartnerById($partner->getId(), $this->account->getAccountId());        
        $this->assertEquals($partner->getId(), $partner2->getId());
        $this->assertEquals($partner->getRemotePartnerId(), $partner2->getRemotePartnerId());

        // Cleanup by partner id (second param)
        $dm->deletePartner($partner, $this->account->getAccountId());
    }

    public function testDeletePartner()
    {
        $partnerId = "UTEST-DEVICE-SAVEANDLOAD";
        $dm = $this->getDataMapper();

        // Save the partner
        $partner = new Partner($dm);
        $partner->setRemotePartnerId($partnerId);
        $partner->setOwnerId($this->user->getEntityId());
        $ret = $dm->savePartner($partner, $this->account->getAccountId());

        // Now delete it
        $dm->deletePartner($partner, $this->account->getAccountId());

        // Try to load the partner and verify it was not found
        $partner2 = $dm->getPartnerById($partner->getId(), $this->account->getAccountId());
        $this->assertNull($partner2);
    }

    /**
     * Now test saving and loading an entity collection through the partner
     */
    public function testSaveAndLoadPartnerEntityCollection()
    {
        $partnerId = "UTEST-DEVICE-SAVEANDLOADPARTNERENTITYCOLLECITON";
        $dm = $this->getDataMapper();
        $testConditions = [
            ["blogic" => "and", "field" => "name", "operator" => "is_equal", "condValue" => "test"]
        ];

        // Create a partner
        $partner = new Partner($dm);
        $partner->setRemotePartnerId($partnerId);
        $partner->setOwnerId($this->user->getEntityId());

        // Add a collection
        $collection = $this->getEntityCollection($this->account->getAccountId());
        $collection->setObjType(ObjectTypes::CONTACT);
        $collection->setConditions($testConditions);
        $partner->addCollection($collection);

        // Save the partner
        $dm->savePartner($partner, $this->account->getAccountId());

        // Now load the parter fresh and check the collection
        $partner2 = $dm->getPartnerById($partner->getId(), $this->account->getAccountId());
        $collections = $partner2->getCollections();
        $this->assertEquals(1, count($collections));
        $this->assertEquals($testConditions, $collections[0]->getConditions());
        $this->assertEquals(ObjectTypes::CONTACT, $collections[0]->getObjType());

        // Cleanup by partner id (second param)
        $dm->deletePartner($partner, true , $this->account->getAccountId());
    }

    /*
     * Make sure we can make changes to a collection inside a partner and save
     */
    public function testUpdatePartnerCollection()
    {
        $partnerId = "UTEST-DEVICE-SAVEAUPLOADPARTNERENTITYCOLLECITON";
        $dm = $this->getDataMapper();
        $testConditions = [
            ["blogic" => "and", "field" => "name", "operator" => "is_equal", "condValue" => "test"]
        ];

        // Create a partner
        $partner = new Partner($dm);
        $partner->setRemotePartnerId($partnerId);
        $partner->setOwnerId($this->user->getEntityId());

        // Add a collection
        $collection = $this->getEntityCollection($this->account->getAccountId());
        $collection->setObjType(ObjectTypes::CONTACT);
        $partner->addCollection($collection);

        // Save the partner which should save the collection
        $dm->savePartner($partner, $this->account->getAccountId());

        // Reload the parter fresh and update it
        $partner2 = $dm->getPartnerById($partner->getId(), $this->account->getAccountId());        
        $collections = $partner2->getCollections();        
        $collections[0]->setFieldName("categories");
        $collections[0]->setConditions($testConditions);
        $dm->savePartner($partner2, $this->account->getAccountId());

        // Reload the parter fresh and update it
        $partner3 = $dm->getPartnerById($partner->getId(), $this->account->getAccountId());
        $collections = $partner3->getCollections();
        $this->assertEquals(1, count($collections));
        $this->assertEquals($testConditions, $collections[0]->getConditions());
        $this->assertEquals(ObjectTypes::CONTACT, $collections[0]->getObjType());
        $this->assertEquals("categories", $collections[0]->getFieldName());

        // Cleanup by partner id (second param)
        $dm->deletePartner($partner, $this->account->getAccountId());
    }

    /**
     * Test deleting a collection from the partner
     */
    public function testDeletePartnerCollection()
    {
        $partnerId = "UTEST-DEVICE-SAVEANDLOADPARTNERENTITYCOLLECITON";
        $dm = $this->getDataMapper();

        // Create a partner
        $partner = new Partner($dm);
        $partner->setRemotePartnerId($partnerId);
        $partner->setOwnerId($this->user->getEntityId());

        // Add a collection and save
        $collection = $this->getEntityCollection($this->account->getAccountId());
        //$collection->setPartnerId($partner->getId());
        $collection->setObjType(ObjectTypes::CONTACT);
        $partner->addCollection($collection);
        $dm->savePartner($partner, $this->account->getAccountId());

        // Now load the parter and delete the collection
        $partner2 = $dm->getPartnerById($partner->getId(), $this->account->getAccountId());
        $collections = $partner2->getCollections();
        $partner2->removeCollection($collections[0]->getCollectionId());
        $dm->savePartner($partner2, $this->account->getAccountId());

        // Load it once more and make sure there are no collections
        $partner3 = $dm->getPartnerById($partner->getId(), $this->account->getAccountId());
        $this->assertEquals(0, count($partner3->getCOllections()));

        // Cleanup by partner id (second param)
        $dm->deletePartner($partner, $this->account->getAccountId());
    }

    public function testLogExportedCommit()
    {
        $partnerId = "UTEST-DEVICE-SAVEANDLOADPARTNERENTITYCOLLECITON";
        $uniqueId = '5eb4d21c-1234-4c1a-be72-4524b4711455';
        $commitId1 = 1;

        $dm = $this->getDataMapper();

        // Create a partner
        $partner = new Partner($dm);
        $partner->setRemotePartnerId($partnerId);
        $partner->setOwnerId($this->user->getEntityId());

        // Add a collection and save
        $collection = $this->getEntityCollection($this->account->getAccountId());
        //$collection->setPartnerId($partner->getId());
        $collection->setObjType(ObjectTypes::CONTACT);
        $partner->addCollection($collection);
        $dm->savePartner($partner, $this->account->getAccountId());

        // Add new exported entry
        $ret = $dm->logExported($collection->getAccountId(), $collection->getType(), $collection->getCollectionId(), $uniqueId, $commitId1);
        $this->assertEquals($ret, 1);

        // Cleanup by partner id (second param)
        $dm->deletePartner($partner, $this->account->getAccountId());
    }

    public function testSetAndGetExportedCommitStale()
    {
        $partnerId = "UTEST-DEVICE-SAVEANDLOADPARTNERENTITYCOLLECITON";
        $uniqueId = '5eb4d21c-1234-4c1a-be72-4524b4711455';
        $commitId1 = 1;
        $commitId2 = 2;

        $dm = $this->getDataMapper();

        // Create a partner
        $partner = new Partner($dm);
        $partner->setRemotePartnerId($partnerId);
        $partner->setOwnerId($this->user->getEntityId());

        // Add a collection and save
        $collection = $this->getEntityCollection($this->account->getAccountId());
        $collection->setObjType(ObjectTypes::CONTACT);
        $partner->addCollection($collection);
        $dm->savePartner($partner, $this->account->getAccountId());

        // Add new exported entry then mark it as stale
        $dm->logExported($collection->getAccountId(), $collection->getType(), $collection->getCollectionId(), $uniqueId, $commitId1);
        $dm->setExportedStale($collection->getAccountId(), $collection->getType(), $commitId1, $commitId2);

        // Make sure the stale stat is returned when called
        $staleStats = $collection->getExportedStale();
        $this->assertEquals(1, count($staleStats));
        $this->assertEquals($uniqueId, $staleStats[0]['id']);

        // Cleanup by partner id (second param)
        $dm->deletePartner($partner, $this->account->getAccountId());
    }
}

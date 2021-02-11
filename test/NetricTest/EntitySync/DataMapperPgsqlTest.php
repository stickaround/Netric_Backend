<?php

/**
 * Test entity definition loader class that is responsible for creating and initializing exisiting definitions
 */

namespace NetricTest\EntitySync;

use Netric\EntitySync;
use Netric\EntitySync\DataMapperRdb;
use Netric\EntitySync\DataMapperFactory;
use Netric\EntitySync\Collection\EntityCollectionFactory;
use Netric\EntityDefinition\ObjectTypes;

/**
 * @group integration
 * @group integration-pgsql
 */
class DataMapperPgsqlTest extends AbstractDataMapperTests
{
    /**
     * Setup datamapper
     *
     * @return DataMapperInterface
     */
    protected function getDataMapper()
    {
        return $this->account->getServiceManager()->get(DataMapperFactory::class);
    }

    /**
     * Setup entity collection
     *
     * @return CollectionInterface
     */
    protected function getEntityCollection(string $accountId)
    {
        $entityCollection = $this->account->getServiceManager()->get(EntityCollectionFactory::class);
        $entityCollection->setAccountId($accountId);

        return $entityCollection;
    }

    /**
     * Test construction
     */
    public function testConstruct()
    {
        $dm = $this->getDataMapper();
        $this->assertInstanceOf(DataMapperRdb::class, $dm);
    }

    /**
     * In the pgsql datamapper save and delete individual collections
     * are handled as private helper functions to saving partners.
     *
     * Other datamappers will probably implement this differently depending
     * on how they manage relationships. For example, a document store will
     * probably just embed the collections into the partner object.
     */
    public function testSaveAndDeleteCollection()
    {
        $dm = $this->getDataMapper();

        // Setup save colleciton reflection object
        $refIm = new \ReflectionObject($dm);
        $saveCollection = $refIm->getMethod("saveCollection");
        $saveCollection->setAccessible(true);

        // Save a the partner because it is required for saving a colleciton
        $partner = new EntitySync\Partner($dm);
        $partner->setRemotePartnerId("UTEST-DEVICE-SAVEANDLOAD");
        $partner->setOwnerId($this->user->getEntityId());
        $ret = $dm->savePartner($partner, $this->account->getAccountId());

        // Create a new collection and save it
        $collection = $this->getEntityCollection($this->account->getAccountId());
        $collection->setPartnerId($partner->getId());
        $collection->setObjType(ObjectTypes::CONTACT);

        $ret = $saveCollection->invoke($dm, $collection, $this->account->getAccountId());
        $this->assertTrue($ret, $dm->getLastError());
        $this->assertNotNull($collection->getCollectionId());
        $this->assertEquals(ObjectTypes::CONTACT, $collection->getObjType());

        // Save changes to a collection
        $collection->setObjType(ObjectTypes::TASK);
        $ret = $saveCollection->invoke($dm, $collection, $this->account->getAccountId());
        $this->assertTrue($ret, $dm->getLastError());
        $this->assertNotNull($collection->getCollectionId());
        $this->assertEquals(ObjectTypes::TASK, $collection->getObjType());

        // Cleanup by partner id (second param)
        $dm->deletePartner($partner, $this->account->getAccountId());

        $deleteCollection = $refIm->getMethod("deleteCollection");
        $deleteCollection->setAccessible(true);
        $ret = $deleteCollection->invoke($dm, $collection->getCollectionId(), $collection->getCollectionId());
        $this->assertTrue($ret, $dm->getLastError());
    }
}

<?php

/**
 * Test entity definition loader class that is responsible for creating and initializing exisiting definitions
 */

namespace NetricTest\EntitySync;

use Netric\EntitySync;
use Netric\EntitySync\DataMapperRdb;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntitySync\Commit\CommitManagerFactory;
use Netric\EntitySync\Collection\EntityCollection;
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
        $database = $this->account->getServiceManager()->get(RelationalDbFactory::class);
        return new DataMapperRdb($this->account, $database);
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
        $partner->setPartnerId("UTEST-DEVICE-SAVEANDLOAD");
        $partner->setOwnerId($this->user->getEntityId());
        $ret = $dm->savePartner($partner);

        // Create a new collection and save it
        $index = $this->account->getServiceManager()->get(IndexFactory::class);
        $commitManager = $this->account->getServiceManager()->get(CommitManagerFactory::class);
        $collection = new EntityCollection($dm, $commitManager, $index);
        $collection->setPartnerId($partner->getId());
        $collection->setObjType(ObjectTypes::CONTACT);

        $ret = $saveCollection->invoke($dm, $collection);
        $this->assertTrue($ret, $dm->getLastError());
        $this->assertNotNull($collection->getCollectionId());
        $this->assertEquals(ObjectTypes::CONTACT, $collection->getObjType());

        // Save changes to a collection
        $collection->setObjType(ObjectTypes::TASK);
        $ret = $saveCollection->invoke($dm, $collection);
        $this->assertTrue($ret, $dm->getLastError());
        $this->assertNotNull($collection->getCollectionId());
        $this->assertEquals(ObjectTypes::TASK, $collection->getObjType());

        // Cleanup by partner id (second param)
        $dm->deletePartner($partner);

        $deleteCollection = $refIm->getMethod("deleteCollection");
        $deleteCollection->setAccessible(true);
        $ret = $deleteCollection->invoke($dm, $collection->getCollectionId());
        $this->assertTrue($ret, $dm->getLastError());
    }
}

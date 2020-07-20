<?php

/**
 * Common Collection Tests
 */

namespace NetricTest\EntitySync\Collection;

use PHPUnit\Framework\TestCase;
use Netric\EntitySync;
use NetricTest\Bootstrap;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntitySync\DataMapperFactory;
use Netric\EntitySync\Commit\CommitManagerFactory;
use Netric\EntitySync\Partner;
use Netric\EntitySync\Collection\CollectionInterface;

/*
 * @group integration
 */

abstract class AbstractCollectionTests extends TestCase
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
     * @var \Netric\User
     */
    protected $user = null;

    /**
     * EntitySync DataMapper
     *
     * @var \Netric\EntitySync\DataMapperInterface
     */
    protected $esDataMapper = null;

    /**
     * Commit manager
     *
     * @var \Netric\EntitySync\Commit\CommitManager
     */
    protected $commitManager = null;

    /**
     * Test partner
     *
     * @var EntitySync\Partner
     */
    protected $partner = null;


    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
        $this->user = $this->account->getUser(null, UserEntity::USER_SYSTEM);
        $this->esDataMapper = $this->account->getServiceManager()->get(DataMapperFactory::class);
        $this->commitManager = $this->account->getServiceManager()->get(CommitManagerFactory::class);

        // Create a new partner
        $this->partner = new Partner($this->esDataMapper);
        $this->partner->setPartnerId("AbstractCollectionTests");
        $this->partner->setOwnerId($this->user->getEntityId());
        $this->esDataMapper->savePartner($this->partner);
    }

    protected function tearDown(): void
    {
        $this->deleteLocal();

        // Cleanup partner
        $this->esDataMapper->deletePartner($this->partner);
    }

    /**
     * Get a collection object to perform common tests
     *
     * @return CollectionInterface
     */
    abstract protected function getCollection();

    /**
     * Create a new local object
     *
     * @return array('id', 'revision')
     */
    abstract protected function createLocal();

    /**
     * Change a local object
     *
     * @param $id
     */
    abstract protected function changeLocal($id);

    /**
     * Delete a local object or objects
     *
     * @param null $id If no $id is passed then delete all local objects (cleanup)
     */
    abstract protected function deleteLocal($id = null);

    /**
     * Make sure we can construct this colleciton
     */
    public function testConstruct()
    {
        $coll = $this->getCollection();

        $this->assertInstanceOf(CollectionInterface::class, $coll);
    }

    /**
     * Test to make sure the collection returns a valid type
     */
    public function testGetType()
    {
        $coll = $this->getCollection();
        $this->assertTrue($coll->getType() > 0);
    }

    /**
     * Make sure we can set and get the last commit id
     */
    public function testSetAndGetLastCommitId()
    {
        $coll = $this->getCollection();
        $coll->setLastCommitId(123);
        $this->assertEquals(123, $coll->getLastCommitId());
    }

    public function testSetAndGetId()
    {
        $coll = $this->getCollection();
        $coll->setId(123);
        $this->assertEquals(123, $coll->getId());
    }

    public function testSetAndGetPartnerId()
    {
        $coll = $this->getCollection();
        $coll->setPartnerId(123);
        $this->assertEquals(123, $coll->getPartnerId());
    }

    public function testSetAndGetObjType()
    {
        $coll = $this->getCollection();
        $coll->setObjType("customer");
        $this->assertEquals("customer", $coll->getObjType());
    }

    public function testSetAndGetFieldName()
    {
        $coll = $this->getCollection();
        $coll->setFieldName("groups");
        $this->assertEquals("groups", $coll->getFieldName());
    }

    public function testSetAndGetLastSync()
    {
        $now = new \DateTime();
        $coll = $this->getCollection();
        $coll->setLastSync($now);
        $this->assertEquals($now, $coll->getLastSync());
        $this->assertEquals($now->format("Y-m-d H:i:s"), $coll->getLastSync("Y-m-d H:i:s"));
    }

    public function testSetAndGetRevision()
    {
        $coll = $this->getCollection();
        $coll->setRevision(1);
        $this->assertEquals(1, $coll->getRevision());
    }

    public function testSetAndGetConditions()
    {
        $conditions = array(
            array("blogic" => "and", "field" => "groups", "operator" => "is_equal", "condValue" => 1)
        );

        $coll = $this->getCollection();
        $coll->setConditions($conditions);
        $this->assertEquals($conditions, $coll->getConditions());
    }

    /**
     * Test importing objects from a remote source/device
     */
    public function testGetImportChanged()
    {
        // Setup collection
        $collection = $this->getCollection();

        // Create and save partner with one collection watching customers
        $partner = new Partner($this->esDataMapper);
        $partner->setPartnerId("AbstractCollectionTests::testGetImportChanged");
        $partner->setOwnerId($this->user->getEntityId());
        $partner->addCollection($collection);
        $this->esDataMapper->savePartner($partner);

        // Import original group of changes
        $customers = array(
            array('remote_id' => 'test1', 'remote_revision' => 1),
            array('remote_id' => 'test2', 'remote_revision' => 1),
        );
        $stats = $collection->getImportChanged($customers);
        $this->assertEquals(count($stats), count($customers));
        foreach ($stats as $ostat) {
            $this->assertEquals('change', $ostat['action']);
            $collection->logImported($ostat['remote_id'], $ostat['remote_revision'], 1001, 1);
        }

        // Try again with no changes
        $stats = $collection->getImportChanged($customers);
        $this->assertEquals(count($stats), 0);

        // Change the revision of one of the objects
        $customers = array(
            array('remote_id' => 'test1', 'remote_revision' => 2),
            array('remote_id' => 'test2', 'remote_revision' => 1),
        );
        $stats = $collection->getImportChanged($customers);
        $this->assertEquals(count($stats), 1);

        // Remove one of the objects
        $customers = array(
            array('remote_id' => 'test2', 'remote_revision' => 1),
        );
        $stats = $collection->getImportChanged($customers);
        $this->assertEquals(count($stats), 1);
        $this->assertEquals($stats[0]['action'], 'delete');

        // Change both revisions
        $customers = array(
            array('remote_id' => 'test1', 'remote_revision' => 2),
            array('remote_id' => 'test2', 'remote_revision' => 2),
        );
        $stats = $collection->getImportChanged($customers);
        $this->assertEquals(count($stats), 2);
        $this->assertEquals($stats[0]['action'], 'change');
        $this->assertEquals($stats[1]['action'], 'change');

        // Cleanup
        $this->esDataMapper->deletePartner($partner);
    }

    /**
     * Test getting changed objects
     */
    public function testGetExportChanged()
    {
        // Create and save partner with one collection watching customers
        $collection = $this->getCollection();
        $this->partner->addCollection($collection);
        $this->esDataMapper->savePartner($this->partner);
        $collection->fastForwardToHead();

        // Create a local object to work with
        $localData = $this->createLocal();
        $localId = $localData['id'];

        // Initial pull should start with all objects
        $stats = $collection->getExportChanged();
        $this->assertTrue(count($stats) >= 1);


        // Should be no changes now
        $stats = $collection->getExportChanged();
        $this->assertEquals(0, count($stats));

        // Change the local object
        $this->changeLocal($localId);

        // Make sure the one change is now returned
        $stats = $collection->getExportChanged();
        $this->assertTrue(count($stats) >= 1);
        $this->assertEquals($localId, $stats[0]['id'], var_export($stats, true));
    }

    /**
     * Make sure we do not export imports because that will cause an infinite loop
     */
    public function testNotExportImport()
    {
        // Create and save partner with one collection watching customers
        $collection = $this->getCollection();
        $this->partner->addCollection($collection);
        $this->esDataMapper->savePartner($this->partner);
        $collection->fastForwardToHead();

        // Import original group of changes
        $customers = array(
            array('remote_id' => 'test1', 'remote_revision' => 1),
            array('remote_id' => 'test2', 'remote_revision' => 1),
        );
        $stats = $collection->getImportChanged($customers);
        $this->assertEquals(count($stats), count($customers));
        foreach ($stats as $ostat) {
            $newData = $this->createLocal();
            $collection->logImported(
                $ostat['remote_id'],
                $ostat['remote_revision'],
                $newData['id'],
                $newData['revision']
            );
        }

        // Now pull export changes which should be 0
        $stats = $collection->getExportChanged();
        $this->assertEquals(0, count($stats));

        // Make a change after the import
        $localData = $this->createLocal();
        $localId = $localData['id'];
        // Make sure the one change is now returned
        $stats = $collection->getExportChanged();
        $this->assertEquals(1, count($stats));
        $this->assertEquals($stats[0]['id'], $localId);
    }
}

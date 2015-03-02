<?php
/**
 * Test entity/object class
 */
namespace NetricTest\EntitySync\Collection;

use Netric\EntitySync;
use Netric\EntitySync\Collection;
use PHPUnit_Framework_TestCase;

class GroupingCollectionTest extends AbstractCollectionTests 
{
    /**
     * Entity DataMapper
     *
     * @var \Netric\Entity\DataMapperInterface
     */
    private $entityDataMapper = null;

    /**
     * Test partner id
     *
     * @var string
     */
    private $partner = null;

    /**
     * Cleanup
     */
    protected function tearDown()
    {
        // Make sure we dont override parent teardonw
        parent::tearDown();

        if ($this->partner)
        {
            $this->esDataMapper->deletePartner($this->partner, true);
        }

    }
    
    /**
     * Required by AbstractCollectionTests
     */
	protected function getCollection()
	{
		$this->entityDataMapper = $this->account->getServiceManager()->get("Entity_DataMapper");
		return new Collection\GroupingCollection($this->esDataMapper, $this->commitManager, $this->entityDataMapper);
	}

	/**
     * Test getting changed objects for this collection
     */
    public function testGetExportChanged() 
    {
        // Setup collection
        $collection = $this->getCollection();
        $collection->setObjType("customer");
        $collection->setFieldName("groups");

		// Create and save partner with one collection watching customers
		$this->partner = new EntitySync\Partner($this->esDataMapper);
        $this->partner->setPartnerId("GroupingCollectionTest::testGetExportChanged");
        $this->partner->setOwnerId($this->user->getId());
        $this->esDataMapper->savePartner($this->partner);

        $dm = $this->entityDataMapper;

        // Create the grouping below
        $groupings = $dm->getGroupings("customer", "groups");
        $newGroup = $groupings->create();
        $newGroup->name = "UTTEST CS::testGetExportChanged";
        $groupings->add($newGroup);
        $dm->saveGroupings($groupings);
        $group1 = $groupings->getByName("UTTEST CS::testGetExportChanged");

        // Fast forward past all previous groupings
        $collection->fastForwardToHead();

        // Should be no changes now, we have to loop over to check
        $stats = $collection->getExportChanged();
        $this->assertEquals(0, count($stats));

        // Record a change to the grouping
        $group1->name = "UTTEST CS::testGetExportChanged2";
        $group1->setDirty(true);
        $dm->saveGroupings($groupings);

		// Make sure the one change is now returned
        $stats = $collection->getExportChanged();
        $found = false;
        foreach ($stats as $stat)
        {
            if ($stat["id"] == $group1->id)
                $found = true;
        }
        $this->assertTrue($found);

        // Cleanup
        $groupings->delete($group1->id);
        $dm->saveGroupings($groupings);
    }

    /**
     * Make sure we can detect when an entity has been deleted
     */
    public function testGetExportChanged_Deleted() 
    {
        // Setup collection 
        $collection = $this->getCollection();
        $collection->setObjType("customer");
        $collection->setFieldName("groups");

        // Create and save partner with one collection watching customers
        $this->partner = new EntitySync\Partner($this->esDataMapper);
        $this->partner->setPartnerId("GroupingCollectionTest::testGetExportChanged_Deleted");
        $this->partner->setOwnerId($this->user->getId());
        $this->partner->addCollection($collection);
        $this->esDataMapper->savePartner($this->partner);

        $dm = $this->entityDataMapper;

        // Create the grouping below
        $groupings = $dm->getGroupings("customer", "groups");
        $newGroup = $groupings->create();
        $newGroup->name = "UTTEST CS::testGetExportChanged_Deleted";
        $groupings->add($newGroup);
        $dm->saveGroupings($groupings);
        $group1 = $groupings->getByName("UTTEST CS::testGetExportChanged_Deleted");

        // Get exported which will cause the customer to be logged
        $stats = $collection->getExportChanged();

        // Fast forward past all previous groupings
        $collection->fastForwardToHead();

        // Should be no changes now, we have to loop over to check
        $stats = $collection->getExportChanged();
        $this->assertEquals(0, count($stats));

        // Delete the grouping
        $groupings->delete($group1->id);
        $dm->saveGroupings($groupings);

        // Make sure the one change is now returned
        $stats = $collection->getExportChanged();
        $foundStat = null;
        foreach ($stats as $stat)
        {
            if ($stat["id"] == $group1->id)
                $foundStat = $stat;
        }
        $this->assertNotNull($foundStat);
        $this->assertEquals("delete", $foundStat['action']);

        // Make sure a second call does not get the same stale id
        $stats = $collection->getExportChanged();
        $foundStat = null;
        foreach ($stats as $stat)
        {
            if ($stat["id"] == $group1->id)
                $foundStat = $stat;
        }
        $this->assertNull($foundStat);
    }
}
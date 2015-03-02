<?php
/**
 * Test entity/object class
 */
namespace NetricTest\EntitySync\Collection;

use Netric\EntitySync;
use Netric\EntitySync\Collection;
use PHPUnit_Framework_TestCase;

class EntityCollectionTest extends AbstractCollectionTests 
{
	protected function getCollection()
	{
		$index = $this->account->getServiceManager()->get("EntityQuery_Index");
		return new Collection\EntityCollection($this->esDataMapper, $this->commitManager, $index);
	}

	/**
     * Test getting changed objects for this collection
     */
    public function testGetExportChanged() 
    {
        $pid = "AntObjectSync_CollectionTest::testGetChangedObjects";

        // Create customer just in case there are none already in the database
        $customer = $this->account->getServiceManager()->get("EntityLoader")->create("customer");
		$customer->setValue("name", "EntityEyncTests");
		$this->account->getServiceManager()->get("Entity_DataMapper")->save($customer);
		
		// Create and save partner with one collection watching customers
		$partner = new EntitySync\Partner($this->esDataMapper);
        $partner->setPartnerId($pid);
        $partner->setOwnerId($this->user->getId());
        $collection = $this->getCollection();
        $collection->setObjType("customer");
        $this->esDataMapper->savePartner($partner);

        // Initial pull should start with all objects
        $stats = $collection->getExportChanged();
        $this->assertTrue(count($stats) >= 1);
        $collection->fastForwardToHead();

        // Should be no changes now
        $stats = $collection->getExportChanged();
        $this->assertEquals(0, count($stats));

        // Record object change
        $customer->setValue("name", "EntityEyncTests_2");
		$this->account->getServiceManager()->get("Entity_DataMapper")->save($customer);

		// Make sure the one change is now returned
        $stats = $collection->getExportChanged();
        $this->assertTrue(count($stats) >= 1);
        $this->assertEquals($stats[0]['id'], $customer->getId());

        // Cleanup
        $this->esDataMapper->deletePartner($partner, true);
        $this->account->getServiceManager()->get("Entity_DataMapper")->delete($customer, true);
    }

    /**
     * Make sure we can detect when an entity has been deleted
     */
    public function testGetExportChanged_Deleted() 
    {
        $pid = "AntObjectSync_CollectionTest::testGetChangedObjects";

        // Create customer just in case there are none already in the database
        $customer = $this->account->getServiceManager()->get("EntityLoader")->create("customer");
		$customer->setValue("name", "EntityEyncTests");
		$this->account->getServiceManager()->get("Entity_DataMapper")->save($customer);
        $customerId = $customer->getId();
		
		// Create and save partner with one collection watching customers
		$partner = new EntitySync\Partner($this->esDataMapper);
        $partner->setPartnerId($pid);
        $partner->setOwnerId($this->user->getId());
        $collection = $this->getCollection();
        $collection->setObjType("customer");
        $partner->addCollection($collection);
        $this->esDataMapper->savePartner($partner);

        // Get all exported which will cause the customer to be logged
        while (count($stats = $collection->getExportChanged())) {}

        // Fast-forward past the created customer
        $collection->fastForwardToHead();
        $stats = $collection->getExportChanged();
        $this->assertEquals(0, count($stats));

        // Soft delete the customer
        $this->account->getServiceManager()->get("Entity_DataMapper")->delete($customer);

		// Make sure the one change is now returned for the deleted item
        $stats = $collection->getExportChanged();
        $this->assertEquals(1, count($stats));
        $this->assertEquals($customerId, $stats[0]['id']);
        $this->assertEquals("delete", $stats[0]['action']);

        // Make sure a next call does not return the stale item again
        $stats = $collection->getExportChanged();
        $this->assertEquals(0, count($stats));

        // Cleanup
        $this->esDataMapper->deletePartner($partner, true);
        $this->account->getServiceManager()->get("Entity_DataMapper")->delete($customer, true);
    }

}

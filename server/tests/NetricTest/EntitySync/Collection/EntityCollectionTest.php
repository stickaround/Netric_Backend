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

        // Get exported which will cause the customer to be logged
        $stats = $collection->getExportChanged();

        // Fast-forward past the created customer
        $collection->fastForwardToHead();
        $stats = $collection->getExportChanged();
        $this->assertEquals(0, count($stats));

        // Soft delete the customer
        $this->account->getServiceManager()->get("Entity_DataMapper")->delete($customer);

		// Make sure the one change is now returned
        $stats = $collection->getExportChanged();
        $this->assertTrue(count($stats) >= 1);
        $this->assertEquals($customerId, $stats[0]['id']);
        $this->assertEquals("delete", $stats[0]['action']);

        // Cleanup
        $this->esDataMapper->deletePartner($partner, true);
        $this->account->getServiceManager()->get("Entity_DataMapper")->delete($customer, true);
    }

    /**
     * Get getting changed objects for this collection using heiarchy
     */
    public function testGetExportChangedHeiarch() 
    {
    	/*
        $pid = "AntObjectSync_CollectionTest::testGetChangedObjectsHeiarch";

        // Create folder and file
        $antfs = new AntFs($this->dbh, $this->user);
        $fldr = $antfs->openFolder("/tests/testGetChangedObjectsHeiarch", true);
        $this->assertNotNull($fldr);
        $file = $fldr->openFile("testsync", true);
        $this->assertNotNull($file);

        // Add file to collection
        $partner = new AntObjectSync_Partner($this->dbh, $pid, $this->user);
        $coll = $partner->addCollection("file");
        $coll->fInitialized = true; // Restrain pulling all existing files for performance reasons
        $ret = $coll->updateObjectStat($file, 'c');

        // Now try to pull with wrong parent id (folder 2)
        $fldr2 = $antfs->openFolder("/tests/testGetChangedObjectsHeiarch/F2", true);
        $stats = $coll->getChangedObjects($fldr2->id);
        $this->assertEquals(count($stats), 0);

        // Try to pull with right parent id (folder 1)
        $stats = $coll->getChangedObjects($fldr->id);
        $this->assertEquals(count($stats), 1);

        // Cleanup
        $coll->remove();
        $partner->remove();
        $fldr2->removeHard();
        $file->removeHard();
        $fldr->removeHard();
        */
    }

    /**
     * Test moving with a heiarchy - should add a delete entry for old parent
     */
    public function testGetExportChangedHeiarchMoved() 
    {
    	/*
        $pid = "AntObjectSync_CollectionTest::testMovedObjectsHeiarch";

        // Create folder and file
        $antfs = new AntFs($this->dbh, $this->user);
        $fldr = $antfs->openFolder("/tests/testGetChangedObjectsHeiarch", true);
        $this->assertNotNull($fldr);
        $file = $fldr->openFile("testsync", true);
        $this->assertNotNull($file);
        $fldr2 = $antfs->openFolder("/tests/testGetChangedObjectsHeiarch/F2", true);

        // Add file to collection
        $partner = new AntObjectSync_Partner($this->dbh, $pid, $this->user);
        $coll = $partner->addCollection("file");
        $coll->fInitialized = true; // Restrain pulling all existing files for performance reasons
        $partner->save();
        $ret = $coll->updateObjectStat($file, 'c'); // stat initial file

        // Now move to new folder which should create two stat entries
        $file->move($fldr2);

        // Pull changes from new folder and look for add/change
        $stats = $coll->getChangedObjects($fldr2->id);
        $found = false;
        foreach ($stats as $stat)
        {
            if ($stat['id'] == $file->id && $stat['action'] == 'change')
                $found = true;
        }
        $this->assertTrue($found);

        // Pull changes from old folder and look for delete
        $stats = $coll->getChangedObjects($fldr->id);
        $found = false;
        foreach ($stats as $stat)
        {
            if ($stat['id'] == $file->id && $stat['action'] == 'delete')
                $found = true;
        }
        $this->assertTrue($found);


        // Cleanup
        $coll->remove();
        $partner->remove();
        $fldr2->removeHard();
        $file->removeHard();
        $fldr->removeHard();
        */
    }

    /**
     * Get getting changed objects for this collection using heiarchy and folders
     *
    public function testGetChangedObjectsHeiarchFldr() 
    {
        $pid = "AntObjectSync_CollectionTest::testGetChangedObjectsHeiarch";

        // Create folder and file
        $antfs = new AntFs($this->dbh, $this->user);
        $fldr = $antfs->openFolder("/tests/testGetChangedObjectsHeiarch", true);
        $this->assertNotNull($fldr);
        $file = $fldr->openFile("testsync", true);
        $this->assertNotNull($file);

        // Add file to collection
        $partner = new AntObjectSync_Partner($this->dbh, $pid, $this->user);
        $coll = $partner->addCollection("file");
        $coll->fInitialized = true; // Restrain pulling all existing files for performance reasons
        $ret = $coll->updateObjectStat($file, 'c');

        // Now try to pull with wrong parent id (folder 2)
        $fldr2 = $antfs->openFolder("/tests/testGetChangedObjectsHeiarch/F2", true);
        $stats = $coll->getChangedObjects($fldr2->id);
        $this->assertEquals(count($stats), 0);

        // Try to pull with right parent id (folder 1)
        $stats = $coll->getChangedObjects($fldr->id);
        $this->assertEquals(count($stats), 1);

        // Cleanup
        $coll->remove();
        $partner->remove();
        $fldr2->removeHard();
        $file->removeHard();
        $fldr->removeHard();
    }
    */
    

    /**
     * Test moving with a heiarchy - should add a delete entry for old parent
     *
     * @group testMovedObjectsHeiarchEmail
     *
    public function testMovedObjectsHeiarchEmail() 
    {
        $pid = "AntObjectSync_CollectionTest::testMovedObjectsHeiarchEmail";

        // Create folder and file
        $obj = CAntObject::factory($this->dbh, "email_message", null, $this->user);
        $grp1 = $obj->addGroupingEntry("mailbox_id", "testMovedObjectsHeiarchEmail1");
        $grp2 = $obj->addGroupingEntry("mailbox_id", "testMovedObjectsHeiarchEmail2");
        $obj->setValue("subject", "My test email");
        $obj->setValue("mailbox_id", $grp1['id']);
        $mid = $obj->save();

        // Add message to collection
        $partner = new AntObjectSync_Partner($this->dbh, $pid, $this->user);
        $coll = $partner->addCollection("email_message");
        $coll->fInitialized = true; // Restrain pulling all existing messages for performance reasons
        $partner->save();
        $ret = $coll->updateObjectStat($obj, 'c'); // stat initial stat

        // Now move to new folder which should create two stat entries
        $obj->setValue("mailbox_id", $grp2['id']);
        $mid = $obj->save();

        // Pull changes from new folder and look for add/change
        $stats = $coll->getChangedObjects($grp2['id']);
        $found = false;
        foreach ($stats as $stat)
        {
            if ($stat['id'] == $obj->id && $stat['action'] == 'change')
                $found = true;
        }
        $this->assertTrue($found);

        // Pull changes from old folder and look for delete
        $stats = $coll->getChangedObjects($grp1['id']);
        $found = false;
        foreach ($stats as $stat)
        {
            if ($stat['id'] == $obj->id && $stat['action'] == 'delete')
                $found = true;
        }
        $this->assertTrue($found);


        // Cleanup
        $coll->remove();
        $partner->remove();
        $obj->deleteGroupingEntry("groups", $grp1['id']);
        $obj->deleteGroupingEntry("groups", $grp2['id']);
        $obj->removeHard();
    }
    */
}

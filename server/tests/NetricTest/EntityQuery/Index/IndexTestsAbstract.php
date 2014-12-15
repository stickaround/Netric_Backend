<?php
/**
 * Define common tests that will need to be run with all data mappers.
 *
 * In order to implement the unit tests, a datamapper test case just needs
 * to extend this class and create a getDataMapper class that returns the
 * datamapper to be tested
 */
namespace NetricTest\EntityQuery\Index;

use Netric;
use PHPUnit_Framework_TestCase;

abstract class IndexTestsAbstract extends PHPUnit_Framework_TestCase 
{
    /**
     * Tennant account
     * 
     * @var \Netric\Account
     */
    protected $account = null;

	/**
	 * Setup each test
	 */
	protected function setUp() 
	{
        $this->account = \NetricTest\Bootstrap::getAccount();
	}
    
    /**
     * Required by all derrieved classes
     * 
     * @return \Netric\EnittyQuery\Index\IndexInterface The setup index to query
     */
    abstract protected function getIndex();
    
    /**
     * Create a test customer
     */
    protected function createTestCustomer()
    {
        $uniName = "utestequals." . uniqid();
        
        // Save a test object
        $dm = $this->account->getServiceManager()->get("Entity_DataMapper");
        $obj = $this->account->getServiceManager()->get("EntityLoader")->create("customer");
        $obj->setValue("name", $uniName);
        $obj->setValue("f_nocall", true);
        $obj->setValue("type_id", 2); // Organization
        // Status id
        $statusG = $this->createGrouping("customer", "status_id", "Unit Test Status");
        $obj->setValue("status_id", $statusG['id'], $statusG['name']);
        $obj->setValue("last_contacted", time());
        // Groups
		$groupsG = $this->createGrouping("customer", "groups", "Unit Test Group");
        $obj->addMultiValue("groups", $groupsG['id'], $groupsG['name']);
        $oid = $dm->save($obj);
        
        return $obj;
    }
    
    /**
     * Delete a test customer
     */
    protected function deleteTestCustomer($ent)
    {
        // Clearn groupings
        $this->deleteGrouping("customer", "status_id", $ent->getValue("status_id"));
        $groupings = $ent->getValue("groups");
        $this->deleteGrouping("customer", "groups", $groupings[0]);
        
        /// Save a test object
        $dm = $this->account->getServiceManager()->get("Entity_DataMapper");
        $dm->delete($ent, true);
    }
    
    /**
     * Create an object grouping entry for testing
     * 
     * @param string $objType
     * @param string $field
     * @param string $name
     * @return array("id", "name")
     */
    protected function createGrouping($objType, $field, $name, $parent=null)
    {
        $dm = $this->account->getServiceManager()->get("Entity_DataMapper");
        $groupings = $dm->getGroupings($objType, $field);
        $group = $groupings->create($name);
        if ($parent)
            $group->parentId = $parent;
        $groupings->add($group);
        $dm->saveGroupings($groupings);
        $group = $groupings->getByName($name, $parent);
        
        return $group->toArray();
    }

    /**
     * Delete an object grouping
     * 
     * @param seting $objType
     * @param string $field
     * @param int $id
     */
    protected function deleteGrouping($objType, $field, $id)
    {
        $dm = $this->account->getServiceManager()->get("Entity_DataMapper");
        $groupings = $dm->getGroupings($objType, $field);
        $groupings->delete($id);
        $dm->saveGroupings($groupings);
    }
    
    /**
     * Run test of is equal conditions
     */
    public function testWhereFullText()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
            //$this->assertTrue(false, "Index could not be setup!");
        
        // Save a test object
        $testEnt = $this->createTestCustomer();
        
        // Query value
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('*')->fullText($testEnt->getValue("name"));
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getId(), $obj->getId());
               
        // Cleanup
        $this->deleteTestCustomer($testEnt);
    }
    
    /**
     * Run test of is equal conditions
     */
    public function testWhereEqualsText()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
            //$this->assertTrue(false, "Index could not be setup!");
        
        // Save a test object
        $testEnt = $this->createTestCustomer();
        
        // Query value
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('name')->equals($testEnt->getValue("name"));
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getId(), $obj->getId());
        
        // Query null - first name is not set
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('first_name')->equals(null);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
               
        // Cleanup
        $this->deleteTestCustomer($testEnt);
    }
    
    /**
     * Run test of is equal conditions
     */
    public function testWhereEqualsNumber()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
            //$this->assertTrue(false, "Index could not be setup!");
        
        $uniName = "utestequals." . uniqid();
        
        // Save a test object
        $testEnt = $this->createTestCustomer();
        
        // Test with number
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('type_id')->equals(2);
        $res = $index->executeQuery($query);
        $this->assertTrue($res->getTotalNum()>=1);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
        
        // Test null
        // -------------------------------------------------
        $testEnt->setValue("type_id", null);
        $this->account->getServiceManager()->get("Entity_DataMapper")->save($testEnt);
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('type_id')->equals(null);
        $res = $index->executeQuery($query);
        $this->assertTrue($res->getTotalNum()>=1);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
               
        // Cleanup
        $this->deleteTestCustomer($testEnt);
    }
    
    /**
     * Run test of is equal conditions
     * 
     * @group testWhereEqualsFkey
     */
    public function testWhereEqualsFkey()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
            //$this->assertTrue(false, "Index could not be setup!");
        
        $uniName = "utestequals." . uniqid();
        
        // Save a test object
        $testEnt = $this->createTestCustomer();
        
        // Test value is set
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('status_id')->equals($testEnt->getValue("status_id"));
        $res = $index->executeQuery($query);
        $this->assertTrue($res->getTotalNum()>=1);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
        
        // Test null
        // -------------------------------------------------
        $cachedStatus = $testEnt->getValue("status_id");
        $testEnt->setValue("status_id", null);
        $this->account->getServiceManager()->get("Entity_DataMapper")->save($testEnt);
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('status_id')->equals(null);
        $res = $index->executeQuery($query);
        $this->assertTrue($res->getTotalNum()>=1);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
        
        // Make sure query with old id does not return entity
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('status_id')->equals($cachedStatus);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
               
        // Cleanup
        $this->deleteTestCustomer($testEnt);
    }
    
    /**
     * Run test of is equal conditions
     */
    public function testWhereEqualsFkeyMulti()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
            //$this->assertTrue(false, "Index could not be setup!");
        
        $uniName = "utestequals." . uniqid();
        
        // Save a test object
        $testEnt = $this->createTestCustomer();
        
        // Query collection for fkey_multi
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $groups = $testEnt->getValue("groups");
        $query->where('groups')->equals($groups[0]);
        $res = $index->executeQuery($query);
        $this->assertTrue($res->getTotalNum()>=1);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
        
        $cachedGroups = $testEnt->getValue("groups");
        $testEnt->setValue("groups", null);
        $this->account->getServiceManager()->get("Entity_DataMapper")->save($testEnt);
        
        // Test null for groups
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $groups = $testEnt->getValue("groups");
        $query->where('groups')->equals(null);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
        
        // Make sure object no longer returns on null query with old id
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $groups = $testEnt->getValue("groups");
        $query->where('groups')->equals($cachedGroups[0]);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
               
        // Cleanup
        $this->deleteTestCustomer($testEnt);
    }
    
    /**
     * Run test of is equal conditions
     */
    public function testWhereEqualsBool()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
            //$this->assertTrue(false, "Index could not be setup!");
        
        // Save a test object
        $testEnt = $this->createTestCustomer();
        
        // Query collection for boolean
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('f_nocall')->equals(true);
        $res = $index->executeQuery($query);
        $this->assertTrue($res->getTotalNum()>=1);
        // Look for the entity above
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
                $found = true;
        }
        $this->assertTrue($found);
               
        // Cleanup
        $this->deleteTestCustomer($testEnt);
    }
    
    /**
     * Run test of is equal conditions
     */
    public function testWhereEqualsObject()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
            //$this->assertTrue(false, "Index could not be setup!");
        
        $dm = $this->account->getServiceManager()->get("Entity_DataMapper");
                
        // Save a test customer
        $testEnt = $this->createTestCustomer();
        
        // Create a test case attached to the customer
        $case = $this->account->getServiceManager()->get("EntityLoader")->create("case");
        $case->setValue("name", "Unit Test Case");
        $case->setValue("customer_id", $testEnt->getId(), $testEnt->getName());
        $cid = $dm->save($case);
        
        // Query for customer id
        $query = new \Netric\EntityQuery($case->getObjType());
        $query->where('customer_id')->equals($testEnt->getId());
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        
        // Query with null customer id
        $case->setValue("customer_id", "");
        $dm->save($case);
        $query = new \Netric\EntityQuery($case->getObjType());
        $query->where('id')->equals($case->getId());
        $query->where('customer_id')->equals("");
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
               
        // Cleanup
        $dm->delete($case, true);
        $this->deleteTestCustomer($testEnt);
    }
    
    /**
     * Not euquals text
     */
    public function testWhereNotEqualsText()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
        
        // Save a test object
        $testEnt = $this->createTestCustomer();
        
        // Query value
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('name')->doesNotEqual($testEnt->getValue("name"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
        
        // Does not equal null
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('name')->doesNotEqual(null);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
               
        // Cleanup
        $this->deleteTestCustomer($testEnt);
    }
    
    /**
     * Not euquals text
     */
    public function testWhereNotEqualsNumber()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
        
        // Save a test object
        $testEnt = $this->createTestCustomer();
        
        // Query value
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('type_id')->doesNotEqual(2);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
        
        // Does not equal null
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('type_id')->doesNotEqual(null);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
               
        // Cleanup
        $this->deleteTestCustomer($testEnt);
    }
    
    /**
     * Run test of is equal conditions
     */
    public function testWhereNotEqualsFkey()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
        
        // Save a test object
        $testEnt = $this->createTestCustomer();
        
        // Test value is set
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('status_id')->doesNotEqual($testEnt->getValue("status_id"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
        
        // Test null
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('status_id')->doesNotEqual(null);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
        
        // Cleanup
        $this->deleteTestCustomer($testEnt);
    }
    
    /**
     * Run test of is equal conditions
     */
    public function testWhereNotEqualsFkeyMulti()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
        
        // Save a test object
        $testEnt = $this->createTestCustomer();
        
        // Test value is set
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $groups = $testEnt->getValue("groups");
        $query->where('groups')->doesNotEqual($groups[0]);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
        
        // Test null
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('groups')->doesNotEqual(null);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
        
        // Cleanup
        $this->deleteTestCustomer($testEnt);
    }
    
    /**
     * Test numbers for is greater
     */
    public function testIsLessNumber()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
            //$this->assertTrue(false, "Index could not be setup!");
        
        // Save a test object
        $testEnt = $this->createTestCustomer();
        
        // Is greater inclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('type_id')->isLessThan(3);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
        
        // Is greater exclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('type_id')->isLessThan(2);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
        
        // Is greater or equal inclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('type_id')->isLessOrEqualTo(2);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
        
        // Is greater or equal exclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('type_id')->isLessOrEqualTo(1);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
        
               
        // Cleanup
        $this->deleteTestCustomer($testEnt);
    }
    
    /**
     * Test numbers for is greater
     */
    public function testIsLessDateTime()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
            //$this->assertTrue(false, "Index could not be setup!");
        
        // Save a test object
        $testEnt = $this->createTestCustomer();
        
        // Is greater inclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('last_contacted')->isLessThan(strtotime("+1 day"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
        
        // Is greater exclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('last_contacted')->isLessThan(strtotime("-1 day"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
        
        // Is greater or equal inclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('last_contacted')->isLessOrEqualTo($testEnt->getValue("last_contacted"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
        
        // Is greater or equal exclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('last_contacted')->isLessOrEqualTo(strtotime("-1 day"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
        
               
        // Cleanup
        $this->deleteTestCustomer($testEnt);
    }
    
    /**
     * Test numbers for is greater
     */
    public function testIsGreaterNumber()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
            //$this->assertTrue(false, "Index could not be setup!");
        
        $uniName = "utestequals." . uniqid();
        
        // Save a test object
        $testEnt = $this->createTestCustomer();
        
        // Is greater inclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('type_id')->isGreaterThan(1);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
        
        // Is greater exclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('type_id')->isGreaterThan(2);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
        
        // Is greater or equal inclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('type_id')->isGreaterOrEqualTo(2);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
        
        // Is greater or equal exclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('type_id')->isGreaterOrEqualTo(3);
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
        
               
        // Cleanup
        $this->deleteTestCustomer($testEnt);
    }
    
    /**
     * Test numbers for is greater
     */
    public function testIsGreaterDateTime()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
            //$this->assertTrue(false, "Index could not be setup!");
        
        $uniName = "utestequals." . uniqid();
        
        // Save a test object
        $testEnt = $this->createTestCustomer();
        
        // Is greater inclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('last_contacted')->isGreaterThan(strtotime("-1 day"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
        
        // Is greater exclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('last_contacted')->isGreaterThan(strtotime("+1 day"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
        
        // Is greater or equal inclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('last_contacted')->isGreaterOrEqualTo($testEnt->getValue("last_contacted"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
        
        // Is greater or equal exclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('last_contacted')->isGreaterOrEqualTo(strtotime("+1 day"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
        
               
        // Cleanup
        $this->deleteTestCustomer($testEnt);
    }
    
    /**
     * Check begins with
     */
    public function testBeginsWith()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
        
        // Save a test object
        $testEnt = $this->createTestCustomer();
        
        // Query null - first name is not set
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('name')->beginsWith(substr($testEnt->getValue("name"), 0, 10));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
               
        // Cleanup
        $this->deleteTestCustomer($testEnt);
    }
    
    /**
     * Check begins with
     */
    public function testContains()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
        
        // Save a test object
        $testEnt = $this->createTestCustomer();
        
        // Query null - first name is not set
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('name')->contains(substr($testEnt->getValue("name"), 4, 6));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
               
        // Cleanup
        $this->deleteTestCustomer($testEnt);
    }
    
    /**
     * Test date contains
     */
    public function testDateIsEqual()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
        
        // Save a test object
        $testEnt = $this->createTestCustomer();
        
        // Day is equal
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('last_contacted')->dayIsEqual(date("j"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
        
        // Month is equal
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('last_contacted')->monthIsEqual(date("n"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
        
        // Year is equal
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('last_contacted')->yearIsEqual(date("Y"));
        $res = $index->executeQuery($query);
        $found = false;
        for ($i = 0; $i < $res->getTotalNum(); $i++)
        {
            $ent = $res->getEntity($i);
            if ($ent->getId() == $testEnt->getId())
            {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
               
        // Cleanup
        $this->deleteTestCustomer($testEnt);
    }
    
    public function testWithinLastXNum()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
        
        // Save a test object
        $testEnt = $this->createTestCustomer();
        
        // Day - inclusive
        // -------------------------------------------------
        $testEnt->setValue("last_contacted", strtotime("-2 days"));
        $this->account->getServiceManager()->get("Entity_DataMapper")->save($testEnt);
        
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->lastNumDays(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getId(), $obj->getId());
        
        // Day - exclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->lastNumDays(1);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());
        
        // Week - inclusive
        // -------------------------------------------------
        $testEnt->setValue("last_contacted", strtotime("-2 weeks"));
        $this->account->getServiceManager()->get("Entity_DataMapper")->save($testEnt);
        
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->lastNumWeeks(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getId(), $obj->getId());
        
        // Week - exclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->lastNumWeeks(1);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());
        
        // Month - inclusive
        // -------------------------------------------------
        $testEnt->setValue("last_contacted", strtotime("-2 months"));
        $this->account->getServiceManager()->get("Entity_DataMapper")->save($testEnt);
        
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->lastNumMonths(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getId(), $obj->getId());
        
        // Month - exclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->lastNumMonths(1);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());
        
        // Year - inclusive
        // -------------------------------------------------
        $testEnt->setValue("last_contacted", strtotime("-2 years"));
        $this->account->getServiceManager()->get("Entity_DataMapper")->save($testEnt);
        
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->lastNumYears(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getId(), $obj->getId());
        
        // Year - exclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->lastNumYears(1);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());
        
        // Cleanup
        $this->deleteTestCustomer($testEnt);
    }
    
    public function testWithinNextXNum()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
        
        // Save a test object
        $testEnt = $this->createTestCustomer();
        
        // Day - inclusive
        // -------------------------------------------------
        $testEnt->setValue("last_contacted", strtotime("+2 days"));
        $this->account->getServiceManager()->get("Entity_DataMapper")->save($testEnt);
        
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->nextNumDays(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getId(), $obj->getId());
        
        // Day - exclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->nextNumDays(1);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());
        
        // Week - inclusive
        // -------------------------------------------------
        $testEnt->setValue("last_contacted", strtotime("+2 weeks"));
        $this->account->getServiceManager()->get("Entity_DataMapper")->save($testEnt);
        
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->nextNumWeeks(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getId(), $obj->getId());
        
        // Week - exclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->nextNumWeeks(1);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());
        
        // Month - inclusive
        // -------------------------------------------------
        $testEnt->setValue("last_contacted", strtotime("+2 months"));
        $this->account->getServiceManager()->get("Entity_DataMapper")->save($testEnt);
        
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->nextNumMonths(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getId(), $obj->getId());
        
        // Month - exclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->nextNumMonths(1);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());
        
        // Year - inclusive
        // -------------------------------------------------
        $testEnt->setValue("last_contacted", strtotime("+2 years"));
        $this->account->getServiceManager()->get("Entity_DataMapper")->save($testEnt);
        
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->nextNumYears(3);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $obj = $res->getEntity(0);
        $this->assertEquals($testEnt->getId(), $obj->getId());
        
        // Year - exclusive
        // -------------------------------------------------
        $query = new \Netric\EntityQuery($testEnt->getObjType());
        $query->where('id')->equals($testEnt->getId());
        $query->where('last_contacted')->nextNumYears(1);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());
        
        // Cleanup
        $this->deleteTestCustomer($testEnt);
    }
    
    
    /**
	 * Test query string patter explosion
	 */
	public function testSearchStrExpl()
	{
		// Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;

		// Single email address
		$qstr = "sky.stebnicki@aereus.com";
		$terms = $index->queryStringToTerms($qstr);
		$this->assertEquals($terms[0], "sky.stebnicki@aereus.com");

		// terms and phrases
		$qstr = "sky.stebnicki@aereus.com \"in quotes\" single";
		$terms = $index->queryStringToTerms($qstr);
		$this->assertEquals($terms[0], "sky.stebnicki@aereus.com");
		$this->assertEquals($terms[1], "\"in quotes\"");
		$this->assertEquals($terms[2], "single");
	}
    
    public function testSearchDeleted()
	{
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
        
        // Save a test object
        $dm = $this->account->getServiceManager()->get("Entity_DataMapper");
        $obj = $this->account->getServiceManager()->get("EntityLoader")->create("project_story");
        $obj->setValue("name", "testSearchDeleted");
        $oid = $dm->save($obj);
        $dm->delete($obj);

		// First test regular query without f_deleted flag set
        $query = new \Netric\EntityQuery("project_story");
        $query->where('id')->equals($oid);
        $res = $index->executeQuery($query);
        $this->assertEquals(0, $res->getTotalNum());

		// Test deleted flag set should return with deleted customer
        $query = new \Netric\EntityQuery("project_story");
        $query->where('id')->equals($oid);
        $query->where('f_deleted')->equals(true);
        $res = $index->executeQuery($query);
        $this->assertEquals(1, $res->getTotalNum());
        $ent = $res->getEntity(0);
        $this->assertEquals($oid, $ent->getId());
        
		// Cleanup
		$dm->delete($obj, true);
	}
    
    /**
     * Test getting heiarchy for groups for each index - may have custom version
     */
    public function testGetHeiarchyDownGrp()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
        
        $g1 = $this->createGrouping("customer", "groups", "HeiarchyDownGrp1");
        $g2 = $this->createGrouping("customer", "groups", "HeiarchyDownGrp2", $g1['id']);
        
        $def = $this->account->getServiceManager()->get("EntityDefinitionLoader")->get("customer");
        $field = $def->getField("groups");
        
        $children = $index->getHeiarchyDownGrp($field, $g1["id"]);
        $this->assertTrue(count($children)>0);
        $found1 = false;
        $found2 = false;
        foreach ($children as $gid)
        {
            if ($gid == $g1['id'])
                $found1 = true;
            if ($gid == $g2['id'])
                $found2 = true;
        }
        $this->assertTrue($found1);
        $this->assertTrue($found2);
        
        // Cleanup
        $this->deleteGrouping("customer", "groups", $g1['id']);
        $this->deleteGrouping("customer", "groups", $g2['id']);
    }
    
    /**
     * Test getting heiarchy for objects
     */
    public function testGetHeiarchyDownObj()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
        $loader = $this->account->getServiceManager()->get("EntityLoader");
        $dm = $this->account->getServiceManager()->get("Entity_DataMapper");
        
        $folder1 = $loader->create("folder");
        $folder1->setValue("name", "My Test Folder");
        $dm->save($folder1);
        $this->assertNotNull($folder1->getId());
        
        $folder2 = $loader->create("folder");
        $folder2->setValue("name", "My Test SubFolder");
        $folder2->setValue("parent_id", $folder1->getId());
        $dm->save($folder2);
        $this->assertNotNull($folder2->getId());
        
        $children = $index->getHeiarchyDownObj("folder", $folder1->getId());
        $this->assertTrue(count($children)>0);
        $found1 = false;
        $found2 = false;
        foreach ($children as $gid)
        {
            if ($gid == $folder1->getId())
                $found1 = true;
            if ($gid == $folder2->getId())
                $found2 = true;
        }
        $this->assertTrue($found1);
        $this->assertTrue($found2);
        
        // Cleanup
        $dm->delete($folder2, true);
        $dm->delete($folder1, true);
    }
    
    /**
     * Test getting heiarchy for objects
     */
    public function testGetHeiarchyUpObj()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index)
            return;
        $loader = $this->account->getServiceManager()->get("EntityLoader");
        $dm = $this->account->getServiceManager()->get("Entity_DataMapper");
        
        $folder1 = $loader->create("folder");
        $folder1->setValue("name", "My Test Folder");
        $dm->save($folder1);
        $this->assertNotNull($folder1->getId());
        
        $folder2 = $loader->create("folder");
        $folder2->setValue("name", "My Test SubFolder");
        $folder2->setValue("parent_id", $folder1->getId());
        $dm->save($folder2);
        $this->assertNotNull($folder2->getId());
        
        $children = $index->getHeiarchyUpObj("folder", $folder2->getId());
        $this->assertTrue(count($children)>0);
        $found1 = false;
        $found2 = false;
        foreach ($children as $gid)
        {
            if ($gid == $folder1->getId())
                $found1 = true;
            if ($gid == $folder2->getId())
                $found2 = true;
        }
        $this->assertTrue($found1);
        $this->assertTrue($found2);
        
        // Cleanup
        $dm->delete($folder2, true);
        $dm->delete($folder1, true);
    }
    
    /**
	 * Test hierarcy subqueries
	 *
	 * @group testHierarcySubqueries
	 *
	public function testHierarcySubqueries()
	{
		$indexes = array("db");
		if (index_is_available("elastic"))
			$indexes[] = "elastic";
		
		// Setup files and folders for example
		$antfs = new AntFs($this->dbh, $this->user);
		$fldr = $antfs->openFolder("/tests/testHierarcySubqueries", true);
		$this->assertNotNull($fldr);
		$fldr2 = $antfs->openFolder("/tests/testHierarcySubqueries/Child", true);
		$this->assertNotNull($fldr2);
		$file = $fldr2->openFile("testsync", true);
		$this->assertNotNull($file);

		foreach ($indexes as $indName)
		{
			$fldr->setIndex($indName);
			$fldr->index();
			$fldr2->setIndex($indName);
			$fldr2->index();
			$file->setIndex($indName);
			$file->index();

			// Test equal to root which should return none
			$objList = new CAntObjectList($this->dbh, "file", $this->user);
			$objList->setIndex($indName); // Manually set index type
			$objList->addCondition("and", "folder_id", "is_equal", $fldr->id);
			$objList->getObjects();
			$this->assertEquals(0, $objList->getNumObjects());

			// Now test with is_less_or_equal
			$objList = new CAntObjectList($this->dbh, "file", $this->user);
			$objList->setIndex($indName); // Manually set index type
			$objList->addCondition("and", "folder_id", "is_less_or_equal", $fldr->id);
			$objList->getObjects();
			$this->assertTrue($objList->getNumObjects() > 0);
		}

		// Cleanup
		$file->removeHard();
		$fldr2->removeHard();
		$fldr->removeHard();
	}
     * 
     */

	/**
	 * Test if using an fkey label works
	 *
	 * @group testFkeyLabelToId
	 *
	public function testFkeyLabelToId()
	{
		$dbh = $this->dbh;

		$obj = new CAntObject($dbh, "activity", null, $this->user);
		$grpdat = $obj->getGroupingEntryByName("type_id", "testFkeyLabelToId");
		if (!$grpdat)
			$grpdat = $obj->addGroupingEntry("type_id", "testFkeyLabelToId");
		$obj->setValue("name", "Test customer testFkeyLabelToId");
		$obj->setValue("type_id", $grpdat["id"]);
		$oid = $obj->save();

		// Query based on type_id label
		$objList = new CAntObjectList($this->dbh, "activity", $this->user);
		$objList->addCondition("and", "type_id", "is_equal", "testFkeyLabelToId");
		$objList->getObjects();
		$this->assertTrue($objList->getNumObjects() > 0);

		// Cleanup
		$obj->deleteGroupingEntry("groups", $grpdat['id']);
		$obj->removeHard();
	}
     * 
     */
}
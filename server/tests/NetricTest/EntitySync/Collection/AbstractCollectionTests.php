<?php
/**
 * Common Collection Tests
 */
namespace NetricTest\EntitySync\Collection;

use PHPUnit_Framework_TestCase;

abstract class AbstractCollectionTests extends PHPUnit_Framework_TestCase 
{
	/**
     * Tennant accountAbstractCollectionTests
     * 
     * @var \Netric\Account
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
	protected function setUp() 
	{
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->user = $this->account->getUser(\Netric\User::USER_ADMINISTRATOR);
	}
    
    /**
	 * Get a collection object to perform common tests
	 *
	 * @return CollectionInterface
	 */
	abstract protected function getCollection();

    /**
     * Test save and load
     */
    public function testSaveAndLoad()
    {
        /*
        $pid = "EntitySync_CollectionTest::testSave";

        $partner = new AntObjectSync_Partner($this->dbh, $pid, $this->user);
        $partnerId = ($partner->id) ? $partner->id : $partner->save();

        // Save new collection
        $coll = new AntObjectSync_Collection($this->dbh);
        $coll->partnerId = $partnerId;
        $coll->objectType = $cust->object_type;
        $coll->objectTypeId = $cust->object_type_id;
        $coll->conditions = array(
            array("blogic"=>"and", "field"=>"type_id", "operator"=>"is_equal", "condValue"=>'2'),
        );
        $cid = $coll->save();
        $this->assertTrue(is_numeric($cid));

        // Load and check values
        unset($coll);
        $coll = new AntObjectSync_Collection($this->dbh, $cid);
        $this->assertEquals($coll->partnerId, $partnerId);
        $this->assertEquals($coll->objectType, $cust->object_type);
        $this->assertEquals($coll->objectTypeId, $cust->object_type_id);
        $this->assertEquals(count($coll->conditions), 1);

        // Cleanup
        $coll->remove();
        $partner->remove();
        */
    }
}
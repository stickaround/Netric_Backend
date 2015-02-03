<?php
/**
 * Test entity sync partners
 */
namespace NetricTest\EntitySync;

use Netric\EntitySync\Partner;
use PHPUnit_Framework_TestCase;

class PartnerTest extends PHPUnit_Framework_TestCase 
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

	protected function getPartner()
	{
		$partnerId = "PartnerTest";
		$dm = $this->account->getServiceManager()->get("EntitySync_DataMapper");
		//$index = $this->account->getServiceManager()->get("EntityQuery_Index");
		return new Partner($dm);
	}

	/**
	 * Make sure we can construct this partner
	 */
	public function testConstruct()
	{
		$partner = $this->getPartner();
		
		$this->assertInstanceOf('\Netric\EntitySync\Partner', $partner);
	}

}

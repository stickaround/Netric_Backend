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
use PHPUnit_Framework_TestCase;

abstract class AbstractDataMapperTests extends PHPUnit_Framework_TestCase 
{
	/**
     * Tennant account
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
	 * Setup datamapper for the parent DataMapperTests class
	 *
	 * @return DataMapperInterface
	 */
	abstract protected function getDataMapper();
}
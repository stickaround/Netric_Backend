<?php
/**
 * Test saving and loading recurrence patterns from the DataMapper
 */
namespace NetricTest\Entity\Recurrence;

use Netric\Entity\Recurrence;
use PHPUnit_Framework_TestCase;

class EntitySeriesWriterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tennant account
     *
     * @var \Netric\Account
     */
    private $account = null;

    /**
     * Administrative user
     *
     * @var \Netric\User
     */
    private $user = null;

    /**
     * Setup each test
     */
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->user = $this->account->getUser(\Netric\User::USER_ADMINISTRATOR);
    }

    public function testCreateInstances()
    {

    }

    public function removeSeries()
    {

    }
}
<?php
/**
 * Test querying ElasticSearch server
 *
 * Most tests are inherited from IndexTestsAbstract.php.
 * Only define index specific tests here and try to avoid name collision with the tests
 * in the parent class. For the most part, the parent class tests all public functions
 * so private functions should be tested below.
 */
namespace NetricTest\EntityQuery\Index;

use Netric;
use PHPUnit\Framework\TestCase;

class PgsqlTest extends IndexTestsAbstract
{
    /**
     * Handle to pgsql database
     *
     * @var Db\Pgsql
     */
    private $dbh = null;
    
    /**
     * Use this funciton in all the indexes to construct the datamapper
     *
     * @return EntityDefinition_DataMapperInterface
     */
    protected function getIndex()
    {
        $this->dbh = $this->account->getServiceManager()->get("Db");
        return new \Netric\EntityQuery\Index\Pgsql($this->account);
    }
    
    /**
     * Dummy test
     */
    public function testDummy()
    {
        $this->assertTrue(true);
    }
}

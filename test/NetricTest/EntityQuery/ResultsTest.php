<?php

namespace NetricTest\EntityQuery;

use Netric;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Entity\Entity;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Results;
use Netric\EntityQuery;
use NetricTest\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test querying ElasticSearch server
 *
 * Most tests are inherited from IndexTestsAbstract.php.
 * Only define index specific tests here and try to avoid name collision with the tests
 * in the parent class. For the most part, the parent class tests all public functions
 * so private functions should be tested below.
 */
class ResultsTest extends TestCase
{
    /**
     * Test automatic pagination
     */
    public function testPagination()
    {
        $account = Bootstrap::getAccount();
        $entityLoader = $account->getServiceManager()->get(EntityLoaderFactory::class);
        $testDefinition = new EntityDefinition("test", $account->getAccountId());

        $query = new EntityQuery("test");
        $query->setOffset(0);
        $query->setLimit(2);

        // Simulate results and add entities
        $results = new Results($query);
        $results->addEntity(new Entity($testDefinition, $entityLoader));
        $results->addEntity(new Entity($testDefinition, $entityLoader));
        $results->setTotalNum(5);

        // Should push us to the second page
        $ent = $results->getEntity(2);
        $this->assertEquals(2, $results->getOffset());

        // Should push us to the last
        $ent = $results->getEntity(4);
        $this->assertEquals(4, $results->getOffset());

        // Now rewind back to the first page
        $ent = $results->getEntity(0);
        $this->assertEquals(0, $results->getOffset());
    }

    /**
     * Make sure that trying to get an entity out of bounds will throw an execption
     */
    public function testGetEntityOutOfBounds()
    {
        $query = new EntityQuery("customer");
        $results = new Results($query);

        $this->expectException(\RuntimeException::class);

        // Get number way outside the bounds (should throw an exception)
        $results->getEntity(1000);
    }
}

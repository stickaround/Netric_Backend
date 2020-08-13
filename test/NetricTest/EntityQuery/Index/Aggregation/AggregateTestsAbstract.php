<?php

/**
 * Define common tests that will need to be run with all data mappers.
 *
 * In order to implement the unit tests, a datamapper test case just needs
 * to extend this class and create a getDataMapper class that returns the
 * datamapper to be tested
 */

namespace NetricTest\EntityQuery\Index\Aggregation;

use Netric\EntityQuery\Aggregation;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\EntityQuery;
use Netric\EntityQuery\Aggregation\Terms;
use Netric\EntityQuery\Aggregation\Sum;
use Netric\EntityQuery\Aggregation\Stats;
use Netric\EntityQuery\Aggregation\Avg;
use Netric\EntityQuery\Aggregation\Min;
use Netric\EntityDefinition\ObjectTypes;

abstract class AggregateTestsAbstract extends TestCase
{
    /**
     * Tennant account
     *
     * @var \Netric\Account\Account
     */
    protected $account = null;

    /**
     * Campaign id used for filter
     *
     * @var int
     */
    protected $campaignId = null;

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
        $this->createTestData();
    }

    /**
     * Cleanup test objects
     */
    protected function tearDown(): void
    {
        $this->deleteTestData();
    }

    /**
     * Required by all derrieved classes
     *
     * @return \Netric\EnittyQuery\Index\IndexInterface The setup index to query
     */
    abstract protected function getIndex();

    /**
     * Create a few test objects
     */
    protected function createTestData()
    {
        // Cleanup any old objects
        $this->deleteTestData();

        // Get datamapper
        $dm = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);

        // Create a campaign for filtering
        $obj = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::MARKETING_CAMPAIGN, $this->account->getAccountId());
        $obj->setValue("name", "Unit Test Aggregates");
        $this->campaignId = $dm->save($obj, $this->account->getSystemUser());
        if (!$this->campaignId) {
            throw new \Exception("Could not create campaign");
        }

        // Create first opportunity
        $obj = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::OPPORTUNITY, $this->account->getAccountId());
        $obj->setValue("name", "Website");
        $obj->setValue("f_won", false);
        $obj->setValue("probability_per", 50);
        $obj->setValue("campaign_id", $this->campaignId);
        $obj->setValue("amount", 100);
        $oid = $dm->save($obj, $this->account->getSystemUser());

        // Create first opportunity
        $obj = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::OPPORTUNITY, $this->account->getAccountId());
        $obj->setValue("name", "Application");
        $obj->setValue("f_won", true);
        $obj->setValue("probability_per", 75);
        $obj->setValue("campaign_id", $this->campaignId);
        $obj->setValue("amount", 50);
        $oid = $dm->save($obj, $this->account->getSystemUser());
    }


    /**
     * Create a few test objects
     */
    protected function deleteTestData()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        $dm = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);

        // Find campaign id if not set
        if (!$this->campaignId) {
            $query = new EntityQuery(ObjectTypes::MARKETING_CAMPAIGN, $this->account->getAccountId());
            $query->where('name')->equals("Unit Test Aggregates");
            $res = $index->executeQuery($query);
            if ($res->getTotalNum() > 0) {
                $this->campaignId = $res->getEntity(0)->getEntityId();
            }
        }

        // Nothing to delete yet
        if (!$this->campaignId) {
            return;
        }


        $query = new EntityQuery(ObjectTypes::OPPORTUNITY, $this->account->getAccountId());
        $query->where('campaign_id')->equals($this->campaignId);
        $res = $index->executeQuery($query);
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $ent = $res->getEntity(0);
            $dm->delete($ent, $this->account->getAuthenticatedUser()); // delete hard
        }

        // Delete the campaign
        $ent = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->get(ObjectTypes::MARKETING_CAMPAIGN, $this->campaignId);
        $dm->delete($ent, $this->account->getAuthenticatedUser());
    }

    /**
     * Make sure the getTypeName for the abstract class works
     */
    public function testGetTypeName()
    {
        $agg = new Terms("test");
        $this->assertEquals("terms", $agg->getTypeName());
    }

    /**
     * Test terms aggregate
     */
    public function testTerms()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        $query = new EntityQuery(ObjectTypes::OPPORTUNITY, $this->account->getAccountId());
        $query->where('campaign_id')->equals($this->campaignId);

        $agg = new Terms("test");
        $agg->setField("name");
        $query->addAggregation($agg);
        $agg = $index->executeQuery($query)->getAggregation("test");
        $appInd = (strtolower($agg[0]["term"]) == "application") ? 0 : 1;
        $webInd = (strtolower($agg[0]["term"]) == "website") ? 0 : 1;

        $this->assertEquals(1, $agg[$appInd]["count"]);
        $this->assertEquals("application", strtolower($agg[$appInd]["term"]));
        $this->assertEquals(1, $agg[$webInd]["count"]);
        $this->assertEquals("website", strtolower($agg[$webInd]["term"]));
    }

    /**
     * Test sum aggregate
     */
    public function testSum()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        $query = new EntityQuery(ObjectTypes::OPPORTUNITY, $this->account->getAccountId());
        $query->where('campaign_id')->equals($this->campaignId);

        $agg = new Sum("test");
        $agg->setField("amount");
        $query->addAggregation($agg);
        $agg = $index->executeQuery($query)->getAggregation("test");
        $this->assertEquals(150, $agg); // 2 opps one with 50 and one with 100
    }

    /**
     * Test stats aggregate
     */
    public function testStats()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        $query = new EntityQuery(ObjectTypes::OPPORTUNITY, $this->account->getAccountId());
        $query->where('campaign_id')->equals($this->campaignId);

        $agg = new Stats("test");
        $agg->setField("amount");
        $query->addAggregation($agg);
        $agg = $index->executeQuery($query)->getAggregation("test");
        $this->assertEquals(2, $agg["count"]); // 2 opps one with 50 and one with 100
        $this->assertEquals(50, $agg["min"]); // 2 opps one with 50 and one with 100
        $this->assertEquals(100, $agg["max"]); // 2 opps one with 50 and one with 100
        $this->assertEquals(((100 + 50) / 2), $agg["avg"]); // 2 opps one with 50 and one with 100
        $this->assertEquals(150, $agg["sum"]); // 2 opps one with 50 and one with 100
    }

    /**
     * Test agv aggregate
     */
    public function testAvg()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        $query = new EntityQuery(ObjectTypes::OPPORTUNITY, $this->account->getAccountId());
        $query->where('campaign_id')->equals($this->campaignId);

        $agg = new Avg("test");
        $agg->setField("amount");
        $query->addAggregation($agg);
        $agg = $index->executeQuery($query)->getAggregation("test");
        $this->assertEquals(((100 + 50) / 2), $agg); // 2 opps one with 50 and one with 100
    }

    /**
     * Test min aggregate
     */
    public function testMin()
    {
        // Get index and fail if not setup
        $index = $this->getIndex();
        if (!$index) {
            return;
        }

        $query = new EntityQuery(ObjectTypes::OPPORTUNITY, $this->account->getAccountId());
        $query->where('campaign_id')->equals($this->campaignId);

        $agg = new Min("test");
        $agg->setField("amount");
        $query->addAggregation($agg);
        $agg = $index->executeQuery($query)->getAggregation("test");
        $this->assertEquals(50, $agg); // 2 opps one with 50 and one with 100
    }
}

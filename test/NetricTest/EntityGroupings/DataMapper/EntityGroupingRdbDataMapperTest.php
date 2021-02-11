<?php
namespace NetricTest\EntityGroupings\DataMapper;

use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperInterface;
use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperFactory;

/**
 * Test entity definition loader class that is responsible for creating and initializing exisiting definitions
 */
class EntityGroupingRdbDataMapperTest extends AbstractDataMapperTests
{
    /**
     * Setup datamapper for the parent DataMapperTests class
     *
     * @return EntityGroupingDataMapperInterface
     */
    protected function getDataMapper()
    {
        return $this->account->getServiceManager()->get(EntityGroupingDataMapperFactory::class);
    }
}

<?php

namespace NetricTest\Entity\DataMapper;

use Netric\Entity\DataMapper\EntityDataMapperFactory;
use Netric\Entity\DataMapper\EntityDataMapperInterface;

/**
 * Test entity definition loader class that is responsible for creating and initializing existing definitions
 */
class EntityPgsqlDataMapperTest extends DmTestsAbstract
{
    /**
     * Setup datamapper for the parent DataMapperTests class
     *
     * @return EntityDataMapperInterface
     */
    protected function getDataMapper()
    {
        // TODO: Normally we would construct the datamapper here, but since there
        // are so many dependencies in it right now, we are just using the factory.
        return $this->account->getServiceManager()->get(EntityDataMapperFactory::class);
    }
}

<?php
namespace NetricTest\Entity\DataMapper;

use Netric\Entity\DataMapperInterface;
use Netric\Entity\DataMapper\EntityRdbDataMapper;

/**
 * Test entity definition loader class that is responsible for creating and initializing exisiting definitions
 */
class EntityRdbDataMapperTest extends DmTestsAbstract
{
    /**
     * Setup datamapper for the parent DataMapperTests class
     *
     * @return DataMapperInterface
     */
    protected function getDataMapper()
    {
        return new EntityRdbDataMapper($this->account);
    }
}

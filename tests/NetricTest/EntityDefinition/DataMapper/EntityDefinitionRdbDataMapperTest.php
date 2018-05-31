<?php
namespace NetricTest\EntityDefinition\DataMapper;

use Netric\EntityDefinition\DataMapper\EntityDefinitionDataMapperInterface;
use Netric\EntityDefinition\DataMapper\EntityDefinitionRdbDataMapper;

/**
 * Test entity definition loader class that is responsible for creating and initializing exisiting definitions
 *
 * @group integration
 */
class EntityDefinitionRdbDataMapperTest extends DmTestsAbstract
{
    /**
     * Setup datamapper for the parent DataMapperTests class
     *
     * @return DataMapperInterface
     */
    protected function getDataMapper()
    {
        return new EntityDefinitionRdbDataMapper($this->account);
    }
}

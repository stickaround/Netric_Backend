<?php

namespace NetricTest\Account\Module\DataMapper;

use Netric\Account\Module\DataMapper\ModuleRdbDataMapper;
use Netric\Account\Module\DataMapper\DataMapperInterface;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Entity\EntityLoaderFactory;
use NetricTest\Bootstrap;
use Netric\Config\ConfigFactory;

/**
 * Db implementation of module DataMapper test
 *
 * @group integration
 */
class ModuleRdbDataMapperTest extends AbstractDataMapperTests
{
    /**
     * Get Db implementation of DataMapper
     *
     * @return DataMapperInterface
     */
    public function getDataMapper()
    {
        $account = Bootstrap::getAccount();

        $sl = $account->getServiceManager();
        $db = $sl->get(RelationalDbFactory::class);
        $config = $sl->get(ConfigFactory::class);
        $entityLoader = $sl->get(EntityLoaderFactory::class);

        return new ModuleRdbDataMapper($db, $config, $entityLoader);
    }
}

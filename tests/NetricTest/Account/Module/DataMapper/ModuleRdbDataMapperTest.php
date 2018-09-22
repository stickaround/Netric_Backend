<?php
namespace NetricTest\Account\Module\DataMapper;

use Netric\Account\Module\DataMapper\ModuleRdbDataMapper;
use Netric\Account\Module\DataMapper\DataMapperInterface;
use Netric\Db\Relational\RelationalDbFactory;
use NetricTest\Bootstrap;
use Netric\Config\ConfigFactory;

/**
 * Db implementation of module DataMapper test
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

        return new ModuleRdbDataMapper($db, $config, $account);
    }
}

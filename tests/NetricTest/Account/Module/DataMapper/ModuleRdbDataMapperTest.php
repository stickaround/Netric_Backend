<?php
namespace NetricTest\Account\Module\DataMapper;

use Netric\Account\Module\DataMapper\ModuleRdbDataMapper;
use Netric\Account\Module\DataMapper\DataMapperInterface;
use Netric\Db\Relational\RelationalDbFactory;

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
        $account = \NetricTest\Bootstrap::getAccount();

        $sl = $account->getServiceManager();
        $db = $sl->get(RelationalDbFactory::class);
        $config = $sl->get("Config");

        return new ModuleRdbDataMapper($db, $config, $account);
    }
}
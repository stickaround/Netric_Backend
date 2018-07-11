<?php
namespace Netric\Entity;

use Netric\ServiceManager;
use Netric\Config\ConfigFactory;
use Netric\Db\DbFactory;

/**
 * Service factory for the Forms
 */
class FormsFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $dbh = $sl->get(DbFactory::class);
        $config = $sl->get(ConfigFactory::class);
        return new Forms($dbh, $config);
    }
}

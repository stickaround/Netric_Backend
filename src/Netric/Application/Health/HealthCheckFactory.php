<?php
namespace Netric\Application\Health;

use Netric\FileSystem\FileStore\FileStoreFactory;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\Log\LogFactory;
use Netric\Db\Relational\RelationalDbFactory;

/**
 * Construct an application health service
 */
class HealthCheckFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator For loading dependencies
     * @return HealthCheck
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $log = $serviceLocator->get(LogFactory::class);
        $dbh = $serviceLocator->get(RelationalDbFactory::class);
        $fileStore = $serviceLocator->get(FileStoreFactory::class);
        return new HealthCheck($log, $dbh, $fileStore);
    }
}

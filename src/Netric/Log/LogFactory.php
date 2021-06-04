<?php
/**
 * Service factory for Log
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Log;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Create a Log service
 */
class LogFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface $serviceLocator For loading dependencies
     * @return LogInterface
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        return $serviceLocator->getApplication()->getLog();
    }
}

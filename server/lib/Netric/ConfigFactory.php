<?php
/**
 * Service factory for the Config
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric;

use Netric\ServiceManager;

/**
 * Create a Config service
 */
class ConfigFactory implements ServiceManager\AccountServiceLocatorInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return Config
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        return $sl->getAccount()->getApplication()->getConfig();
    }
}

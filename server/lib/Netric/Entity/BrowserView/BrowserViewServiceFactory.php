<?php
/**
 * Service factory for the Forms
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\BrowserView;

use Netric\ServiceManager;

/**
 * Create a new BrowserView service for getting and saving forms
 *
 * @package Netric\FileSystem
 */
class BrowserViewServiceFactory implements ServiceManager\ServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function createService(ServiceManager\ServiceLocatorInterface $sl)
    {
        $dbh = $sl->get("Db");
        $config = $sl->get("Config");
        $defLoader = $sl->get("EntityDefinitionLoader");
        return new BrowserViewService($dbh, $config, $defLoader);
    }
}

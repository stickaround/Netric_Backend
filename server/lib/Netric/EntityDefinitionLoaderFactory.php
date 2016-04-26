<?php
/**
 * Service factory for the Entity Definition Loader
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric;

use Netric\ServiceManager;

/**
 * Create a Entity Definition Loader service
 *
 * @package Netric\FileSystem
 */
class EntityDefinitionLoaderFactory implements ServiceManager\AccountServiceLocatorInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $dm = $sl->get("EntityDefinition_DataMapper");
        $cache = $sl->get("Cache");

        return new EntityDefinitionLoader($dm, $cache);
    }
}

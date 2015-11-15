<?php
/**
 * Service factory for the EntityValidator
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\Validator;

use Netric\ServiceManager;

/**
 * Create an entity validator instance
 */
class EntityValidatorFactory implements ServiceManager\ServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function createService(ServiceManager\ServiceLocatorInterface $sl)
    {
        return new EntityValidator();
    }
}

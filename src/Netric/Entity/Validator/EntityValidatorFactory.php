<?php
/**
 * Service factory for the EntityValidator
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\Validator;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Create an entity validator instance
 */
class EntityValidatorFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new EntityValidator();
    }
}

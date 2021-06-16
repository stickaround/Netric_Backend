<?php
/**
 * Service factory for the EntityValidator
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\Validator;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Create an entity validator instance
 */
class EntityValidatorFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        return new EntityValidator();
    }
}

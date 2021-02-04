<?php
/**
 * Interface for entity factories
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\EntityDefinition\EntityDefinition;

/**
 * Service factories are classes that handle the construction of complex/cumbersome services
 */
interface EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param EntityDefinition $def The definition of this type of object
     * @return new EntityInterface object
     */
    public static function create(ServiceLocatorInterface $serviceLocator, EntityDefinition $def);
}

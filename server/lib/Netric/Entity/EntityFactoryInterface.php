<?php
/**
 * Interface for entity factories
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity;

use Netric\ServiceManager;

/**
 * Service factories are classes that handle the construction of complex/cumbersome services
 */
interface EntityFactoryInterface
{
    /**
     * Entity creation factory
     *
     * @param \Netric\ServiceManager\ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return new EntityInterface object
     */
    public static function create(ServiceManager\ServiceLocatorInterface $sl);
}

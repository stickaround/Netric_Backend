<?php
/**
 * Define invokable service factory interface
 * 
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\ServiceManager;

/**
 * Service factories are classes that handle the construction of complex/cumbersome services
 */
interface ServiceFactoryInterface
{
	/**
	 * Service creation factory
	 *
	 * @param \Netric\ServiceManager\ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
	 * @return mixed Initailized service object
	 */
	public function createService(\Netric\ServiceManager\ServiceLocatorInterface $sl);
}

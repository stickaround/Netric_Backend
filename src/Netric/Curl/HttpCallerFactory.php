<?php

declare(strict_types=1);

namespace Netric\Curl;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;

/**
 * Construct the Http Caller
 */
class HttpCallerFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Construct a controller and return it
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
      return new HttpCaller();
    }
}

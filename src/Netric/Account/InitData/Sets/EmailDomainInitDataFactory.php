<?php

declare(strict_types=1);

namespace Netric\Account\InitData\Sets;

use Netric\Mail\MailSystemFactory;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Return data intializer
 */
class EmailDomainInitDataFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return InitDataInterface[]
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $mailSystem = $serviceLocator->get(MailSystemFactory::class);
        return new EmailDomainInitData(
            $mailSystem
        );
    }
}

<?php

namespace Netric\Authentication;

use Netric\ServiceManager;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\Request\RequestFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Crypt\VaultServiceFactory;

/**
 * Create an authentication service
 *
 * @package Netric\Authentication
 */
class AuthenticationServiceFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return AuthenticationService
     */
    public function createService(AccountServiceManagerInterface $sl)
    {

        $userIndex = $sl->get(IndexFactory::class);
        $userLoader = $sl->get(EntityLoaderFactory::class);
        $request = $sl->get(RequestFactory::class);

        $vault = $sl->get(VaultServiceFactory::class);
        $key = $vault->getSecret("auth_private_key");

        return new AuthenticationService($key, $userIndex, $userLoader, $request);
    }
}

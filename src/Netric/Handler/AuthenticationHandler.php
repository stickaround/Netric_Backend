<?php

namespace Netric\Handler;

use Netric\Account\AccountContainerInterface;
use Netric\Authentication\AuthenticationService;
use Netric\Application\Application;
use NetricApi\AuthenticationIf;

class AuthenticationHandler implements AuthenticationIf
{
    /**
     * Container used to load accounts
     */
    private AccountContainerInterface $accountContainer;

    /**
     * Service used to get the current user/account
     */
    private AuthenticationService $authService;

    /**
     * The current application instance
     */
    private Application $application;

    /**
     * Initialize controller and all dependencies
     *
     * @param AccountContainerInterface $accountContainer Container used to load accounts
     * @param AuthenticationService $authService Service used to get the current user/account
     * @param Application $application The current application instance
     */
    public function __construct(
        AccountContainerInterface $accountContainer,
        AuthenticationService $authService,
        Application $application
    ) {
        $this->accountContainer = $accountContainer;
        $this->authService = $authService;
        $this->application = $application;
    }

    /**
     * Check if a token is valid
     *
     * @param string $token
     * @return boolean
     */
    public function isTokenValid($token): bool
    {
        return false;
    }
}

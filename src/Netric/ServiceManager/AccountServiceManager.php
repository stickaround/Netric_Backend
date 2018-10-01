<?php

/**
 * Our implementation of a ServiceLocator pattern
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\ServiceManager;

use Netric\Account\Account;
use Netric\Entity\EntityLoaderFactory;
use Netric;

/**
 * Class for constructing, caching, and finding services by name
 */
class AccountServiceManager extends AbstractServiceManager implements AccountServiceManagerInterface
{
    /**
     * Handle to netric account
     *
     * @var Account
     */
    private $account = null;

    /**
     * Class constructor
     *
     * @param Account $account The account we are loading services for
     */
    public function __construct(Account $account)
    {
        $this->account = $account;
        $application = $account->getApplication();
        parent::__construct($application, $application->getServiceManager());
    }

    /**
     * Get account instance of netric
     *
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }
}

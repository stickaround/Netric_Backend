<?php
namespace Netric\Account;

use Netric\Application\DataMapperInterface;
use Netric\Application\Application;
use Netric\Cache;
use Netric\Error\Error;
use Netric\Error\ErrorAwareInterface;

/**
 * IdentityMapper interface for loading accounts
 */
interface AccountIdentityMapperInterface
{
    /**
     * Load an account by id
     *
     * @param string $id The unique id of the account to get
     * @param Application $application Reference to Application instance
     * @return Account on success, null on failure
     */
    public function loadById(int $id, Application $application) : ? Account;

    /**
     * Get an account by the unique name
     *
     * @param string $name
     * @param Application $application Reference to Application instance
     * @return Account on success, null on failure
     */
    public function loadByName(string $name, Application $application) : ? Account;

    /**
     * Delete an account
     *
     * @param Account $account The account to delete
     * @return bool true on success, false on failure
     * @throws \RuntimeException If account is not a valid account with an ID
     */
    public function deleteAccount(Account $account) : bool;

    /**
     * Create a new account and return the ID
     *
     * @param string $name A unique name for this account
     * @return int Unique id of the created account, 0 on failure
     */
    public function createAccount(string $name) : int;
}

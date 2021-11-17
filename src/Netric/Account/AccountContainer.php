<?php

namespace Netric\Account;

use Netric\Application\DataMapperInterface;
use Netric\Application\Application;
use Netric\Authentication\AuthenticationService;
use Netric\Cache\CacheInterface;
use Netric\Error\Error;
use Netric\Error\ErrorAwareInterface;

/**
 * IdentityMapper for loading accounts
 */
class AccountContainer implements AccountContainerInterface, ErrorAwareInterface
{
    /**
     * Application Data Mapper for persistence
     *
     * @var DataMapperinterface
     */
    private $appDm = null;

    /**
     * System cache used to spare the db from too many hits
     *
     * @var CacheInterface
     */
    private $cache = null;

    /**
     * In memory cache of loaded accounts
     *
     * @var Account[]
     */
    private $loadedAccounts = [];

    /**
     * In memory maps from name to id
     */
    private $nameToIdMap = [];

    /**
     * Array of errors
     *
     * @var Error[]
     */
    private $errors = [];

    /**
     * Construct and setup dependencies
     *
     * @param DataMapperinterface $appDm Application DataMapper
     * @param CacheInterface $cache
     * @throws \Exception If all required dependencies were not passed
     */
    public function __construct(
        DataMapperInterface $appDm,
        CacheInterface $cache,
        Application $application
    ) {
        if (!$appDm) {
            throw new \Exception("Application datamapper is required");
        }

        $this->appDm = $appDm;
        $this->cache = $cache;
        $this->application = $application;
    }

    /**
     * Load an account by id
     *
     * @param string $id The unique id of the account to get
     * @param Application $application Reference to Application instance
     * @return Account on success, null on failure
     */
    public function loadById(string $accountId): ?Account
    {
        // First check to see if we have it cached in local memory
        $account = $this->loadFromMemory($accountId);

        // Return already loaded account
        if ($account) {
            return $account;
        }

        // Account is not already loaded so create a new instance
        $account = new Account($this->application);

        // Try from cache if not loaded in memeory
        if ($this->loadFromCache($accountId, $account)) {
            return $account;
        }

        // Load from the datamapper
        $ret = $this->appDm->getAccountById($accountId, $account);

        // Save the data to cache and memory
        if ($ret) {
            $this->setLocalMemory($account);
            $this->setCache($account);
            return $account;
        }

        return null;
    }

    /**
     * Get an account by the unique name
     *
     * @param string $name
     * @return Account on success, null on failure
     */
    public function loadByName(string $name): ?Account
    {
        // Try local memory first
        if (isset($this->nameToIdMap[$name])) {
            return $this->loadById($this->nameToIdMap[$name], $this->application);
        }

        // Now try cache
        $cachedId = $this->cache->get("netric/account/nametoidmap/$name");
        if ($cachedId) {
            return $this->loadById($cachedId);
        }

        // Load from the datamapper by name
        $account = new Account($this->application);
        if ($this->appDm->getAccountByName($name, $account)) {
            // Save the data to cache and memory
            $this->setLocalMemory($account);
            $this->setCache($account);

            // Save the maps
            $this->nameToIdMap[$name] = $account->getAccountId();
            $this->cache->set("netric/account/nametoidmap/$name", $account->getAccountId());
            return $account;
        }

        return null;
    }

    /**
     * Delete an account
     *
     * @param Account $account The account to delete
     * @return bool true on success, false on failure
     * @throws \RuntimeException If account is not a valid account with an ID
     */
    public function deleteAccount(Account $account): bool
    {
        // Make sure this account is valid with an ID
        if (empty($account->getAccountId())) {
            throw new \RuntimeException("Cannot delete an account that does not exist");
        }

        $accountId = $account->getAccountId();
        $accountName = $account->getName();
        if ($this->appDm->deleteAccount($accountId)) {
            // Clear cache
            $this->cache->delete("netric/account/" . $accountId);

            // Remove from in-memory cache
            if (isset($this->loadedAccounts[$accountId])) {
                unset($this->loadedAccounts[$accountId]);
            }

            // Clear save the maps
            $this->cache->delete("netric/account/nametoidmap/$accountName");

            if (isset($this->nameToIdMap[$accountName])) {
                unset($this->nameToIdMap[$accountName]);
            }

            return true;
        }

        // Something failed
        $this->errors[] = $this->appDm->getLastError();
        return false;
    }

    /**
     * Create a new account and return the ID
     *
     * @param string $name A unique name for this account
     * @return string Unique id of the created account, 0 on failure
     */
    public function createAccount(string $name): string
    {
        return $this->appDm->createAccount($name);
    }

    /**
     * Update an existing account
     *
     * @param string $accountId Unique id of the account that we are updating
     * @param array $accountData The data that will be used for updating an account
     * @return bool true on success, false on failure
     */
    public function updateAccount(string $accountId, array $accountData)
    {
        $result = $this->appDm->updateAccount($accountId, $accountData);

        // Clear cache
        $this->cache->delete("netric/account/" . $accountId);

        return $result;
    }

    /**
     * Get the last error
     *
     * @return Error|null
     */
    public function getLastError()
    {
        return (count($this->errors)) ? array_pop($this->errors) : null;
    }

    /**
     * Get array of errors that have occurred
     *
     * @return \Netric\Error\Error[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get array of all active account IDs
     *
     * This will be uncached and return only the ID and name
     *
     *  @return array [['account_id'=>ID, 'name'=>NAME]]
     */
    public function getAllActiveAccounts(): array
    {
        return $this->appDm->getAccounts();
    }

    /**
     * Get an account details from cache and load
     *
     * @param string $id The unique id of the account to get
     * @param Account $account Account to load data into
     * @return bool true on success, false on failure/not found
     */
    private function loadFromCache($id, Account &$account)
    {
        $data = $this->cache->get("netric/account/$id");
        if ($data) {
            if (isset($data["account_id"]) && isset($data["name"])) {
                $account->fromArray($data);
                // Put in local memory for even faster retrieval next time
                $this->setLocalMemory($account);

                return true;
            }
        }

        // Not found
        return false;
    }

    /**
     * Load from local memory
     *
     * @param string $id The unique id of the account to get
     * @return bool true on success, false on failure/not found
     */
    private function loadFromMemory($id)
    {
        if (isset($this->loadedAccounts[$id])) {
            return $this->loadedAccounts[$id];
        }

        // Not found
        return false;
    }

    /**
     * Cache an account in local memory
     *
     * @param Account $account Reference to Account object to initialize
     */
    private function setLocalMemory(Account &$account)
    {
        $this->loadedAccounts[$account->getAccountId()] = $account;
    }

    /**
     * Cache an account
     *
     * @param Account $account Reference to Account object to initialize
     * @return bool true on success, false on failure
     */
    private function setCache(Account &$account)
    {
        return $this->cache->set("netric/account/" . $account->getAccountId(), $account->toArray());
    }
}

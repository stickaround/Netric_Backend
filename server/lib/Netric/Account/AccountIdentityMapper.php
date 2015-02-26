<?php
/*
 * IdentityMapper for loading accounts
 * 
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2014 Aereus
 */
namespace Netric\Account;

use Netric\Application;
use Netric\Cache;

class AccountIdentityMapper
{
	/**
	 * Application datamapper
	 *
	 * @var \Netric\Application\DataMapperInterface
	 */
	private $appDm = null;

    /**
     * System cache used to spare the db from too many hits
     *
     * @var \Netric\Cache\CacheInterface
     */
    private $cache = null;

    /**
     * In memory cache of loaded accounts
     *
     * @var \Netric\Account[]
     */
    private $loadedAccounts = array();

    /**
     * In memory maps from name to id
     */
    private $nameToIdMap = array();

	/**
	 * Construct and setup dependencies
	 *
	 * @param \Netric\Application\DataMapperInterface $appDm Application DataMapper
     * @param \Netric\Cache\CacheInterface $cache
	 */
	public function __construct(Application\DataMapperInterface $appDm, Cache\CacheInterface $cache)
	{
		$this->appDm = $appDm;
        $this->cache = $cache;
	}

	/**
     * Load an account by id
     * 
     * @param string $id The unique id of the account to get
     * @param \Netric\Account $account Reference to Account object to initialize
     * @return bool true on success, false on failure/not found
     */
    public function loadById($id, &$account)
    {
        // First check to see if we have it cached in local memory
        if ($this->loadFromMemory($id, $account))
        {
            return true;
        }
        else if ($this->loadFromCache($id, $account))
        {
            return true;
        }

        // Load from the datamapper
        $ret = $this->appDm->getAccountById($id, $account);

        // Save the data to cache and memory
        if ($ret)
        {
            $this->setLocalMemory($account);
            $this->setCache($account);
        }

        return $ret;
    }

    /**
     * Get an account by the unique name
     * 
     * @param string $name
     * @param \Netric\Account $account Reference to Account object to initialize
     * @return bool true on success, false on failure/not found
     */
    public function loadByName($name, &$account)
    {
        // Try local memory first
        if (isset($this->nameToIdMap[$name]))
        {
            return $this->loadById($this->nameToIdMap[$name], $account);
        }

        // Now try cache
        $cachedId = $this->cache->get("netric/account/nametoidmap/$name");
        if ($cachedId)
        {
            return $this->loadById($cachedId, $account);
        }

        // Load from the datamapper by name
        if ($this->appDm->getAccountByName($name, $account))
        {
            // Save the data to cache and memory
            $this->setLocalMemory($account);
            $this->setCache($account);

            // Save the maps
            $this->nameToIdMap[$name] = $account->getId();
            $this->cache->set("netric/account/nametoidmap/$name", $account->getId());
            return true;
        }

        return false;
    }

    /**
     * Get an account details from cache and load
     *
     * @param string $id The unique id of the account to get
     * @param \Netric\Account $account Reference to Account object to initialize
     * @return bool true on success, false on failure/not found
     */
    private function loadFromCache($id, \Netric\Account &$account)
    {
        $data = $this->cache->get("netric/account/" . $id);
        if ($data)
        {
            if (isset($data["id"]) && isset($data["name"]))
            {
                $account->fromArray($data);
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
     * @param \Netric\Account $account Reference to Account object to initialize
     * @return bool true on success, false on failure/not found
     */
    private function loadFromMemory($id, \Netric\Account &$account)
    {
        if (isset($this->loadedAccounts[$id]))
        {
            $account->fromArray($this->loadedAccounts[$id]);
            return true;
        }

        // Not found
        return false;
    }

    /**
     * Cache an account in local memory
     *
     * @param \Netric\Account $account Reference to Account object to initialize
     */
    private function setLocalMemory(\Netric\Account &$account)
    {
        $this->loadedAccounts[$account->getId()] = $account->toArray();
    }

    /**
     * Cache an account
     *
     * @param \Netric\Account $account Reference to Account object to initialize
     * @return bool true on success, false on failure
     */
    private function setCache(\Netric\Account &$account)
    {
        return $this->cache->set("netric/account/" . $account->getId(), $account->toArray());
    }

}

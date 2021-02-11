<?php

/**
 * Manage dynamic settings for users and accounts
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Settings;

use Netric\Cache\CacheInterface;
use Netric\Db\DbInterface;
use Netric\ServiceManager;
use Netric\Entity\ObjType\UserEntity;
use Netric\Db\Relational\RelationalDbInterface;

/**
 * Get and set account and user settings
 */
class Settings
{
    /**
     * Handle to database
     *
     * @var RelationalDbInterface
     */
    protected $database = null;

    /**
     * Application cache - usually Memecache
     *
     * @var CacheInterface|null
     */
    private $cache = null;

    /**
     * Create new settings service
     *
     * @param RelationalDbInterface $database Handles to database actions
     * @param CacheInterface $cache Cache settings to speed things up
     */
    public function __construct(RelationalDbInterface $database, CacheInterface $cache)
    {
        $this->database = $database;
        $this->cache = $cache;
    }

    /**
     * Get a setting by name
     *
     * @param string $name Unique name of the setting value to save
     * @param string $accountId The account id of the current tennant
     * @return string
     */
    public function get(string $name, string $accountId): ?string
    {
        // First try to get from cache (it's much faster that way)
        $ret = $this->getCached($accountId, $name);

        if (!$ret) {
            $ret = $this->getDb($name);
        }

        return $ret;
    }

    /**
     * Bypass any cache to get a setting
     *
     * @param [type] $name
     * @return void
     */
    public function getNoCache(string $name): ?string
    {
        return $this->getDb($name);
    }

    /**
     * Set a setting by name
     *
     * @param string $name Unique name of the setting value to save
     * @param string $value Value to store
     * @param string $accountId The account id of the current tennant
     * @return bool true on success, false on failure
     */
    public function set(string $name, string $value, string $accountId)
    {
        if ($value === null) {
            $value = '';
        }

        // First save to the database and make sure it was a success
        $ret = $this->saveDb($name, $value);

        // Now save to cache for later retieval
        if ($ret) {
            $this->setCache($accountId, $name, $value);
        }

        return $ret;
    }

    public function getForTeam($teamId, $name)
    {
        // TODO: Implement
    }

    public function setForTeam($teamId, $name, $value)
    {
        // TODO: Implement
    }

    /**
     * Get a setting for a user by name
     *
     * @param UserEntity $user
     * @param string $name
     * @return string
     */
    public function getForUser(UserEntity $user, $name)
    {
        // First try to get from cache (it's much faster that way)
        $ret = $this->getCached($user->getAccountId(), $name, $user->getEntityId());

        if ($ret === null) {
            $ret = $this->getDb($name, $user->getEntityId());
        }

        return $ret;
    }

    /**
     * Set a setting by name for a specific user
     *
     * @param UserEntity $user
     * @param string $name
     * @param mixed $value
     * @return bool true on success, false on failure
     */
    public function setForUser(UserEntity $user, string $name, $value)
    {
        // First save to the database and make sure it was a success
        $ret = $this->saveDb($name, $value, $user->getEntityId());

        // Now save to cache for later retieval
        if ($ret) {
            $this->setCache($user->getAccountId(), $name, $value, $user->getEntityId());
        }

        return $ret;
    }

    /**
     * Get a setting from cache if it is set
     *
     * @param string $accountId The account id of the current tennant
     * @param string $name Unique name of the setting value that we are getting
     * @param int $userId Optional user id if this is a user setting
     * @return mixed
     */
    private function getCached(string $accountId, string $name, $userId = null)
    {
        $key = $this->getCachedKey($accountId, $name, $userId);
        return $this->cache->get($key);
    }

    /**
     * Save a setting to cache
     *
     * @param string $accountId The account id of the current tennant
     * @param string $name Unique name of the setting value to save
     * @param string $value Value to store
     * @param int $userId Optional user id if this is a user setting
     */
    private function setCache(string $accountId, string $name, $value, $userId = null)
    {
        $key = $this->getCachedKey($accountId, $name, $userId);
        $this->cache->set($key, $value);
    }

    /**
     * Construct a unique key to store the cache in
     *
     * @param string $accountId The account id of the current tennant
     * @param $name The unique name of the settings key
     * @param int $userId Optional user id
     * @param int $teamId Optional team id
     * @return string
     */
    private function getCachedKey(string $accountId, string $name, $userId = null)
    {
        // Namespace by account id
        $cachedKey = $accountId;

        // if user-specific then prefix with the user id
        if ($userId) {
            $cachedKey .= "/users/" . $userId;
        }

        $cachedKey .= "/settings";

        return $cachedKey . "/" . $name;
    }

    /**
     * Save a setting in the database
     *
     * @param string $name The unique setting name
     * @param string $value The value to save
     * @param string $userId Optional user id to save the setting for
     * @param string $teamId Optional team id to save the setting for
     * @return bool true on success, false on failure
     */
    private function saveDb(string $name = '', string $value = '', string $userId = '', string $teamId = '')
    {
        // Set the parameters
        $params = ["name" => $name];
        $settingData = ["value" => $value];

        $sql = "SELECT id FROM settings WHERE name=:name";

        // Either add a user or explicitely exclude it
        if ($userId) {
            $sql .= " AND user_id=:user_id";
            $params["user_id"] = $userId;
        } else {
            $sql .= " AND user_id IS NULL";
        }

        $result = $this->database->query($sql, $params);
        if ($result->rowCount()) {
            $row = $result->fetch();
            $this->database->update("settings", $settingData, ["id" => $row['id']]);
        } else {
            $this->database->insert("settings", array_merge($params, $settingData));
        }

        return true;
    }

    /**
     * Get a value from the account database
     *
     * @param string $name The unique setting name
     * @param string $userId Optional user id to save the setting for
     * @param string $teamId Optional team id to save the setting for
     * @return null
     */
    private function getDb(string $name, string $userId = null, string $teamId = null)
    {
        $params = ["name" => $name];
        $sql = "SELECT value FROM settings WHERE name=:name";

        // Either add a user or explicitely exclude it
        if ($userId) {
            $sql .= " AND user_id=:user_id";
            $params["user_id"] = $userId;
        } else {
            $sql .= " AND user_id IS NULL";
        }

        $result = $this->database->query($sql, $params);
        if ($result->rowCount()) {
            $row = $result->fetch();
            return $row['value'];
        }

        return null;
    }
}

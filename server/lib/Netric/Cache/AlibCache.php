<?php
/*
 * This class will abstract cache methods get, and delete from whatever engine we choose to use
 * 
 *  @author Sky Stebnicki <sky.stebnicki@aereus.com>
 *  @copyright 2014 Aereus
 */
namespace Netric\Cache;

// /Legacy include
require_once(dirname(__FILE__) . "/../../AntConfig.php");
require_once(dirname(__FILE__) . "/../../aereus.lib.php/CCache.php");

/**
 * Legacy cache using alib which has a number of engines including memcached.
 *
 * @author Sky Stebnicki
 */
class AlibCache implements CacheInterface
{
    /**
     * Handle to CCache aereus.lib.php class
     * 
     * Abstracting this so we can easily move to custom or another driver later
     * 
     * @var CCache
     */
    private $cache = null;
    
    public function __construct() {
        $this->cache = \CCache::getInstance();
    }
    /**
     * Set a value to the cache
     *
     * @param string $key Unique key for referencing the value
     * @param string $value The value to store
     * @param int $expires Number of seconds to expire cache or 0 for never
     * @return boolean true on success, false on failure
     */
    public function set($key, $value, $expires=0)
    {
        return $this->cache->set($key, $value);
    }
    
    /**
     * Get a value from cache by key
     * 
     * @param type $key The unique key of the value to retrieve
     * @return string
     */
    public function get($key)
    {
        return $this->cache->get($key);
    }
    
    /**
     * Delete a value from cache by key
     * 
     * @param string $key Unique key to delete
     */
    public function delete($key)
    {
        return $this->cache->remove($key);
    }
    
    /**
     * Legacy passthrough to delete
     * 
     * @param string $key
     */
    public function remove($key)
    {
        $this->delete($key);
    }
}

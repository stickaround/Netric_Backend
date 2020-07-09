<?php

/**
 * Use memcached extension to store cache in a memcache server cluster
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2017 Aereus
 */

namespace Netric\Cache;

use Aereus\Config\Config;
use Memcached;

class MemcachedCache implements CacheInterface
{
    /**
     * Instance of Memcached
     *
     * @var \Memcached
     */
    private $memCached = null;

    /**
     * MemcachedCache constructor.
     *
     * @param Config $config The cache portion of the config
     */
    public function __construct($config)
    {
        $this->memCached = new Memcached();

        // Make sure servers are not already added
        if (!count($this->memCached->getServerList())) {
            if (is_array($config->host)) {
                $servers = [];
                foreach ($config->host as $svr) {
                    $servers[] = array($svr, 11211, 100);
                }

                $this->memCached->addServers($servers);
            } else {
                $this->memCached->addServer($config->host, 11211);
            }
        }
    }

    /**
     * Set a value to the cache
     *
     * @param string $key Unique key for referencing the value
     * @param string $value The value to store
     * @param int $expires Number of seconds to expire cache or 0 for never
     * @return boolean true on success, false on failure
     */
    public function set($key, $value, $expires = 0)
    {
        return $this->memCached->set($key, $value, $expires);
    }

    /**
     * Get a value from cache by key
     *
     * @param mixed $key The unique key of the value to retrieve
     * @return string
     */
    public function get($key)
    {
        return $this->memCached->get($key);
    }

    /**
     * Delete a key in the cache
     *
     * @param string $key
     * @return bool true on success, false on fail
     */
    public function delete($key)
    {
        return $this->memCached->delete($key);
    }

    /**
     * Legacy passthrough to delete
     *
     * @param string $key
     * @return bool true on success, false on fail
     */
    public function remove($key)
    {
        return $this->delete($key);
    }
}

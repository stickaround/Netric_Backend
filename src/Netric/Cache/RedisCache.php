<?php

/**
 * Use memcached extension to store cache in a memcache server cluster
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2017 Aereus
 */

namespace Netric\Cache;

use Aereus\Config\Config;
use Netric\PubSub\PubSubInterface;
use Redis;

class RedisCache implements CacheInterface, PubSubInterface
{
    /**
     * Instance of Memcached
     *
     * @var Redis
     */
    private $redis = null;

    /**
     * MemcachedCache constructor.
     *
     * @param Config $config The cache portion of the config
     */
    public function __construct($config)
    {
        $this->redis = new Redis();
        $this->redis->connect($config->host);
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
        $encodedValue = serialize($value);
        $ret = $this->redis->set($key, $encodedValue);

        if ($expires) {
            $this->redis->setEx($key, $expires, $encodedValue);
        }
        return $ret;
    }

    /**
     * Get a value from cache by key
     *
     * @param mixed $key The unique key of the value to retrieve
     * @return string
     */
    public function get($key)
    {
        $value = $this->redis->get($key);
        return ($value) ? unserialize($value) : "";
    }

    /**
     * Delete a key in the cache
     *
     * @param string $key
     * @return bool true on success, false on fail
     */
    public function delete($key)
    {
        return $this->redis->del($key) >= 1;
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

    /**
     * Publish to a redis topic
     *
     * @param string $topic
     * @param array $data
     * @return void
     */
    public function publish(string $topic, array $data): void
    {
        $this->redis->publish($topic, json_encode($data));
    }
}

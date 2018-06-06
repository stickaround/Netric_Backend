<?php
/*
 * Cache interface
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Cache;

interface CacheInterface
{
    /**
     * Set a value to the cache
     *
     * @param string $key Unique key for referencing the value
     * @param string $value The value to store
     * @param int $expires Number of seconds to expire cache or 0 for never
     * @return boolean true on success, false on failure
     */
    public function set($key, $value, $expires = 0);

    /**
     * Get a value from cache by key
     *
     * @param string $key The unique key of the value to retrieve
     * @return string
     */
    public function get($key);

    /**
     * Delete a value from cache by key
     *
     * @param string $key Unique key to delete
     */
    public function delete($key);

    /**
     * Legacy passthrough to delete
     *
     * @param string $key
     */
    public function remove($key);
}

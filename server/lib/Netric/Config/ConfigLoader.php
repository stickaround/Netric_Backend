<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Config;

/**
 * Construct a Config object from files
 */
class ConfigLoader
{
    /**
     * Load configuration files and merge them together into one configuration object
     *
     * Files will load in the following order and merged together:
     *
     * 1. global.php
     * 2. {$appEnv}.php
     * 3. local.php (developer overrides, should never be checked into repo)
     *
     * @param string $configPath Directory path that contains the config files
     * @param string $appEnv The name of the environment to load
     * @param array $params If set these params will be applied last after files
     * @return Config
     */
    public static function fromFolder($configPath, $appEnv="", array $params=array())
    {
        // Load and merge arrays
        $global = self::importFileArray($configPath . "/global.php");
        $env = self::importFileArray($configPath . "/" . $appEnv . ".php");
        $local = self::importFileArray($configPath . "/local.php");

        $merged = array_merge($global, $env, $local, $params);

        // Return merged config
        return new Config($merged);
    }

    /**
     * Load a configuration file and turn it into an array
     *
     * @todo Support loading ini files
     * @param string $filePath The path of the config file to load
     * @return array
     */
    public static function importFileArray($filePath)
    {
        if (file_exists($filePath)) {

            // Get the array from the file
            $data = include($filePath);

            // Throw an exception if the returned value is not an array
            if (!is_array($data)) {
                throw new Exception\RuntimeException("$filePath did not return an array");
            }

            return $data;

        } else {
            // Return an empty array to merge
            return array();
        }
    }
}

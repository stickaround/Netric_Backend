<?php

// Include netric autoloader for all netric libraries
require_once(dirname(__FILE__) . "/../../../../init_autoloader.php");

use Netric\Application\Application;
use Aereus\Config\ConfigLoader;


/**
 * Loader singleton to keep one copy of the netric application running for ZPUsh
 */
class NetricApplicationInit
{
    /**
     * Static netric Application instance
     *
     * @var Application
     */
    private static $applicationInstance = null;

    /**
     * Get the application
     *
     * This will init the Application if not set
     *
     * @return Application
     */
    public static function getApplication()
    {
        if (self::$applicationInstance) {
            return self::$applicationInstance;
        }

        /*
         * The netric application was not yet initialized so we'll need to
         * construct our own instance of the netric application logger
         */
        // Setup config
        $configLoader = new ConfigLoader();
        $applicationEnvironment = (getenv('APPLICATION_ENV')) ?
            getenv('APPLICATION_ENV') : "production";
        $config = $configLoader->fromFolder(
            dirname(__FILE__) . "/../../../../config",
            $applicationEnvironment
        );
        self::$applicationInstance = Application::init($config);
        $log = self::$applicationInstance->getLog();
        if (empty($log->getRequestId())) {
            $log->setRequestId(uniqid());
        }

        return self::$applicationInstance;
    }
}

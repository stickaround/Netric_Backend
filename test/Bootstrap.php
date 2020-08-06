<?php

namespace NetricTest;

// Get application autoloader
include(__DIR__ . "/../init_autoloader.php");

use Zend\Loader\StandardAutoloader;
use Aereus\Config\ConfigLoader;
use Netric\Application\Application;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;

error_reporting(E_ALL | E_STRICT);
chdir(__DIR__);

/**
 * Test bootstrap, for setting up autoloading
 */
class Bootstrap
{
    protected static $account;
    protected static $user;

    public static function init()
    {
        static::initAutoloader();

        // Initialize Netric Application and Account
        $configLoader = new ConfigLoader();

        // Setup the new config
        $config = $configLoader->fromFolder(__DIR__ . "/../config", 'testing');

        // Initialize application
        $application = new Application($config);

        // Initialize account
        static::$account = $application->getAccount(null, 'autotest');
        if (!static::$account) {
            static::$account = $application->createAccount(
                'autotest',
                "automated_test",
                "automated_test@netric.com",
                'password'
            );
        }

        // Get or create an administrator user so permissions are not limiting
        $user = self::$account->getUser(null, "automated_test");

        if (!$user) {
            // Create the default account
            $entityLoader = static::$account->getServiceManager()->get(EntityLoaderFactory::class);
            $adminUser = $entityLoader->create(ObjectTypes::USER, static::$account->getAccountId());
            $adminUser->setValue("name", 'automated_test');
            $adminUser->setValue("email", 'automated_test@netric.com');
            $adminUser->setValue("password", 'password');
            $adminUser->setIsAdmin(true);
            $entityLoader->save($adminUser, static::$account->getSystemUser());
        }

        static::$user = $user;
        static::$account->setCurrentUser($user);
    }

    public static function getAccount()
    {
        // Set the user each time since tests may have modified it
        static::$account->setCurrentUser(static::$user);
        return static::$account;
    }

    protected static function initAutoloader()
    {
        $autoLoader = new StandardAutoloader([
            'namespaces' => [
                __NAMESPACE__ => __DIR__ . '/' . __NAMESPACE__,
            ],
            'fallback_autoloader' => true,
        ]);
        $autoLoader->register();
    }

    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (!is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) {
                return false;
            }
            $previousDir = $dir;
        }
        return $dir . '/' . $path;
    }
}

Bootstrap::init();

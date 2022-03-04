<?php

namespace NetricTest;

// Get application autoloader
include(__DIR__ . "/../vendor/autoload.php");

use Aereus\Config\ConfigLoader;
use Netric\Account\AccountSetupFactory;
use Netric\Application\Application;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\UserEntity;
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
        // Initialize Netric Application and Account
        $configLoader = new ConfigLoader();

        // Setup the new config
        $config = $configLoader->fromFolder(__DIR__ . "/../config", 'testing');

        // Initialize application
        $application = new Application($config);

        // Initialize account
        static::$account = $application->getAccount(null, 'autotest');

        if (!static::$account) {
            $accountSetup = $application->getServiceManager()->get(AccountSetupFactory::class);
            static::$account = $accountSetup->createAndInitailizeNewAccount(
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

    /**
     * Get the created test user
     *
     * @return UserEntity
     */
    public static function getTestUser(): UserEntity
    {
        return static::$user;
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

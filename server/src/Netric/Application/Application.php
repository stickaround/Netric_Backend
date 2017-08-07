<?php
namespace Netric\Application;

use Netric\Account\Account;
use Netric\Application\Exception;
use Netric\Application\Setup\Setup;
use Netric\Request\RequestInterface;
use Netric\Mvc\Router;
use Netric\Config\Config;
use Netric\Log\LogInterface;
use Netric\Log\Log;
use Netric\Cache\MemcachedCache;
use Netric\Cache\CacheInterface;
use Netric\Account\AccountIdentityMapper;
use Netric\ServiceManager\ApplicationServiceManager;
use Netric\Entity\DataMapperInterface;

/**
 * Main application instance class
 */
class Application
{
    /**
     * Initialized configuration class
     *
     * @var Config
     */
    protected $config = null;

    /**
     * Application log
     *
     * We make it static so that it is not re-initialized any time an application instance
     * is loaded. This is especially useful when we want to mock the log out in unit tests
     * and make sure that all loaded instances of the Application inherit the mocked log.
     *
     * @var LogInterface
     */
    static protected $log = null;

    /**
     * Application DataMapper
     *
     * @var DataMapperInterface
     */
    private $dm = null;

    /**
     * Application cache
     * \
     *
     * @var CacheInterface
     */
    private $cache = null;

    /**
     * Accounts identity mapper
     *
     * @var AccountIdentityMapper
     */
    private $accountsIdentityMapper = null;

    /**
     * Request made when launching the application
     *
     * @var RequestInterface
     */
    private $request = null;

    /**
     * Application service manager
     *
     * @var ApplicationServiceManager
     */
    private $serviceManager = null;

    /**
     * The unique ID of this request
     *
     * @var string
     */
    private $requestId = null;

    /**
     * Initialize application
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        // start profiling if enabled
        if (extension_loaded('xhprof')) {
            xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
        }

        $this->config = $config;

        // Setup log
        if (!self::$log) {
            self::$log = new Log($config);
        }

        // Watch for error notices and log them
        set_error_handler(array(self::$log , "phpErrorHandler"));

        // Log unhandled exceptions
        set_exception_handler(array(self::$log, "phpUnhandledExceptionHandler"));

        // Watch for fatals which cause script execution to fail
        register_shutdown_function(array(self::$log, "phpShutdownErrorChecker"));

        // Setup the application service manager
        $this->serviceManager = new ApplicationServiceManager($this);

        // TODO: Convert the below to service factories

        // Setup application datamapper
        $this->dm = new DataMapperPgsql($config->db["host"],
            $config->db["sysdb"],
            $config->db["user"],
            $config->db["password"]
        );

        // Setup application cache
        $this->cache = new MemcachedCache($config->cache);

        // Setup account identity mapper
        $this->accountsIdentityMapper = new AccountIdentityMapper($this->dm, $this->cache);
    }

    /**
     * Initialize an instance of the application
     *
     * @param Config $config
     * @return Application
     */
    static public function init(Config $config)
    {
        return new Application($config);
    }

    /**
     * Run The application
     *
     * @param string $path Optional initial route to load
     */
    public function run($path = "")
    {
        // We give each request a unique ID in order to track calls and logs through the system
        $this->requestId = uniqid();

        // Add to every log to make tracking down problems easier
        self::$log->setRequestId($this->requestId);

        // Get the request
        $request = $this->serviceManager->get("Netric/Request/Request");

        // Get the router
        $router = new Router($this);

        // Check if we have set the first/initial route
        if ($path) {
            $request->setPath($path);
        }

        // Execute through the router
        $router->run($request);

        // Handle any profiling needed for this request
        $this->profileRequest();
    }

    /**
     * Get initialized config
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get the unique ID of this request
     *
     * @return string
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * Get current account
     *
     * @param string $accountId If set the pull an account by id, otherwise automatically get from url or config
     * @param string $accountName If set try to get an account by the unique name
     * @throws \Exception when an invalid account id or name is passed
     * @return Account
     */
    public function getAccount($accountId="", $accountName="")
    {
        // If no specific account is set to be loaded, then get current/default
        if (!$accountId && !$accountName)
            $accountName = $this->getAccountName();

        if (!$accountId && !$accountName)
            throw new \Exception("Cannot get account without accountName");

        // Get the account with either $accountId or $accountName
        $account = null;
        if ($accountId)
            $account = $this->accountsIdentityMapper->loadById($accountId, $this);
        else
            $account = $this->accountsIdentityMapper->loadByName($accountName, $this);

        return $account;
    }

    /**
     * Get all acounts for this application
     *
     * @return Account[]
     */
    public function getAccounts()
    {
        $config = $this->getConfig();
        $accountsData = $this->dm->getAccounts($config->version);

        $accounts = [];
        foreach ($accountsData as $data)
        {
            $accounts[] = $this->accountsIdentityMapper->loadById($data['id'], $this);
        }

        return $accounts;
    }


    /**
     * Get account and username from email address
     *
     * @param string $emailAddress The email address to pull from
     * @return array("account"=>"accountname", "username"=>"the login username")
     */
    public function getAccountsByEmail($emailAddress)
    {
        $accounts = $this->dm->getAccountsByEmail($emailAddress);

        // Add instanceUri
        for ($i = 0; $i < count($accounts); $i++)
        {
            $proto = ($this->config->force_https) ? "https://" : "http://";
            $accounts[$i]['instanceUri'] = $proto . $accounts[$i]["account"] . "." . $this->config->localhost_root;
        }

        return $accounts;
    }

    /**
     * Set account and username from email address
     *
     * @param int $accountId The id of the account user is interacting with
     * @param string $username The user name - unique to the account
     * @param string $emailAddress The email address to pull from
     * @return bool true on success, false on failure
     */
    public function setAccountUserEmail($accountId, $username, $emailAddress)
    {
        return $this->dm->setAccountUserEmail($accountId, $username, $emailAddress);
    }

    /**
     * Determine what account we are working with.
     *
     * This is usually done by the third level url, but can fall
     * all the way back to the system default account if needed.
     *
     * @return string The unique account name for this instance of netric
     */
    private function getAccountName()
    {
        global $_SERVER, $_GET, $_POST, $_SERVER;

        $ret = null;

        // Check url - 3rd level domain is the account name
        if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] != $this->getConfig()->localhost_root
            && strpos($_SERVER['HTTP_HOST'], "." . $this->getConfig()->localhost_root))
        {
            $left = str_replace("." . $this->getConfig()->localhost_root, '', $_SERVER['HTTP_HOST']);
            if ($left)
                return $left;
        }

        // Check get - less common
        if (isset($_GET['account']) && $_GET['account'])
        {
            return $_GET['account'];
        }

        // Check post - less common
        if (isset($_POST['account']) && $_POST['account'])
        {
            return $_POST['account'];
        }

        // Check for any third level domain (not sure if this is safe)
        if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] && substr_count($_SERVER['HTTP_HOST'], '.')>=2)
        {
            $left = substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], '.'));
            if ($left)
                return $left;
        }

        // Get default account from the system settings
        return $this->getConfig()->default_account;
    }

    /**
     * Initialize a brand new account and create the admin user
     *
     * @param string $accountName A unique name for the new account
     * @param string $adminUserName Required username for the admin/first user
     * @param string $adminUserPassword Required password for the admin
     * @return Account
     */
    public function createAccount($accountName, $adminUserName, $adminUserPassword)
    {
        // Make sure the account does not already exists
        if ($this->accountsIdentityMapper->loadByName($accountName, $this))
        {
            throw new Exception\AccountAlreadyExistsException($accountName . " already exists");
        }

        // TODO: Check the account name is valid

        // Create new account
        $accountId = $this->accountsIdentityMapper->createAccount($accountName);

        // Make sure the created account is valid
        if (!$accountId)
        {
            throw new Exception\CouldNotCreateAccountException(
                "Failed creating account " . $this->accountsIdentityMapper->getLastError()->getMessage()
            );
        }

        // Load the newly created account
        $account = $this->accountsIdentityMapper->loadById($accountId, $this);

        // Initialize with setup
        $setup = new Setup();
        $setup->setupAccount($account, $adminUserName, $adminUserPassword);

        // TODO: 3. Change status to active

        // Return the new account
        return $account;
    }

    /**
     * Delete an account by name
     *
     * @param string $accountName The unique name of the account to delete
     * @return bool on success, false on failure
     */
    public function deleteAccount($accountName)
    {
        // Get account by name
        $account = $this->getAccount(null, $accountName);

        // Delete the account if it is valid
        if ($account->getId())
        {
            return $this->accountsIdentityMapper->deleteAccount($account);
        }

        return false;
    }

    /**
     * Create the application database if it does not exist
     */
    public function initDb()
    {
        // Create database if it does not exist
        if (!$this->dm->createDatabase()) {
            throw new \RuntimeException(
                "Could not create application database: " .
                $this->dm->getLastError()->getMessage()
            );
        }

        // Initialize with setup
        $setup = new Setup();
        return $setup->updateApplication($this);
    }

    /**
     * Get the application service manager
     *
     * @return ApplicationServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Get the application log
     *
     * @return \Netric\Log\Log
     */
    public function getLog()
    {
        return self::$log;
    }

    /**
     * Get the application cache
     *
     * @return \Netric\Cache\CacheInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Get the request for this application
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Create a new email domain
     *
     * @param int $accountId
     * @param string $domainName
     * @return bool true on success, false on failure
     */
    public function createEmailDomain($accountId, $domainName)
    {
        return $this->dm->createEmailDomain($accountId, $domainName);
    }

    /**
     * Delete an existing email domain
     *
     * @param int $accountId
     * @param string $domainName
     * @return bool true on success, false on failure
     */
    public function deleteEmailDomain($accountId, $domainName)
    {
        return $this->dm->deleteEmailDomain($accountId, $domainName);
    }

    /**
     * Create or update an email alias
     *
     * @param int $accountId
     * @param string $emailAddress
     * @param string $goto
     * @return bool true on success, false on failure
     */
    public function createOrUpdateEmailAlias($accountId, $emailAddress, $goto)
    {
        return $this->dm->createOrUpdateEmailAlias($accountId, $emailAddress, $goto);
    }

    /**
     * Delete an email alias
     *
     * @param int $accountId
     * @param string $emailAddress
     * @return bool true on success, false on failure
     */
    public function deleteEmailAlias($accountId, $emailAddress)
    {
        return $this->dm->deleteEmailAlias($accountId, $emailAddress);
    }

    /**
     * Create a new or update an existing email user in the mail system
     *
     * @param int $accountId
     * @param string $emailAddress
     * @param string $password
     * @return bool true on success, false on failure
     */
    public function createOrUpdateEmailUser($accountId, $emailAddress, $password)
    {
        return $this->dm->createOrUpdateEmailUser($accountId, $emailAddress, $password);
    }

    /**
     * Delete an email user from the mail system
     *
     * @param int $accountId
     * @param string $emailAddress
     * @return bool true on success, false on failure
     */
    public function deleteEmailUser($accountId, $emailAddress)
    {
        return $this->dm->deleteEmailUser($accountId, $emailAddress);
    }

    /**
     * Obtain a lock so that only one instance of a process can run at once
     *
     * @param string $uniqueLockName Globally unique lock name
     * @param int $expiresInMs Expire after defaults to 1 day or 86400000 milliseconds
     * @return bool true if lock obtained, false if the process name is already locked (running)
     */
    public function acquireLock($uniqueLockName, $expiresInMs=86400000)
    {
        return $this->dm->acquireLock($uniqueLockName, $expiresInMs);
    }

    /**
     * Clear a lock so that only one instance of a process can run at once
     *
     * @param string $uniqueLockName Globally unique lock name
     */
    public function releaseLock($uniqueLockName)
    {
        $this->dm->releaseLock($uniqueLockName);
    }

    /**
     * Handle profiling this request if enabled
     */
    private function profileRequest()
    {
        if (!extension_loaded('xhprof') || $this->config->profile->enabled !== false) {
            return;
        }

        // Stop profiler and get data
        $xhprofData = xhprof_disable();

        // Loop through each function profiled
        foreach ($xhprofData as $functionAndCalledFrom=>$stats) {
            // If the total walltime (duration) of the function is worth tracking then log
            if ((int) $stats['wt'] >= (int) $this->config->profile->min_wall) {

                /*
                 * xhprof puts the key in the following form: <calledFrom>==><calss_function_called>
                 * Offset 0 will be 'called_from' and 1 will be 'function_name'
                 */
                $parts = explode("==>", $functionAndCalledFrom);

                // If there is no function name then this is the main root profile
                if (empty($parts[1])) {
                    $parts[1] = "main()";
                    $parts[0] = "";
                }

                $profileData = array(
                    "type" => "profile",
                    "function_name" => $parts[1],
                    "called_from" => $parts[0],
                    "num_calls" => $stats['ct'],
                    "duration" => $stats['wt'],
                    "cputime" => $stats['cpu'],
                    "memoryused" => $stats['mu'],
                    "peakmemoryused" => $stats['pmu'],
                );
                self::$log->warning($profileData);
            }
        }

        /*
         * TODO: Add a setting for saving the full profile dump in development environments
         * Then we can create a container in netric just for xhprof so that developers
         * can load up any request to see the full profile and determine where performance
         * issues might be taking place.
         */
        if ($this->config->profile->save_profile) {
            $file_name = __DIR__ . '/../../../data/profile_runs/' . $this->getRequestId() . '.netric.xhprof';
            $file = fopen($file_name, 'w');
            if ($file) {
                // Use PHP serialize function to store the XHProf's
                fwrite($file, serialize($xhprofData));
                fclose($file);
            }
        }
    }
}

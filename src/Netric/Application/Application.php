<?php

namespace Netric\Application;

use Thrift\Factory\TTransportFactory;
use Thrift\Factory\TBinaryProtocolFactory;
use Thrift\Transport\TBufferedTransport;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TPhpStream;
use Netric\Account\Account;
use Netric\Application\Exception;
use Netric\Mvc\Exception\NotAuthorizedForRouteException;
use Netric\Request\RequestInterface;
use Netric\Mvc\Router;
use Aereus\Config\Config;
use Netric\Log\LogInterface;
use Netric\Log\Log;
use Netric\Cache\CacheInterface;
use Netric\Account\AccountContainer;
use Netric\Account\AccountContainerFactory;
use Netric\ServiceManager\ApplicationServiceManager;
use Netric\Entity\DataMapper\EntityDataMapperInterface;
use Netric\Stats\StatsPublisher;
use Netric\Request\RequestFactory;
use Netric\Cache\CacheFactory;
use Netric\Authentication\AuthenticationServiceFactory;

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
    protected static $log = null;

    /**
     * Application DataMapper
     *
     * @var EntityDataMapperInterface
     */
    private $dm = null;

    /**
     * Application cache
     *
     * @var CacheInterface
     */
    private $cache = null;

    /**
     * Accounts identity mapper
     *
     * @var AccountContainer
     */
    private $accountContainer = null;

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
            self::$log = new Log($config->log);
        }

        // Watch for error notices and log them
        set_error_handler([self::$log, "phpErrorHandler"]);

        // Log unhandled exceptions
        set_exception_handler(array(self::$log, "phpUnhandledExceptionHandler"));

        // Watch for fatal errors which cause script execution to fail
        register_shutdown_function(array(self::$log, "phpShutdownErrorChecker"));

        // Setup the application service manager
        $this->serviceManager = new ApplicationServiceManager($this);

        // Setup application datamapper
        $this->dm = $this->serviceManager->get(DataMapperFactory::class);

        // Setup application cache
        $this->cache = $this->serviceManager->get(CacheFactory::class);

        // Setup account identity mapper
        $this->accountContainer = $this->serviceManager->get(AccountContainerFactory::class);
    }

    /**
     * Initialize an instance of the application
     *
     * @param Config $config
     * @return Application
     */
    public static function init(Config $config)
    {
        return new Application($config);
    }

    /**
     * Run The application
     *
     * @param string $path Optional initial route to load
     * @return int Return status code
     */
    public function run($path = ""): int
    {
        $returnStatusCode = 0;

        // We give each request a unique ID in order to track calls and logs through the system
        $this->requestId = uniqid();

        // Add to every log to make tracking down problems easier
        self::$log->setRequestId($this->requestId);

        // Get the request
        $request = $this->serviceManager->get(RequestFactory::class);

        // Get the router
        $router = new Router($this);

        // Check if we have set the first/initial route
        if ($path) {
            $request->setPath($path);
        }

        // Execute through the router
        try {
            $response = $router->run($request);
            // Fail the run if the response code is not successful
            $returnStatusCode = $response->getReturnCode();
        } catch (NotAuthorizedForRouteException $NotAuthorizedException) {
            header('HTTP/1.1 401 Unauthorized');
            print($NotAuthorizedException->getMessage());
            $returnStatusCode = -1;
        } catch (\Exception $unhandledException) {
            // An exception took place and was not handled
            $this->getLog()->error(
                'Unhandled application exception in ' .
                    $unhandledException->getFile() .
                    ':' . $unhandledException->getLine() .
                    "; message=" . $unhandledException->getMessage() .
                    "\n" . $unhandledException->getTraceAsString()
            );

            // If we are suppressing logs then print out this exception
            //if ($this->config->log->writer == 'null') {
            print($this->config->log->writer . "\n" .
                'Unhandled application exception in ' .
                $unhandledException->getFile() .
                ':' . $unhandledException->getLine() .
                "; message=" . $unhandledException->getMessage() .
                "\n" . $unhandledException->getTraceAsString());
            //}

            // Fail
            $returnStatusCode = -1;
        }

        // Handle any profiling needed for this request
        $this->profileRequest();
        return $returnStatusCode;
    }

    /**
     * Run The application through thrift
     *
     * @param string $path Optional initial route to load
     * @return int Return status code
     */
    public function runThrift(): int
    {
        $returnStatusCode = 0;

        // We give each request a unique ID in order to track calls and logs through the system
        $this->requestId = uniqid();

        // Add to every log to make tracking down problems easier
        self::$log->setRequestId($this->requestId);

        // Get the request
        $request = $this->serviceManager->get(RequestFactory::class);

        if (!$request->getParam('handler')) {
            header('HTTP/1.1 404 No Handler Specified');
            // No need to process the profiler
            return -1;
        }

        // Try executing
        try {
            // Normalize the name
            $handlerName = $request->getParam('handler');
            $handlerName = str_replace("_", " ", $handlerName);
            $handlerName = str_replace("-", " ", $handlerName);
            $handlerName = ucwords($handlerName);
            $handlerName = str_replace(" ", "", $handlerName);

            $handlerClass = "Netric\\Handler\\${handlerName}Handler";
            $processorClass = "NetricApi\\${handlerName}Processor";

            // Try loading the process and handler classes
            // Handler is created in Netric, the Processor is auto-generated
            if (
                !class_exists($handlerClass) ||
                !class_exists($processorClass)
            ) {
                header("HTTP/1.1 404 ${handlerName} not found");
                // No need to process the profiler
                return -1;
            }

            $handler = $this->serviceManager->get($handlerClass . "Factory");
            $processor = new $processorClass($handler);

            $transportFactory = new TTransportFactory();
            $protocolFactory = new TBinaryProtocolFactory(true, true);
            $transport = new TBufferedTransport(new TPhpStream(TPhpStream::MODE_R | TPhpStream::MODE_W));
            $protocol = new TBinaryProtocol($transport, true, true);

            header('Content-Type', 'application/x-thrift');
            $transport->open();
            $processor->process($protocol, $protocol);
            $transport->close();
        } catch (NotAuthorizedForRouteException $NotAuthorizedException) {
            header('HTTP/1.1 401 Unauthorized');
            print($NotAuthorizedException->getMessage());
            $returnStatusCode = -1;
        } catch (\Exception $unhandledException) {
            // An exception took place and was not handled
            $this->getLog()->error(
                'Unhandled application exception in ' .
                    $unhandledException->getFile() .
                    ':' . $unhandledException->getLine() .
                    "; message=" . $unhandledException->getMessage() .
                    "\n" . $unhandledException->getTraceAsString()
            );

            // If we are suppressing logs then print out this exception
            header('HTTP/1.1 403 Unhandled exception');
            print($this->config->log->writer . "\n" .
                'Unhandled application exception in ' .
                $unhandledException->getFile() .
                ':' . $unhandledException->getLine() .
                "; message=" . $unhandledException->getMessage() .
                "\n" . $unhandledException->getTraceAsString());
            //}

            // Fail
            $returnStatusCode = -1;
        }

        // Handle any profiling needed for this request
        $this->profileRequest();
        return $returnStatusCode;
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
    public function getAccount($accountId = "", $accountName = "")
    {
        // If no specific account is set to be loaded, then get current/authenticated
        if (!$accountId && !$accountName) {
            $authService = $this->serviceManager->get(AuthenticationServiceFactory::class);
            $authIdentity = $authService->getIdentity();
            if ($authIdentity) {
                $accountId = $authIdentity->getAccountId();
            }

            if (!$accountId) {
                $accountName = $this->getAccountName();
            }
        }

        if (!$accountId && !$accountName) {
            return null;
        }

        // Get the account with either $accountId or $accountName
        if ($accountId) {
            return $this->accountContainer->loadById($accountId);
        }

        return $this->accountContainer->loadByName($accountName);
    }

    /**
     * Get all acounts for this application
     *
     * @return Account[]
     */
    public function getAccounts()
    {
        $config = $this->getConfig();
        $accountsData = $this->dm->getAccounts();

        $accounts = [];
        foreach ($accountsData as $data) {
            $accounts[] = $this->accountContainer->loadById($data['account_id']);
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
        for ($i = 0; $i < count($accounts); $i++) {
            $proto = ($this->config->use_https) ? "https://" : "http://";
            $accounts[$i]['instanceUri'] = $proto . $accounts[$i]["account"] . "." . $this->config->localhost_root;
        }

        return $accounts;
    }

    /**
     * Set account and username from email address
     *
     * @param string $accountId The id of the account user is interacting with
     * @param string $username The user name - unique to the account
     * @param string $emailAddress The email address to pull from
     * @return bool true on success, false on failure
     */
    public function setAccountUserEmail(string $accountId, $username, $emailAddress)
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
    private function getAccountName(): string
    {
        global $_SERVER, $_GET, $_POST, $_SERVER, $_REQUEST;

        // First check if an account param was provided
        if (!empty($_REQUEST['X-NTRC-ACCOUNT'])) {
            return $_REQUEST['X-NTRC-ACCOUNT'];
        }

        // Now look for account header
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if (!empty($headers['X-NTRC-ACCOUNT'])) {
                return $headers['X-NTRC-ACCOUNT'];
            }

            // Some proxies covert to lower case
            if (!empty($headers['x-ntrc-account'])) {
                return $headers['x-ntrc-account'];
            }
        }

        // No account has been set or loaded
        return '';
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
     * Obtain a lock so that only one instance of a process can run at once
     *
     * @param string $uniqueLockName Globally unique lock name
     * @param int $expiresInSeconds Expire after defaults to 1 day or 86400 seconds
     * @return bool true if lock obtained, false if the process name is already locked (running)
     */
    public function acquireLock($uniqueLockName, $expiresInSeconds = 86400)
    {
        return $this->dm->acquireLock($uniqueLockName, $expiresInSeconds);
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
     * Refresh the lock to extend the expires timeout
     *
     * @param string $uniqueLockName Globally unique lock name
     * @return bool true on success, false on failure
     */
    public function extendLock($uniqueLockName)
    {
        return $this->dm->extendLock($uniqueLockName);
    }

    /**
     * Handle profiling this request if enabled
     */
    private function profileRequest()
    {
        if (!extension_loaded('xhprof') || !$this->config->profile->enabled) {
            return;
        }

        // Stop profiler and get data
        $xhprofData = xhprof_disable();

        // Loop through each function profiled
        foreach ($xhprofData as $functionAndCalledFrom => $stats) {
            // If the total walltime (duration) of the function is worth tracking then log
            if ((int) $stats['wt'] >= (int) $this->config->profile->min_wall) {
                $functionCalled = $functionAndCalledFrom;
                $calledFrom = "";

                /*
                 * xhprof puts the key in the following form: <calledFrom>==><class_function_called>
                 * unless it is the main wrapper entry for the entire page, the key name will
                 * just be main()
                 */
                if ($functionCalled !== 'main()') {
                    list($functionCalled, $calledFrom) = explode("==>", $functionAndCalledFrom);
                }

                $profileData = [
                    "type" => "profile",
                    "function_name" => $functionCalled,
                    "called_from" => $calledFrom,
                    "num_calls" => $stats['ct'],
                    "duration" => $stats['wt'],
                    "cputime" => $stats['cpu'],
                    "memoryused" => $stats['mu'],
                    "peakmemoryused" => $stats['pmu'],
                ];
                self::$log->warning($profileData);
            }
        }

        // Send total request time to StatsD in ms (wall time is in microseconds)
        if (isset($xhprofData['main()'])) {
            $statNamePath = 'route' . str_replace("/", ".", $_SERVER['REQUEST_URI']);

            StatsPublisher::timing($statNamePath . '.responsetime', round($xhprofData['main()']['wt'] * 1000));
            StatsPublisher::timing($statNamePath . '.memoryused', $xhprofData['main()']['mu']);
            StatsPublisher::increment($statNamePath . '.hits');

            // Just track all service calls
            StatsPublisher::timing('api.responsetime', round($xhprofData['main()']['wt'] * 1000));
            StatsPublisher::timing('api.memoryused', $xhprofData['main()']['mu']);
            StatsPublisher::increment('api.hits');
        }

        /*
         * TODO: Add a setting for saving the full profile dump in development environments
         * Then we can create a container in netric just for xhprof so that developers
         * can load up any request to see the full profile and determine where performance
         * issues might be taking place.
         */

        if ($this->config->profile->save_profiles) {
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

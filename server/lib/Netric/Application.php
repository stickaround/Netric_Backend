<?php
/**
 * This is the base netric system class
 */
namespace Netric;

class Application
{
    /**
     * Initialized configuration class
     * 
     * @var \Netric\Config
     */
    protected $config = null;

    /**
     * Application log
     *
     * @var \Netric\Log
     */
    protected $log = null;

    
    /**
     * Application DataMapper
     * 
     * @var \Netric\Application\DataMapperInterface
     */
    private $dm = null;

    /**
     * Application cache
     * \
     *
     * @var \Netric\Cache\CacheInterface
     */
    private $cache = null;

    /**
     * Accounts identity mapper
     *
     * @var \Netric\Account\AccountIdentityMapper
     */
    private $accountsIdentityMapper = null;
    
    /**
     * Initialize application
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        // Setup log
        $this->log = new Log($config);

        // Setup error handler if not in a unit test
        if (!class_exists('\PHPUnit_Framework_TestCase'))
        {
            // Watch for error notices and log them
            set_error_handler(array($this->log, "phpErrorHandler"));
            // Log unhandled exceptions
            set_exception_handler(array($this->log, "phpUnhandledExceptionHandler"));
            // Watch for fatals which cause script execution to fail
            register_shutdown_function(array($this->log, "phpShutdownErrorChecker"));
        }
                
        // Setup antsystem datamapper
        $this->dm = new Application\DataMapperPgsql($config->db["host"], 
                                                    $config->db["sysdb"], 
                                                    $config->db["user"], 
                                                    $config->db["password"]);

        // Setup application cache
        $this->cache = new Cache\AlibCache();

        // Setup account identity mapper
        $this->accountsIdentityMapper = new Account\AccountIdentityMapper($this->dm, $this->cache);
    }
    
    /**
     * Get initialized config
     * 
     * @return Netric\Config
     */
    public function getConfig()
    {
        return $this->config;
    }
    
    /**
     * Get current account
     * 
     * @param string $accountId If set the pull an account by id, otherwise automatically get from url or config
     * @param string $name If set try to get an account by the unique name
     * @throws \Exception when an invalid account id or name is passed
     * @return Netric\Account
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

		// 1 check session
		$ret = $this->getSessionVar('aname');
        if ($ret)
            return $ret;

		// 2 check url - 3rd level domain is the account name
		if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] != $this->getConfig()->localhost_root 
			 && strpos($_SERVER['HTTP_HOST'], "." . $this->getConfig()->localhost_root))
		{
			$left = str_replace("." . $this->getConfig()->localhost_root, '', $_SERVER['HTTP_HOST']);
            if ($left)
				return $left;
		}
		
		// 3 check get - less common
		if (isset($_GET['account']) && $_GET['account'])
		{
			return $_GET['account'];
		}

		// 4 check post - less common
		if (isset($_POST['account']) && $_POST['account'])
		{
			return $_POST['account'];
		}

		// 5 check for any third level domain (not sure if this is safe)
		if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] && substr_count($_SERVER['HTTP_HOST'], '.')>=2)
		{
			$left = substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], '.'));
			if ($left)
                return $left;
		}

		// 6 get default account from the system settings
		return $this->getConfig()->default_account;
	}
    
    /**
	 * Get session variable if exists
	 *
	 * These functions can be called statically
	 * This currently uses cookies for sessions
	 *
	 * @param string $name The name of the session variable to get
	 * @return string The value of the session variable
	 */
	public function getSessionVar($name)
	{
		global $_COOKIE;
        
        if(isset($_COOKIE[$name]) && $_COOKIE[$name])
		    return base64_decode($_COOKIE[$name]);
        else
            return null;
	}

	/**
	 * Set session variable
	 *
	 * This function can be called statically
	 * This currently uses cookies for sessions
	 *
	 * @param string $name The name of the session variable to get
	 * @param string $value The value to set the names variable to
	 * @param int $expires Set the number of seconds until this expires
	 */
	public function setSessionVar($name, $value, $expires=null)
	{
		setcookie($name, base64_encode($value), $expires);
	}

    /**
     * Get the application log
     *
     * @return \Netric\Log
     */
    public function getLog()
    {
        return $this->log;
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
}

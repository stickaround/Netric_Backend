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
     * @var Netric\Config
     */
    protected $config = null;
    
    /**
     * Instances of a netric accounts
     *
     * @var Netric\Account[]
     */
    protected $accounts = array();
    
    /**
     * Application DataMapper
     * 
     * @var Netric\Application\DataMapperInterface
     */
    private $dm = null;
    
    /**
     * Initialize application
     *
     * @param \Netric\Netric\Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
                
        // Setup antsystem datamapper
        $this->dm = new Application\DataMapperPgsql($config->db["host"], 
                                                    $config->db["sysdb"], 
                                                    $config->db["user"], 
                                                    $config->db["password"]);
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
     * @return Netric\Account
     */
    public function getAccount($accountId="", $name="")
    {
        // If no specific account is set to be loaded, then get current/default
        if (!$accountId && !$name)
            $name = $this->getAccountName();
        
        // Check to see if account is already loaded
        if ($accountId)
        {
            foreach ($this->accounts as $aname=>$acc)
            {
                if ($acc->getId() == $accountId)
                    return $acc;
            }
        }
        else
        {
            if (isset($this->accounts[$name]))
                return $this->accounts[$name];
        }
        
        if (!$accountId && !$name)
            throw new \Exception("Cannot get account without name");
        
        // Account has not yet been loaded
        $account = new Account($this);
        $ret = ($accountId) ? $this->dm->getAccountById($accountId, $account) : $this->dm->getAccountByName($name, $account);
        if (!$ret)
            return false;
        
        // Cache for later queries
        $this->accounts[$account->getName()] = $account;
        
        return $account;
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
}

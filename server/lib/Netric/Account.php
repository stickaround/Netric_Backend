<?php
/**
 * Netric account instance
 */
namespace Netric;

class Account
{
    /**
     * Unique account ID
     * 
     * @var string
     */
    private $id = "";
    
    /**
     * Unique account name
     * 
     * @var string
     */
    private $name = "";
    
    /**
     * The name of the database
     * 
     * @var string
     */
    private $dbname = "netric";
    
    /**
     * Instance of netric application
     * 
     * @var Application
     */
    private $application = null;
    
    /**
     * Handle to service manager for this account
     * 
     * @var Netric\ServiceManager\ServiceLocatorInterface
     */
    private $serviceManager = null;

    /**
     * Property to set the current user rather than using the auth service
     * 
     * @var Netric\Entity\ObjType\UserEntity
     */
    public $currentUserOverride = null;

    /**
     * The status of this account
     *
     * @var int
     */
    private $status = null;
    const STATUS_ACTIVE = 1;
    const STATUS_EXPIRED = 2;
    const STATUS_DELETED = 3;
    
    /**
     * Initialize netric account
     * 
     * @param \Netric\Application $app
     */
    public function __construct(Application $app)
    {
        $this->application = $app;
        
        $this->serviceManager = new \Netric\ServiceManager\ServiceManager($this);

        // Set default status
        $this->status = self::STATUS_ACTIVE;
    }

    /**
     * Load application data from an associative array
     * 
     * @param array $data
     * @return bool true on successful load, false on failure
     */
    public function fromArray($data)
    {
        // Check required fields
        if (!$data['id'] || !$data['name'])
            return false;
        
        $this->id = $data['id'];
        $this->name = $data['name'];
        if ($data['database'])
            $this->dbname = $data['database'];
                
        return true;
    }

    /**
     * Export internal properties to an associative array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            "id" => $this->id,
            "name" => $this->name,
            "database" => $this->dbname,
        );
    }
    
    /**
     * Get account id
     * 
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Get account unique name
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Get database name for this account
     * 
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->dbname;
    }
    
    /**
     * Get ServiceManager for this account
     * 
     * @return Netric\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }
    
    /**
     * Get application object
     * 
     * @return \Netric\Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Override the currently authenticated user with a specific user
     *
     * This is often used in testing and in background services where
     * there is no current authenticated user but we need to setup one
     * manually for act on behalf of a user.
     *
     * @param \Netric\Entity\ObjType\UserEntity $user
     */
    public function setCurrentUser(\Netric\Entity\ObjType\UserEntity $user)
    {
        $this->currentUserOverride = $user;
    }
    
    /**
     * Get user by id or name
     * 
     * If neither id or username are defined, then try to get the currently authenticated user.
     * If no users are authenticated, then this function will return false.
     * 
     * @param string $userId The userId of the user to get
     * @param string $username Get user by name
     * @return \Netric\User|bool user on success, false on failure
     */
    public function getUser($userId=null, $username=null)
    {      
        // Check to see if we have manually set the current user and if so skip session auth
        if ($this->currentUserOverride)
            return $this->currentUserOverride;

        // Entity loader will be needed once we have determined a user id to load
        $loader = $this->getServiceManager()->get("EntityLoader");
        
        /*
         * Try to get the currently logged in user from the authentication service if not provided
         */
        if (!$userId && !$username) 
        {
            // Get the authentication service
            $auth = $this->getServiceManager()->get("Netric/Authentication/AuthenticationService");

            // Check if the current session is authenticated
            $userId = $auth->getIdentity();
        } 

        /*
         * Load the user with the loader service.
         * This makes it unnecessary to cache the current user locally
         * since the loader handles making sure there is only one instance
         * of each user object in memory.
         */
        if ($userId)
        {
            $user = $loader->get("user", $userId);
            if ($user != false)
            {
                return $user;
            }
        }
        elseif ($username) 
        {
            // TODO: query based on username
            throw new \RuntimeException("Loading a user by username is not yet supported");
        }
                
        // Get anonymous user
        return $loader->get("user", \Netric\Entity\ObjType\UserEntity::USER_ANONYMOUS);
    }

    /**
     * Set account and username for a user's email address and username
     *
     * @param string $username The user name - unique to the account
     * @param string $emailAddress The email address to pull from
     * @return bool true on success, false on failure
     */
    public function setAccountUserEmail($username, $emailAddress)
    {
        return $this->application->setAccountUserEmail($this->getId(), $username, $emailAddress);
    }

    /**
     * Get the url for this account
     *
     * @param bool $includeProtocol If true prepend the default protocol
     * @return string A url like https://aereus.netric.com
     */
    public function getAccountUrl($includeProtocol = true)
    {
        // Get application config
        $config = $this->getServiceManager()->get("Config");

        // Initialize return value
        $url = "";

        // Prepend protocol
        if ($includeProtocol)
            $url .= ($config->force_https) ? "https://" : "http://";

        // Add account third level
        $url .= $this->name . ".";

        // Add the rest of the domain name
        $url .= $config->localhost_root;

        return $url;
    }
}
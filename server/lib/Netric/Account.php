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
     * Handle to the current user
     * 
     * @var Netric\Entity\ObjType\User
     */
    private $currentUser = null;
    
    /**
     * Initialize netric account
     * 
     * @param \Netric\Application $app
     */
    public function __construct(Application $app)
    {
        $this->application = $app;
        
        $this->serviceManager = new \Netric\ServiceManager\ServiceManager($this);
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
     * Get user by id or name
     * 
     * If neither id or username are defined, then try to get the currently authenticated user.
     * If no users are authenticated, then this function will return false.
     * 
     * @param string $id The id of the user to get
     * @param string $username Get user by name
     * @return \Netric\User|bool user on success, false on failure
     */
    public function getUser($id="", $username="")
    {
        $loader = $this->getServiceManager()->get("EntityLoader");
        
        /**
         * Try to get the currently logged in user
         */
        if (!$id && !$username) {
            // First check to see if we already loaded
            if ($this->currentUser)
                return $this->currentUser;
            
            // Try to get currently authenticated user
            $userId = $this->getApplication()->getSessionVar('uid');
            $userName = $this->getApplication()->getSessionVar('uname');
            
            if ($userId)
            {
                $user = $loader->get("user", $userId);
                if ($user != false)
                {
                    $this->currentUser = $user;
                    return $user;
                }
            }
        }
        
        if ($id) 
        {
            $user = $loader->get("user", $id);
            if ($user != false)
            {
                $this->currentUser = $user;
                return $user;
            }
        }
        
        if ($username) {
            
        }
                
        return false;
    }
}
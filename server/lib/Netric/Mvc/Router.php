<?php
/**
 * Interfaces RPC requests via query url variable function=functionName to functions in a class
 *
 * Use this class to expose methods of any class to a script called by url, usually an ajax client.
 * It is recommended that all exposed actions/methods return either void or true/false and simply
 * print the desired results directly from the class. However, RpcSvr::run will return whatever the
 * actual return value of the function is so implementations of this class can decide to return values
 * then print them from the calling script.
 *
 * Usage Example:
 *
 * Lets assume that the class ServerClassName as a function called 'addContact' that we would like to call
 * with params for adding a new contact. The function definition would be "new public function addContact($params)"
 * inslide the ServerClassName class. The class constuctor must take two params, CAnt and AntUser. A handle to the
 * account database can be obtained from the CAnt->dbh property.
 *
 * // We will add mock request params for testing purposes
 * $_REQUEST['function'] = "addContact"; // this is normally set through a URL query variable ?function=addContact
 * $_REQUEST['firstName'] = "test"; // this will be passed in the $params variable of the method called
 *
 * // In the called script, for instance, /testsrv.php, include the RpcSvr and ServerClassName libraries and then do...
 * $svr = new RpcSvr($ANT, $USER); // User is optional, but ant is required
 * $svr->setClass("ServerClassName");
 * $sve->run();
 *
 * // You can also refer to /tests/rpcsvr.php for a working example
 *
 * @category  ANT Library
 * @package   RpcSvr
 * @copyright Copyright (c) 2003-2014 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\Mvc;

/**
 * Expose public class methods to calling script
 */
class Router
{
	/**
     * The name of the class to initiailize
     *
     * @var string
	 */
	private $className;

	/**
     * Handle to the created class
     *
     * @var mixed
	 */
	private $svrCls = null;

	/**
     * Reference to application calss
     *
     * @var \Netric\Application 
	 */
	private $application = null;

    /**
     * Determines if the class is run by unit test
     *
     * @var Boolean
     */
    public $testMode = false;

	/**
	 * Class constructor
     * 
     * @param Netric\Application $application Instance of application
	 */
	function __construct($application)
	{
        $this->application = $application;
	}

	/**
	 * Set the class to expose methods for
	 *
	 * @param string $clsname is the name of the class to load that will process server requests
	 */
	public function setClass($clsname)
	{
		$this->className = $clsname;
	}

	/**
	 * Execute methods in server class
	 *
     * @param string $fname The function name to execute
	 * @return true on success, false on failure
	 */
	public function run($fName)
	{
		global $_REQUEST;

		// Create new instance of class if it does not exist
		if ($this->className && !$this->svrCls)
		{
			$clsname = $this->className;
			$this->svrCls = new $clsname($this->application->getAccount());
            
            if(isset($this->svrCls->testMode))
                $this->svrCls->testMode = $this->testMode;
		}
        
        $requestMethod = (isset($_SERVER['REQUEST_METHOD'])) ? $_SERVER['REQUEST_METHOD'] : null;
		if (method_exists($this->svrCls, $fName) && $requestMethod!='OPTIONS')
		{
			/*
			 * TODO: $params are no longer needed for action functions
			 * since every controller now has a $this->request object
			 * which is more useful for different environments
			 */
			// forward request variables in as params
			$params = array(); 

			// POST params
			foreach ($_POST as $varname=>$varval)
			{
				if ($varname != 'function')
					$params[$varname] = $varval;
			}

			// Add raw post body for JSON
			$params['raw_body'] = file_get_contents("php://input");

			// GET params
			foreach ($_GET as $varname=>$varval)
			{
				if ($varname != 'function' && $varname != 'authentication')
					$params[$varname] = $varval;
			}
            
      		// If testing, add session
      		// I'm not sure why we are doing this - Sky Stebnicki
            if($this->testMode)
            {
                foreach ($_REQUEST as $varname=>$varval)
                {
                    if ($varname != 'function')
                        $params[$varname] = $varval;
                }
            }
            
			// Manually set output if passed as a param
			if (isset($params['output']))
				$this->svrCls->output = $params['output'];

			// Check permissions to make sure the current user has access to the controller
			$hasPermission = $this->currentUserHasPermission();

			// Call class method and pass request params
			if ($hasPermission)
			{
				return call_user_func(array($this->svrCls, $fName), $params);
			}
			else
			{
				// TODO: return 401	Authorization Required
				echo "Authorization Required";
				return false;
			}

		}
		else
		{
			// TODO: return 404	Not Found
			return false;
		}
	}

	/**
	 * Check permissions to verify that the current user has access to this resource
	 *
	 * @return bool true if current user can call the controller, otherwise false
	 */
	private function currentUserHasPermission()
	{
		// Get the DACL for the selected controller
		$dacl = $this->svrCls->getAccessControlList();

		// Get the currently authenticated user
		$user = $this->application->getAccount()->getUser();

		//echo "\nChecking if " . $user->getId() . " has access: " . var_export($user, true);

		// Check if the user can access this resource and return the result
		return $dacl->isAllowed($user);
	}
}

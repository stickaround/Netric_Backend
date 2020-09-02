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
 * @category  ANT Library
 * @package   RpcSvr
 * @copyright Copyright (c) 2003-2014 Aereus Corporation (http://www.aereus.com)
 */

namespace Netric\Mvc;

use Netric\Application\Response\ResponseInterface;
use Netric\Account\Account;
use Netric\Request\RequestInterface;
use Netric\Request\ConsoleRequest;
use Netric\Application\Application;
use Netric\Application\Response\HttpResponse;
use Netric\Mvc\Exception\NotAuthorizedForRouteException;
use Netric\Mvc\Exception\RouteNotFoundException;

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
    private $controllerClass = null;

    /**
     * Reference to application class
     *
     * @var Application
     */
    private $application = null;

    /**
     * Current account we are routing for
     *
     * @var Account
     */
    private $account = null;

    /**
     * Determines if the class is run by unit test
     *
     * @var Boolean
     */
    public $testMode = false;

    /**
     * Class constructor
     *
     * @param Application $application Instance of application
     */
    public function __construct(Application $application, Account $account = null)
    {
        $this->application = $application;

        // See if we can get the account from the application
        if (!$account) {
            $account = $application->getAccount();
        }

        // Now create an empty default account
        // @deprecated We should no longer be injecting account
        // because it makes for a mess, each contorller should
        // handle loading the account as they see fit.
        if (!$account) {
            $account = new Account($this->application);
        }

        $this->account = $account;
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
     * Process a request through a controller with a factory
     *
     * @param RequestInterface $request The application request
     * @return ResponseInterface
     *
     * @throws RouteNotFoundException When client calls non-existent route
     * @throws NotAuthorizedForRouteException When client does not have permission
     */
    public function run(RequestInterface $request): ResponseInterface
    {
        $fName = $this->setControllerAndGetAction($request);

        // Make sure the request had both a controller and action
        if (!$this->className || !$fName) {
            throw new RouteNotFoundException($this->className . "->" . $fName . " not found!");
        }

        // If this is an OPTIONS request for CORS, return an empty body
        if ($request->getParam('REQUEST_METHOD') == 'OPTIONS') {
            return new HttpResponse($request);
        }

        // If a factory does not exist for the controller, call the legacy Controller caller
        if (!class_exists($this->className . "Factory")) {
            return $this->runLegacyWithoutFactory($request, $fName);
        }

        // Create new instance of class if it does not exist
        $factoryClassName = $this->className . "Factory";
        $factoryClass = new $factoryClassName;
        $this->controllerClass = $factoryClass->get($this->account->getServiceManager());

        // Make sure the action function exists
        if (!method_exists($this->controllerClass, $fName)) {
            throw new RouteNotFoundException($this->className . "->" . $fName . " not found!");
        }

        // Check permissions to make sure the current user has access to the controller
        $hasPermission = $this->currentUserHasPermission($request);

        // Call class method and pass request object
        if ($hasPermission) {
            $response = call_user_func([$this->controllerClass, $fName], $request);

            // Print any buffered output if not in test mode
            if (!$this->testMode) {
                $response->printOutput();
            }

            return $response;
        }

        // Unhandled unauthorized call
        throw new NotAuthorizedForRouteException("Authorization Required");
    }

    /**
     * Execute methods in server class without a factory
     *
     * This is being replaced with the Controller factory pattern
     * to make testing and DI easier in the controllers.
     *
     * @param RequestInterface $request The request being made to run
     * @param string $fName the name of the action function to load
     * @return ResponseInterface on success, exception on failure
     *
     * @throws RouteNotFoundException When client calls non-existent route
     * @throws NotAuthorizedForRouteException When client does not have permission
     */
    private function runLegacyWithoutFactory(RequestInterface $request, string $fName): ResponseInterface
    {
        global $_REQUEST;

        // Create new instance of class if it does not exist
        if ($this->className && !$this->controllerClass && class_exists($this->className)) {
            $clsname = $this->className;
            $this->controllerClass = new $clsname($this->application, $this->account);

            if (isset($this->controllerClass->testMode)) {
                $this->controllerClass->testMode = $this->testMode;
            }
        } else {
            throw new RouteNotFoundException($this->className . "->" . $fName . " not found!");
        }

        $requestMethod = (isset($_SERVER['REQUEST_METHOD'])) ? $_SERVER['REQUEST_METHOD'] : null;
        if (method_exists($this->controllerClass, $fName) && $requestMethod != 'OPTIONS') {
            /*
             * TODO: $params are no longer needed for action functions
             * since every controller now has a $this->request object
             * which is more useful for different environments
             */
            // forward request variables in as params
            $params = [];

            // POST params
            foreach ($_POST as $varname => $varval) {
                if ($varname != 'function') {
                    $params[$varname] = $varval;
                }
            }

            // Add raw post body for JSON
            $params['raw_body'] = file_get_contents("php://input");

            // GET params
            foreach ($_GET as $varname => $varval) {
                if ($varname != 'function' && $varname != 'authentication') {
                    $params[$varname] = $varval;
                }
            }

            // If testing, add session
            // I'm not sure why we are doing this - Sky Stebnicki
            if ($this->testMode) {
                foreach ($_REQUEST as $varname => $varval) {
                    if ($varname != 'function') {
                        $params[$varname] = $varval;
                    }
                }
            }

            // Manually set output if passed as a param
            if (isset($params['output'])) {
                $this->controllerClass->output = $params['output'];
            }

            // Check permissions to make sure the current user has access to the controller
            $hasPermission = $this->currentUserHasPermission($request);

            // Call class method and pass request params
            if ($hasPermission) {
                $response = call_user_func([$this->controllerClass, $fName], $params);

                // New controllers should all return a ResponseInterface which handles output
                if (is_object($response) && $response instanceof ResponseInterface) {
                    $response->printOutput();
                }

                return $response;
            } else {
                // TODO: return 401 Authorization Required
                throw new NotAuthorizedForRouteException("Legacy controller could not be loaded");
            }
        }

        throw new RouteNotFoundException($this->className . "->" . $fName . " not found!");
    }

    /**
     * Set the controller and get the action name from the request
     *
     * This will set the classname with $this->setClass
     *
     * @return string The action name we should be loading
     */
    private function setControllerAndGetAction(RequestInterface $request)
    {
        $functionName = "";

        // Check if controller and action were set with .htaccess
        if ($request->getParam("controller") && $request->getParam("function")) {
            $controller = $this->normalizeSegment($request->getParam("controller"));
            $functionName = $this->normalizeSegment($request->getParam("function"));
        } else {
            $parts = explode("/", $request->getPath());

            if (count($parts) > 2) {
                throw new \RuntimeException("Path must be controller/action and no more");
            }

            $controller = $this->normalizeSegment($parts[0]);
            $functionName = (isset($parts[1])) ? $this->normalizeSegment($parts[1]) : "default";
        }

        // Prefix method to functionName and postfix with Action
        $functionName = strtolower($request->getMethod()) . $functionName . "Action";

        // Set controller class to load
        $this->setClass("Netric\\Controller\\" . $controller . "Controller");

        return $functionName;
    }

    /**
     * Change a segment name in the form of my-path to MyPath
     *
     * @param string $pathSegment
     * @return string
     */
    private function normalizeSegment($pathSegment)
    {
        $pathSegment = str_replace("_", " ", $pathSegment);
        $pathSegment = str_replace("-", " ", $pathSegment);
        $pathSegment = ucwords($pathSegment);
        $pathSegment = str_replace(" ", "", $pathSegment);
        return $pathSegment;
    }

    /**
     * Check permissions to verify that the current user has access to this resource
     *
     * @param RequestInterface $request The request being made to run
     * @return bool true if current user can call the controller, otherwise false
     */
    private function currentUserHasPermission(RequestInterface $request)
    {
        // If running from the console then allow the request
        if ($request instanceof ConsoleRequest || empty($this->account->getAccountId())) {
            // No account which means this is probably a console request
            return true;
        }

        // Get the DACL for the selected controller
        $dacl = $this->controllerClass->getAccessControlList();

        // Get the currently authenticated user
        $user = $this->account->getUser();

        // Check if the user can access this resource and return the result
        return $dacl->isAllowed($user);
    }
}

<?php
/**
 * Console Request
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Request;

class ConsoleRequest implements RequestInterface
{
	/**
     * @var array
     */
    private $params = array();

    /**
     * @var array
     */
    private $envParams = array();

    /**
     * @var string
     */
    private $scriptName = null;

	/**
	 * Initialize request object variables
	 */
	public function __construct(array $args = null, array $env = null)
	{
		if ($args === null) 
		{
            if (!isset($_SERVER['argv'])) 
            {
                $errorDescription = (ini_get('register_argc_argv') == false)
                    ? "Cannot create Console\\Request because PHP ini option 'register_argc_argv' is set Off"
                    : 'Cannot create Console\\Request because $_SERVER["argv"] is not set for unknown reason.';
                throw new \RuntimeException($errorDescription);
            }
            $args = $_SERVER['argv'];
        }

        if ($env === null) {
            $env = $_ENV;
        }

        /*
         * Extract first param assuming it is the script name
         */
        if (count($args) > 0) {
            $this->setScriptName(array_shift($args));
        }

        /**
         * Store runtime params
         */
        $this->params = $args;
        //$this->setContent($args);

        /**
         * Store environment data
         */
        $this->envParams = $env;
	}

	/**
	 * Get a request param by name
	 *
	 * @param string $name The name of the param to get
	 */
	public function getParam($name)
	{
		if (isset($this->params[$name]))
			return $this->params[$name];

		// Not found
		return null;
	}

	/**
	 * Get all params in an associative array
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}

    /**
     * Set/override a param
     *
     * @param string $name
     * @param string $value
     */
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
    }

    /**
     * Get an environment variable
     *
     * @param string    $name       Parameter name
     * @param string    $default    (optional) default value in case the parameter does not exist
     * @return \Zend\Stdlib\Parameters
     */
    public function getEnv($name, $default = null)
    {
    	if (!isset($this->envParams[$name]))
    		return $default;

        return $this->envParams[$name];
    }

	/**
	 * Get the raw body of the request
	 *
	 * @return string
	 */
	public function getBody()
	{
		return file_get_contents("php://input");
	}

	/**
     * @param string $scriptName
     */
    public function setScriptName($scriptName)
    {
        $this->scriptName = $scriptName;
    }
    /**
     * @return string
     */
    public function getScriptName()
    {
        return $this->scriptName;
    }
}
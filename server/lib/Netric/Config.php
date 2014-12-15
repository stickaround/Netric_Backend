<?php
/*
 * This will replace AntConfig
 * 
 * For now we can just wrap the functionality
 * 
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2014 Aereus
 */
namespace Netric;

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../..'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Add application path to path
ini_set('include_path', APPLICATION_PATH . "/" . PATH_SEPARATOR . ini_get('include_path'));

/**
 * Global config class, must have no depedencies
 *
 * @author Sky Stebnicki
 */
class Config 
{
    /**
	 * Store the single instance of class for singleton pattern
	 *
	 * @var $this
	 */
	private static $m_pInstance;

	/**
	 * Current environment
	 *
	 * @var string
	 */
	public $m_env = "production";

	/**
	 * Base path where config files can be found
	 *
	 * This can be overridden for alternate config path through the AntConfig::setPath function
	 *
	 * @var string
	 */
	public $m_basePath = null;

	/**
	 * Settings array
	 *
	 * @var array
	 */
	private $m_settings = array();

	/**
	 * Class constructor
	 *
	 * @param string $appEnv Optional application envirionment to load. If not set the 'APPLICATION_ENV' is used.
	 * @param string $path Optional alternate base path
	 */
	function __construct($appEnv=null, $path=null)
	{
		$this->m_env = ($appEnv) ? $appEnv : APPLICATION_ENV;

		// Set initial config values
		$this->m_settings['application_env'] = $this->m_env;

		// Initialize the default base path
		$this->setPath($path);

		// Load configuration files
		$this->readConfigs();
	}

	/**
	 * Factory for returing a singleton reference to this class
	 */
	public static function getInstance()
	{ 
		if (!self::$m_pInstance) 
		{
			self::$m_pInstance = new Config(); 
		}

		return self::$m_pInstance; 
	}

	/**
	 * Overload the set '->' operator
	 *
	 * @param string $name The name of the propery to set
	 * @param mixed $value The value of the named property
	 */
 	public function __set($name, $value)
    {
        $this->m_settings[$name] = $value;
    }

	/**
	 * Overload the get '->' operator
	 *
	 * @param string $name The name of the propery to get
	 */
    public function __get($name)
    {
        if (array_key_exists($name, $this->m_settings))
            return $this->m_settings[$name];

		return null;
	}

	/**
	 * Used to manually set values at runtime
	 *
	 * @param string $name The name of the propery to set
	 * @param string $subName The name of the sub-propery to set
	 * @param mixed $value The value of the named property
	 */
 	public function setValue($name, $subname=null, $value)
    {
		if ($name && $subname)
        	$this->m_settings[$name][$subname] = $value;
		else
        	$this->m_settings[$name] = $value;
    }
    
    /**
     * Get a value by name
     * 
     * @param string $name The name of the value to get
     * @param string $subname Optional subname if subvalue exists
     * @return mixed value if found, false if fial
     */
    public function getValue($name, $subname=null)
    {
        if (isset($this->m_settings[$name]))
        {
            if ($subname){
                return (isset($this->m_settings[$name][$subname])) ? $this->m_settings[$name][$subname] : false;
            } else {
                return $this->m_settings[$name];
            }
            
        }
        
        return false;
    }

	/**
	 * Set the base path for the configuration files
	 * 
	 * @param string $path Option manual path, otherwise defaults to APPLICATION_PATH/config
	 */
	public function setPath($path="")
	{
		$this->m_basePath = ($path) ? $path : APPLICATION_PATH . "/config";
	}

	/**
	 * Read the configuration data
	 *
	 * This function will iterate through configuration files and load settings
	 * according to $this->m_env and available config files.
	 *
	 * /config/ant.ini will always be loaded no matter what. Then it will look for
	 * the (all lowercase) ant.[$this->envname].ini file like 'ant.testing.ini' and
	 * load variables over any settings already defined. This allows for environmental
	 * overrides of each config value.
	 *
	 * Finally the script will check for the existence of a *.local.ini file like
	 * ant.testing.local.ini which should never be included in the repo but will
	 * be used for local only variable overrides and must be manually set.
	 */
	public function readConfigs()
	{
		// Load the default/base config file
		$this->loadConfigFile("ant.ini");

		// Load local values if they exist
		$this->loadConfigFile("ant.local.ini");

		// Load environment specific values
		$this->loadConfigFile("ant." . $this->m_env . ".ini");

		// Load local values if they exist
		$this->loadConfigFile("ant." . $this->m_env . ".local.ini");
	}

	/**
	 * Load a config file into the settings array
	 *
	 * This function may be called if we are not sure if a config file exists
	 * because it will verify it exists before trying to load any values.
	 *
	 * @param string $name The name of the file to load
	 * @return bool true on success, false on failure
	 */
	private function loadConfigFile($name)
	{
		$path = $this->m_basePath . "/" . $name;

		if (!file_exists($path))
			return false;

		// Currently we assume all files are ini but this may change later
		$values = parse_ini_file($path, true); // make sure we process sections

		if (is_array($values))
			$this->setValues($values);
	}

	/**
	 * Set configuration values
	 *
	 * We traverse through the values and set them in order
	 *
	 * @param array $values The values to set
	 */
	private function setValues($values)
	{
		foreach ($values as $name=>$val)
		{
			if (is_array($val))
			{
                if(!isset($this->m_settings[$name]))
                    $this->m_settings[$name] = array();
				else if(!is_array($this->m_settings[$name]))
					$this->m_settings[$name] = array();

				foreach ($val as $subname=>$subval)
					$this->m_settings[$name][$subname] = $subval;
			}
			else
			{
				$this->m_settings[$name] = $val;
			}
		}
	}
}

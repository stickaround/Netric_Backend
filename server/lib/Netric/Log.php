<?php

/**
 * Netric logger class
 * 
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2014 Aereus
 */

namespace Netric;

/**
 * Set log level constants if not already set by the system
 */
if (!defined("LOG_EMERG"))
	define("LOG_EMERG", 1); // system is unusable

if (!defined("LOG_ALERT"))
	define("LOG_ALERT", 2); // action must be taken immediately

if (!defined("LOG_CRIT"))
	define("LOG_CRIT", 3); // critical issues

if (!defined("LOG_ERR"))
	define("LOG_ERR", 4); // error conditions

if (!defined("LOG_WARNING"))
	define("LOG_WARNING", 5); // warning conditions

if (!defined("LOG_NOTICE"))
	define("LOG_NOTICE", 6); // normal, but significant, condition

if (!defined("LOG_INFO"))
	define("LOG_INFO", 7); // informational message

if (!defined("LOG_DEBUG"))
	define("LOG_DEBUG", 8); // debug-level message

/**
 * Description of Log
 */
class Log 
{
  	/**
	 * Current log level
	 *
	 * @var int
	 */
	private $level = LOG_ERR;

	/**
	 * Path to the log file
	 *
	 * @var string
	 */
	private $logPath = "";

	/**
	 * Maximum size in MB for this log file
	 *
	 * @param int
	 */
	public $maxSize = 500;

	/**
	 * Log file handle
	 *
	 * @var int File handle
	 */
	private $logFile = null;
    
    /**
     * Define log csv definition - what columns store what
     * 
     * @var array
     */
    private $logDef = array(
        "LEVEL"=>0,
        "TIME"=>1,
        "DETAILS"=>2,
        "SOURCE"=>3,
        "SERVER"=>4,
        "ACCOUNT"=>5,
        "USER"=>6,
    );

	/**
	 * Constructor
	 *
	 * @param Netric\Config $config
	 */
	public function __construct($config)
	{
		$data_path = $config->data_path;

		// Make sure the local data path exists
		if ($data_path && file_exists($data_path))
		{
			$lname = ($config->log) ? $config->log : "ant.log";
			$this->logPath = $data_path . "/" . $lname;

			// Now make sure we have not exceeded the maxiumu size for this log file
			if (file_exists($this->logPath))
			{
				if (filesize($this->logPath) >= ($this->maxSize * 1024))
					unlink($this->logPath);
			}

			// Check to see if log file exists and create it if it does not
			if (!file_exists($this->logPath))
			{
				if (touch($this->logPath))
					chmod($this->logPath, 0777);
				else
					$this->logPath = ""; // clear the path which will raise exception on write
			}

			// Now open the file
			$this->logFile = fopen($this->logPath, 'a');
		}

		// Set current logging level if defined
		if ($config->log_level)
			$this->level = $config->log_level;
	}

	/**
	 * Destructor - cleanup file handles
	 */
	public function __destruct()
	{
		if ($this->logFile != null)
			@fclose($this->logFile);
	}

	/**
	 * Put a new entry into the log
	 *
	 * This is usually called by one of the aliased methods like info, error, warning
	 * which in turn just sets the level and writes to this method.
	 *
	 * @param int $lvl The level of the event being logged
	 * @param string $message The message to log
	 */
	public function writeLog($lvl, $message)
	{
		// Only log events below the current logging level set
		if ($lvl > $this->level)
			return false;

		if ($this->logPath == "")
			throw new \Exception('AntLog: Data path "' . $this->logPath . '" does not exist or is not writable');

		global $_SERVER;

		$source = "ANT";
		if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'])
			$source = $_SERVER['REQUEST_URI'];
		else if (isset($_SERVER['PHP_SELF']) && $_SERVER['PHP_SELF'])
			$source = $_SERVER['PHP_SELF'];

		$server = "";
		if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'])
			$server = $_SERVER['SERVER_NAME'];

		$eventData = array();
		$eventData[$this->logDef["LEVEL"]] = $this->getLevelName($lvl);
		$eventData[$this->logDef["TIME"]] = date('c');
		$eventData[$this->logDef["DETAILS"]] = $message;
		$eventData[$this->logDef["SOURCE"]] = $source;
		$eventData[$this->logDef["SERVER"]] = $server;
		$eventData[$this->logDef["ACCOUNT"]] = "";
		$eventData[$this->logDef["USER"]] = "";

		//file_put_contents($this->logPath, $message, FILE_APPEND);
		return fputcsv($this->logFile, $eventData);
	}

	/**
	 * Log an informational message
	 * 
	 * @param string $message The message to insert into the log
	 */
	public function info($message)
	{
		return $this->writeLog(LOG_INFO, $message);
	}

	/**
	 * Log a warning message
	 * 
	 * @param string $message The message to insert into the log
	 */
	public function warning($message)
	{
		return $this->writeLog(LOG_WARNING, $message);
	}

	/**
	 * Log an error message
	 * 
	 * @param string $message The message to insert into the log
	 */
	public function error($message)
	{
		return $this->writeLog(LOG_ERR, $message);
	}

	/**
	 * Log a debug message
	 * 
	 * @param string $message The message to insert into the log
	 */
	public function debug($message)
	{
		return $this->writeLog(LOG_DEBUG, $message);
	}

	/**
	 * Get textual representation of the level
	 *
	 * @param int $lvl The level to convert
	 * @return string Textual representation of level
	 */
	public function getLevelName($lvl)
	{
		switch ($lvl)
		{
		case LOG_EMERG:
		case LOG_ALERT:
		case LOG_CRIT:
		case LOG_ERR:
			return 'ERROR';
		case LOG_WARNING:
			return 'WARNING';
		case LOG_DEBUG:
			return 'DEBUG';
		case LOG_NOTICE:
		case LOG_INFO:
		default:
			return 'INFO';
		}
	}
}

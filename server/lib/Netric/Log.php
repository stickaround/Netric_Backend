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
	define("LOG_EMERG", 0); // system is unusable

if (!defined("LOG_ALERT"))
	define("LOG_ALERT", 1); // action must be taken immediately

if (!defined("LOG_CRIT"))
	define("LOG_CRIT", 2); // critical issues

if (!defined("LOG_ERR"))
	define("LOG_ERR", 3); // error conditions

if (!defined("LOG_WARNING"))
	define("LOG_WARNING", 4); // warning conditions

if (!defined("LOG_NOTICE"))
	define("LOG_NOTICE", 5); // normal, but significant, condition

if (!defined("LOG_INFO"))
	define("LOG_INFO", 6); // informational message

if (!defined("LOG_DEBUG"))
	define("LOG_DEBUG", 7); // debug-level message

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

	/**
	 * PHP error handler function is called with set_error_handler early in execution
	 *
	 * @param int $errorno The error code
	 * @param string $errstr The error message
	 * @param string $errfile The file originating the error
	 * @param int $errline The line that triggered the error
	 * @param array $errcontext Every variable that existed in the scope the error was triggered in
	 */
	public function phpErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
	{
		// if error has been supressed with an @
		if (error_reporting() == 0) {
			return;
		}

		// check if function has been called by an exception
		if(func_num_args() == 5) 
		{
			// called by trigger_error()
			$exception = null;
			list($errno, $errstr, $errfile, $errline) = func_get_args();
			$backtrace = array_reverse(debug_backtrace());
		}
		else 
		{
			// called by unhandled exception
			$exc = func_get_arg(0);
			$errno = $exc->getCode();
			$errstr = $exc->getMessage();
			$errfile = $exc->getFile();
			$errline = $exc->getLine();
			$backtrace = $exc->getTrace();
		}

		$errorType = array (
			E_ERROR          => 'ERROR',
			E_WARNING        => 'WARNING',
			E_PARSE          => 'PARSING ERROR',
			E_NOTICE         => 'NOTICE',
			E_CORE_ERROR     => 'CORE ERROR',
			E_CORE_WARNING   => 'CORE WARNING',
			E_COMPILE_ERROR  => 'COMPILE ERROR',
			E_COMPILE_WARNING => 'COMPILE WARNING',
			E_USER_ERROR     => 'USER ERROR',
			E_USER_WARNING   => 'USER WARNING',
			E_USER_NOTICE    => 'USER NOTICE',
			E_STRICT         => 'STRICT NOTICE',
			E_RECOVERABLE_ERROR  => 'RECOVERABLE ERROR',
		);

		// create error message
		if (array_key_exists($errno, $errorType)) 
		{
			$err = $errorType[$errno];
		} 
		else 
		{
			$err = 'UNHANDLED ERROR';
		}

		$errMsg = "$err: $errstr in $errfile on line $errline";

		// start backtrace
		foreach ($backtrace as $v) 
		{
			if (isset($v['class'])) 
			{
				$trace = 'in class '.$v['class'].'::'.$v['function'].'(';

				if (isset($v['args'])) 
				{
					$separator = '';

					foreach($v['args'] as $arg ) 
					{
						$trace .= "$separator".$this->getPhpErrorArgumentStr($arg);
						$separator = ', ';
					}
				}
				$trace .= ')';
			}
			elseif (isset($v['function']) && empty($trace)) 
			{
				$trace = 'in function '.$v['function'].'(';
				if (!empty($v['args'])) 
				{
					$separator = '';

					foreach($v['args'] as $arg ) 
					{
						$trace .= "$separator".$this->getPhpErrorArgumentStr($arg);
						$separator = ', ';
					}
				}
				$trace .= ')';
			}
		}

		// what to do
		switch ($errno) 
		{
		case E_NOTICE:
		case E_USER_NOTICE:
		case E_STRICT:
		case E_DEPRECATED:
			return;
			break;

		default:

			$body = "";
			if (isset($_COOKIE['uname']))
				$body .= "USER_NAME: ".$_COOKIE['uname']."\n";
			$body .= "Type: System\n";
			if (isset($_COOKIE['db']))
				$body .= "DATABASE: ".$_COOKIE['db']."\n";
			if (isset($_COOKIE['dbs']))
				$body .= "DATABASE_SERVER: ".$_COOKIE['dbs']."\n";
			if (isset($_COOKIE['aname']))
				$body .= "ACCOUNT_NAME: ".$_COOKIE['aname']."\n";

			$body .= "When: ".date('Y-m-d H:i:s')."\n";
			$body .= "URL: ".$_SERVER['REQUEST_URI']."\n";
			$body .= "PAGE: ".$_SERVER['PHP_SELF']."\n";
			$body .= "----------------------------------------------\n".nl2br($errMsg)."\nTrace: ".nl2br($trace);
			$body .= "\n----------------------------------------------\n";

			// Log the error
			$this->error($body);

			break;
		}
	}

	/**
	 * Log an unhandled exception
	 *
	 * @param \ExceptionInterface $exception
	 */
	public function phpUnhandledExceptionHandler($exception)
	{
		$errno = $exception->getCode();
		$errstr = $exception->getMessage();
		$errfile = $exception->getFile();
		$errline = $exception->getLine();
		$backtrace = $exception->getTraceAsString();

		$body = "$errMsg = \"$errno: $errstr in $errfile on line $errline\";\n";
		if (isset($_COOKIE['uname']))
			$body .= "USER_NAME: ".$_COOKIE['uname']."\n";
		$body .= "Type: System\n";
		if (isset($_COOKIE['db']))
			$body .= "DATABASE: ".$_COOKIE['db']."\n";
		if (isset($_COOKIE['dbs']))
			$body .= "DATABASE_SERVER: ".$_COOKIE['dbs']."\n";
		if (isset($_COOKIE['aname']))
			$body .= "ACCOUNT_NAME: ".$_COOKIE['aname']."\n";

		$body .= "When: ".date('Y-m-d H:i:s')."\n";
		$body .= "URL: ".$_SERVER['REQUEST_URI']."\n";
		$body .= "PAGE: ".$_SERVER['PHP_SELF']."\n";
		$body .= "----------------------------------------------\n";
		$body .= $errMsg."\nTrace: $backtrace";
		$body .= "\n----------------------------------------------\n";

		// Log the error
		$this->error($body);
	}

	/**
	 * Capture PHP shutdown event to look for a fatal error
	 */
	public function phpShutdownErrorChecker()
	{
		// Check for a fatal error that would halted execution
		$error = error_get_last();
		if (null != $error)
		{
			if ($error['type'] <= E_ERROR)
			{
				$this->phpErrorHandler($error['type'], 
					$error['message'], 
					$error['file'], 
					$error['line'], array()
				);
			}
		}
	}

	/**
	 * Convert an error argument or backtrace to a string for logging
	 */
	private function getPhpErrorArgumentStr($arg)
	{
		switch (strtolower(gettype($arg))) 
		{
		case 'string':
			return( '"'.str_replace( array("\n"), array(''), $arg ).'"' );

		case 'boolean':
			return (bool)$arg;

		case 'object':
			return 'object('.get_class($arg).')';

		case 'array':
			$ret = 'array(';
			$separtor = '';

			foreach ($arg as $k => $v) 
			{
				//$ret .= $separtor.$this->getPhpErrorArgumentStr).' => '.$this->getPhpErrorArgumentStr);
				$separtor = ', ';
			}
			$ret .= ')';

			return $ret;

		case 'resource':
			return 'resource('.get_resource_type($arg).')';

		default:
			return var_export($arg, true);
		}
	}
}

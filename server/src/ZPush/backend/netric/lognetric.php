<?php
/**
 * Z-Push backend for netric
 *
 * The reason all the files are lowercase in here is because that is the z-push standard
 * so we stick with it to be consistent.
 */

$zPushRoot = dirname(__FILE__) ."/../../";
require_once($zPushRoot . 'lib/log/log.php');

use Netric\Application\Application;
use Netric\Config\ConfigLoader;
use Netric\Log\LogInterface;

/**
 * Implementation of ZPush log that passes all messages though to the Netric application log
 */
class LogNetric extends \Log
{
    /**
     * Buffer of the last message written
     * @var string
     */
    private $lastMessageWritten = "";
    
    /**
     * Log constructor cannot take any arguments because we do not control instantiation
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Get the last message written to file
     * 
     * This is mostly used for testing purposes
     *
     * @return string
     */
    public function getLastMessage()
    {
        return $this->lastMessageWritten;
    }

    //
    // Implementation of Log
    //

    /**
     * Writes a log message to the netric log
     *
     * @param int $loglevel ZPush log level
     * @param string $message Message to be logged
     */
    protected function Write($loglevel, $message) {
        $netricLog = $this->getNetricLog();
        $logMessage = $this->buildLogString($loglevel, $message);

        switch ($loglevel) {
            case LOGLEVEL_FATAL:
            case LOGLEVEL_ERROR:
                $netricLog->error($logMessage);
                break;
            case LOGLEVEL_WARN:
                $netricLog->warning($logMessage);
                break;
            case LOGLEVEL_INFO:
                $netricLog->info($logMessage);
                break;
            case LOGLEVEL_DEBUG:
            case LOGLEVEL_WBXML:
            case LOGLEVEL_DEVICEID:
            case LOGLEVEL_WBXMLSTACK:
            default:
                $netricLog->debug($logMessage);
                break;
        }

        $this->lastMessageWritten = $logMessage;
    }

    /**
     * This function is used as an event for log implementer
     * 
     * It happens when the a call to the Log function is finished.
     *
     * @access public
     * @return void
     */
    public function WriteForUser($loglevel, $message) {
        // Always pass the logleveldebug so it uses syslog level LOG_DEBUG
        $this->Write(LOGLEVEL_DEBUG, $message); 
    }

    /**
     * Get the netric application log to send all mesages to
     *
     * @return LogInterface
     */
    private function getNetricLog()
    {
        // First check to see if the netric application was previously initailized
        if (Application::getApplicationInstance()) {
            // This is the easiest solution, just use the application log
            return Application::getApplicationInstance()->getLog();
        } else {
            /*  
             * The netric application was not yet initialized so we'll need to
             * construct our own instance of the netric application logger
             */
             // Setup config
             $configLoader = new ConfigLoader();
             $applicationEnvironment = (getenv('APPLICATION_ENV')) ?
                 getenv('APPLICATION_ENV') : "production";
             $config = $configLoader->fromFolder(
                 dirname(__FILE__)."/../../../../config",
                 $applicationEnvironment
             );
             return Application::init($config)->getLog();
        }
    }

    /**
     * Build the log string for netric log
     *
     * @param int $loglevel ZPUsh log level constant
     * @param string $message Message to be logged
     * @return string
     */
    private function buildLogString($loglevel, $message) {
        $log = $this->GetUser();
        if ($loglevel >= LOGLEVEL_DEVICEID) {
            $log .= $this->GetDevid();
        }
        $log .= ' ' . $message;
        return $log;
    }
}
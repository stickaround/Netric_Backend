<?php

/**
 * Z-Push backend for netric
 *
 * The reason all the files are lowercase in here is because that is the z-push standard
 * so we stick with it to be consistent.
 */

$zPushRoot = dirname(__FILE__) . "/../../";
require_once($zPushRoot . 'lib/log/log.php');

// Include netric autoloader for all netric libraries
require_once(dirname(__FILE__) . "/../../../../vendor/autoload.php");

// Require backend application initialization
require_once(dirname(__FILE__) . '/netricApplicationInit.php');

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
     * Log path
     *
     * This is only used for dumping WBXML
     *
     * @var string
     */
    private $logFilePath = "";

    /**
     * Log constructor cannot take any arguments because we do not control instantiation
     */
    public function __construct()
    {
        parent::__construct();

        // Set output directory for dumping WBXML files
        $this->logFilePath = dirname(__FILE__) . "/../../../../data/log";
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
    protected function Write($loglevel, $message)
    {
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
            case LOGLEVEL_WBXMLSTACK:
                // TODO: We don't really need to log the stack
                // TODO: since it is encoded in WBXML which is not readable
                break;
            case LOGLEVEL_WBXML:
                $this->dumpWbXml($message);
                break;
            case LOGLEVEL_DEBUG:
            case LOGLEVEL_DEVICEID:
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
    public function WriteForUser($loglevel, $message)
    {
        $this->Write($loglevel, $message);
    }

    /**
     * Get the netric application log to send all mesages to
     *
     * @return LogInterface
     */
    private function getNetricLog()
    {
        $application = NetricApplicationInit::getApplication();
        return $application->getLog();
    }

    /**
     * Build the log string for netric log
     *
     * @param int $loglevel ZPUsh log level constant
     * @param string $message Message to be logged
     * @return string
     */
    private function buildLogString($loglevel, $message)
    {
        $log = $this->GetUser();
        if ($loglevel >= LOGLEVEL_DEVICEID) {
            $log .= $this->GetDevid();
        }
        $log .= ' ' . $message;
        return $log;
    }

    /**
     * Output each line of the communications between the client and the server
     *
     * TODO: It would be much better if we could figure out a way to send this to a remote log
     *
     * @param string $line
     */
    private function dumpWbXml($line)
    {
        try {
            $requestId = $this->getNetricLog()->getRequestId();
            $deviceId = $this->GetDevid();
            $deviceId = str_replace('[', '', $deviceId);
            $deviceId = str_replace(']', '', $deviceId);
            $logFileName = ($deviceId) ? $deviceId : $requestId;
            $file = fopen($this->logFilePath . '/' . $logFileName . '.wbxml', 'a+');
            fwrite($file, $line . "\n");
            fclose($file);
        } catch (Exception $ex) {
            // Log the error so we know something went wrong
            $this->Write(LOGLEVEL_ERROR, $ex->getMessage());
        }
    }
}

<?php
/**
 * Netric logger class
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2014 Aereus
 */

namespace Netric\Log;

/**
 * Description of Log
 */
interface LogInterface
{
    /**
     * Determine which writer we are going to use
     *
     * @param string $log
     */
    public function setLogWriter($log);

    /**
     * Set the path to use for logging
     *
     * @param string $logPath
     */
    public function setLogFilePath($logPath);

    /**
     * Put a new entry into the log
     *
     * This is usually called by one of the aliased methods like info, error, warning
     * which in turn just sets the level and writes to this method.
     *
     * @param int $lvl The level of the event being logged
     * @param string $message The message to log
     * @return bool true on success, false on failure
     */
    public function writeLog($lvl, $message);

    /**
     * Log an informational message
     *
     * @param string $message The message to insert into the log
     */
    public function info($message);

    /**
     * Log a warning message
     *
     * @param string $message The message to insert into the log
     */
    public function warning($message);

    /**
     * Log an error message
     *
     * @param string $message The message to insert into the log
     */
    public function error($message);

    /**
     * Log a debug message
     *
     * @param string $message The message to insert into the log
     */
    public function debug($message);

    /**
     * Get textual representation of the level
     *
     * @param int $lvl The level to convert
     * @return string Textual representation of level
     */
    public function getLevelName($lvl);

    /**
     * PHP error handler function is called with set_error_handler early in execution
     *
     * @param int $errno The error code
     * @param string $errstr The error message
     * @param string $errfile The file originating the error
     * @param int $errline The line that triggered the error
     * @param array $errcontext Every variable that existed in the scope the error was triggered in
     */
    public function phpErrorHandler($errno, $errstr, $errfile, $errline, $errcontext);

    /**
     * Log an unhandled exception
     *
     * @param \ExceptionInterface $exception
     */
    public function phpUnhandledExceptionHandler($exception);

    /**
     * Capture PHP shutdown event to look for a fatal error
     */
    public function phpShutdownErrorChecker();

    /**
     * Set or unset a flag that will print all logs to the console
     *
     * @param bool $print
     */
    public function setPrintToConsole($print = false);
}

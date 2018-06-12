<?php
namespace Netric\Log;

/**
 * Log message data
 *
 * This is used as a way to standardize all the data that
 * is available to a log writter for each message.
 */
class LogMessage
{
    /**
     * Map of error levels to names
     *
     * @var array
     */
    const LEVEL_NAMES = [
        Log::LOG_EMERG => 'emergency',
        Log::LOG_ALERT => 'alert',
        Log::LOG_CRIT => 'critical',
        Log::LOG_ERR => 'error',
        Log::LOG_WARNING => 'warning',
        Log::LOG_NOTICE => 'notice',
        Log::LOG_INFO => 'info',
        Log::LOG_DEBUG => 'debug',
    ];

    /**
     * Set the short name or code like 'ERROR_CREATING_DATABASE'
     *
     * This will often the exception name when logging exceptions
     *
     * @var string
     */
    private $name = null;

    /**
     * The numeric log level
     *
     * @var int
     */
    private $levelNumber = 0;

    /**
     * The IP of the calling client
     *
     * @var string
     */
    private $clientIp = null;

    /**
     * Environemnt where the application is running
     *
     * @var string
     */
    private $applicationEnvironment = null;

    /**
     * The current version of the application
     *
     * @var string
     */
    private $applicationVersion = null;

    /**
     * The unique name of the hosted application
     *
     * @var string
     */
    private $applicationName = null;

    /**
     * The path of this request us usually something like /api/controller/action
     *
     * @var string
     */
    private $requestPath = null;

    /**
     * The unique id of this request
     *
     * @var string
     */
    private $requestId = null;

    /**
     * Main body of the log message
     *
     * @var string|array
     */
    private $body = null;

    /**
     * Construct the message
     *
     * @param string $name Required short name
     */
    public function __construct(string $applicationName, string $messageName)
    {
        $this->name = $messageName;
        $this->applicationName = $applicationName;
    }

    /**
     * Get the short name or code of this message that was set during construction
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the level number of this message according to PSR 3 (see Netric\Log)
     *
     * @param int $level
     * @return void
     */
    public function setLevelNumber(int $level)
    {
        $this->levelNumber = $level;
    }

    /**
     * Get the lever number for this message
     *
     * @return int
     */
    public function getLevelNumber(): int
    {
        return $this->levelNumber;
    }

    /**
     * Get the lever name from $this->levelNumber
     *
     * @return string
     */
    public function getLevelName(): ?string
    {
        return self::LEVEL_NAMES[$this->levelNumber];
    }

    /**
     * Set the ip of the client
     *
     * @param string $clientIp
     * @return void
     */
    public function setClientIp(string $clientIp)
    {
        $this->clientIp = $clientIp;
    }

    /**
     * Get the ip of the client
     *
     * @return string|null
     */
    public function getClientIp(): ?string
    {
        return $this->clientIp;
    }

    /**
     * Set the requested path
     *
     * @param string $requestPath
     * @return void
     */
    public function setRequestPath(string $requestPath)
    {
        $this->requestPath = $requestPath;
    }

    /**
     * Get the requested path
     *
     * @return string|null
     */
    public function getRequestPath(): ?string
    {
        return $this->requestPath;
    }

    /**
     * Set the application request id
     *
     * @param string $requestId
     * @return void
     */
    public function setRequestId(string $requestId)
    {
        $this->requestId = $requestId;
    }

    /**
     * Get the application request id
     *
     * @return string|null
     */
    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    /**
     * Set the running environment
     *
     * @param string $applicationEnvironment
     * @return void
     */
    public function setApplicationEnvironment(string $applicationEnvironment)
    {
        $this->applicationEnvironment = $applicationEnvironment;
    }

    /**
     * Get the running environment
     *
     * @return string|null
     */
    public function getApplicationEnvironment(): ?string
    {
        return $this->applicationEnvironment;
    }

    /**
     * Get the version of the logging application
     *
     * @param string $applicationversion
     * @return void
     */
    public function setApplicationVersion(string $applicationversion)
    {
        $this->applicationVersion = $applicationversion;
    }

    /**
     * Get the version of the logging application
     *
     * @param string $applicationVersion
     * @return string|null
     */
    public function getApplicationVersion(): ?string
    {
        return $this->applicationVersion;
    }

    /**
     * Get the unique name of the running application
     *
     * @return string
     */
    public function getApplicationName(): string
    {
        return $this->applicationName;
    }
    
    /**
     * Set the body which can either be a string of any size, or an array for structured
     *
     * @param string|array $body
     * @return void
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * Get the body which can either be a string of any size, or an array for structured
     *
     * @return string|array
     */
    public function getBody()
    {
        return $this->body;
    }
}

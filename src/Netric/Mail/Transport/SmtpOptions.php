<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Mail\Transport;

use Netric\Mail\Exception;
use Netric\Stdlib\AbstractOptions;

class SmtpOptions extends AbstractOptions
{
    /**
     * @var string Local client hostname
     */
    protected $name = 'localhost';

    /**
     * @var string
     */
    protected $connectionClass = 'smtp';

    /**
     * Connection configuration (passed to the underlying Protocol class)
     *
     * @var array
     */
    protected $connectionConfig = [];

    /**
     * @var string Remote SMTP hostname or IP
     */
    protected $host = '127.0.0.1';

    /**
     * @var int
     */
    protected $port = 25;

    /**
     * Return the local client hostname
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the local client hostname or IP
     *
     * @todo   hostname/IP validation
     * @param  string $name
     * @throws \Netric\Mail\Exception\InvalidArgumentException
     * @return SmtpOptions
     */
    public function setName($name)
    {
        if (!is_string($name) && $name !== null) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Name must be a string or null; argument of type "%s" provided',
                (is_object($name) ? get_class($name) : gettype($name))
            ));
        }
        $this->name = $name;
        return $this;
    }

    /**
     * Get connection class
     *
     * This should be either the class Netric\Mail\Protocol\Smtp or a class
     * extending it -- typically a class in the Netric\Mail\Protocol\Smtp\Auth
     * namespace.
     *
     * @return string
     */
    public function getConnectionClass()
    {
        return $this->connectionClass;
    }

    /**
     * Set connection class
     *
     * @param  string $connectionClass the value to be set
     * @throws \Netric\Mail\Exception\InvalidArgumentException
     * @return SmtpOptions
     */
    public function setConnectionClass($connectionClass)
    {
        if (!is_string($connectionClass) && $connectionClass !== null) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Connection class must be a string or null; argument of type "%s" provided',
                (is_object($connectionClass) ? get_class($connectionClass) : gettype($connectionClass))
            ));
        }
        $this->connectionClass = $connectionClass;
        return $this;
    }

    /**
     * Get connection configuration array
     *
     * @return array
     */
    public function getConnectionConfig()
    {
        return $this->connectionConfig;
    }

    /**
     * Set connection configuration array
     *
     * @param  array $connectionConfig
     * @return SmtpOptions
     */
    public function setConnectionConfig(array $connectionConfig)
    {
        $this->connectionConfig = $connectionConfig;
        return $this;
    }

    /**
     * Get the host name
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set the SMTP host
     *
     * @todo   hostname/IP validation
     * @param  string $host
     * @return SmtpOptions
     */
    public function setHost($host)
    {
        $this->host = (string) $host;
        return $this;
    }

    /**
     * Get the port the SMTP server runs on
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set the port the SMTP server runs on
     *
     * @param  int $port
     * @throws \Netric\Mail\Exception\InvalidArgumentException
     * @return SmtpOptions
     */
    public function setPort($port)
    {
        $port = (int) $port;
        if ($port < 1) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Port must be greater than 1; received "%d"',
                $port
            ));
        }
        $this->port = $port;
        return $this;
    }
}

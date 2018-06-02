<?php
namespace Netric\Application\Health\DependencyCheck;

use MogileFs;
use MogileFsException;

/**
 * Make sure we can connect to a PostgeSQL server
 */
class MogileFsDependencyCheck implements DependencyCheckInterface
{
    /**
     * The server address to connect to
     *
     * @var string
     */
    private $mogileServer = "";

    /**
     * The user to use when connecting
     *
     * @var string
     */
    private $mogileAccount = "";

    /**
     * Port used to connect
     *
     * @var int
     */
    private $mogilePort = null;

    /**
     * Constructor
     *
     * @param string $mogileServer
     * @param string $mogileAccount
     * @param string $mogilePort
     */
    public function __construct(string $mogileServer, string $mogileAccount, int $mogilePort = 7001)
    {
        $this->mogileServer = $mogileServer;
        $this->mogileAccount = $mogileAccount;
        $this->mogilePort = $mogilePort;
    }

    /**
     * Check if mogile tracker is running and available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        try {
            $this->mogileFs = new MogileFs();
            $this->mogileFs->connect($this->mogileServer, $this->mogilePort, $this->mogileAccount);
            return true;
        } catch (MogileFsException $ex) {
            // Throw generic FileSystem exception to let callers know what failed
            //throw new CannotConnectException($ex->getMessage());
            return false;
        }
    }
}

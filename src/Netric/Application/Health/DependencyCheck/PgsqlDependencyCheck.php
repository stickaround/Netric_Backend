<?php
namespace Netric\Application\Health\DependencyCheck;

/**
 * Make sure we can connect to a PostgeSQL
 */
class PgsqlDependencyCheck implements DependencyCheckInterface
{
    /**
     * The server address to connect to
     *
     * @var string
     */
    private $databaseHost = "";

    /**
     * The user to use when connecting
     *
     * @var string
     */
    private $databaseUser = "";

    /**
     * Password to use when connecting
     *
     * @var string
     */
    private $databasePassword = "";

    /**
     * Constructor
     *
     * @param string $databaseHost
     * @param string $databaseUser
     * @param string $databasePassword
     */
    public function __construct(string $databaseHost, string $databaseUser, string $databasePassword)
    {
        $this->databaseHost = $databaseHost;
        $this->databaseUser = $databaseUser;
        $this->databasePassword = $databasePassword;
    }

    /**
     * Check if pgsql is running and available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        try {
            $conn = new \PDO(
                "pgsql:host=" . $this->databaseHost,
                $this->databaseUser,
                $this->databasePassword,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_PERSISTENT => true,
                ]
            );
            return ($conn) ? true : false;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Get config values so that we can log it for troubleshooting
     *
     * @return string
     */
    public function getParamsDescription(): string
    {
        return "server={$this->databaseHost};user={$this->databaseUser}";
    }
}

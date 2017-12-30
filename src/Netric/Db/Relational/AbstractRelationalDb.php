<?php
namespace Netric\Db\Relational;

/**
 * Base database class that wraps a PDO connection to the database
 */
class AbstractRelationalDb
{
    /**
     * Supported drivers
     */
    const DRIVER_MYSQL = "mysql";
    const DRIVER_SQLITE = "sqlite";

    /**
     * Define which driver we should use if not provided in the config
     */
    const DEFAULT_DRIVER = self::DRIVER_MYSQL;

    /**
     * Default connection timeout, in seconds
     */
    const CONNECT_TIMEOUT = 2;

    /**
     * Duration after which a query is considered a slow query and may be logged
     */
    const SLOW_QUERY_THRESHOLD = 500;

    /**
     * Number of times to attempt a connection
     */
    const MAX_ATTEMPTS = 2;

    /**
     * @var \PDO $oConnection PDO Connection
     */
    private $oConnection = null;

    /**
     * @var string $sDataSourceName Compiled data source string
     */
    private $sDataSourceName;

    /**
     * @var string $sUser
     */
    private $sUser;

    /**
     * @var string $sPassword
     */
    private $sPassword;

    /**
     * @var integer $iTimeout Connection timeout in seconds
     */
    private $iTimeout;

    /**
     * @var string $sRDbName Used to build our timing key
     */
    private $sRDbName;

    /**
     * @var boolean $bLogTiming Indicates if we should log query times or not
     */
    private $bLogTiming;

    /**
     * @var array $aConnectionAttributes the PDO connection attributes to use with the connection
     */
    private $aConnectionAttributes;

    /**
     * Validate and store the RDb parameters
     *
     * @param string $sDriver Supported drivers as defined by self::DRIVER_*
     * @param string $sHostOrFile Either a hostname or file path based on driver
     * @param string $sRDbName
     * @param string $sUser
     * @param string $sPassword
     * @param integer $iTimeout (in seconds, optional, defaults to 2)
     * @param boolean $bLogTiming Indicates if we should log query times
     * @param array $aConnectionAttributes the PDO attributes to use with the connection
     */
    public function __construct(
        $sDriver,
        $sHostOrFile,
        $sRDbName = "",
        $sUser = "",
        $sPassword = "",
        $iTimeout = self::CONNECT_TIMEOUT,
        $bLogTiming = true,
        array $aConnectionAttributes = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        ]
    ) {
        if ($sDriver === self::DRIVER_MYSQL) {
            $sDataSourceName = $sDriver . ":dbname=" . $sRDbName . ";host=" . $sHostOrFile;
        } elseif ($sDriver === self::DRIVER_SQLITE) {
            $sDataSourceName = $sDriver . ":" . $sHostOrFile;
        } else {
            throw new \RuntimeException($sDriver . " is not a supported RDb driver!");
        }
        $this->sDataSourceName = $sDataSourceName;
        $this->sUser = $sUser;
        $this->sPassword = $sPassword;
        $this->iTimeout = $iTimeout;
        $this->bLogTiming = $bLogTiming;
        $this->sRDbName = $sRDbName;

        // Build the connection attributes ensuring we include our configured timeout
        // NB: Using array_merge is very bad here because it works differently with numeric indices
        $this->aConnectionAttributes = $aConnectionAttributes;

        // If we haven't set an explicit timeout in the connection attributes, use the timeout provided in the constructor
        if (!isset($this->aConnectionAttributes[\PDO::ATTR_TIMEOUT])) {
            $this->aConnectionAttributes[\PDO::ATTR_TIMEOUT] = $iTimeout;
        }
    }

    /**
     * @param string $sDataSourceName the data source name
     * @param string $sUser the database user to connect with
     * @param string $sPassword the password for the database user
     * @param array $aPDOConfiguration any specific PDO configuration options to use
     * @return \PDO the PDO connection
     */
    protected function createPDO($sDataSourceName, $sUser, $sPassword, array $aPDOConfiguration)
    {
        return new \PDO(
            $sDataSourceName,
            $sUser,
            $sPassword,
            $aPDOConfiguration
        );
    }

    /**
     * Method to help lazy load the PDO database connection
     *
     * @return \PDO $this->oConnection
     *
     * @throws DatabaseException
     */
    private function getConnection()
    {
        if (!is_null($this->oConnection)) {
            return $this->oConnection;
        }
        $oLastException = null;
        for ($iAttempt = 1; $iAttempt <= self::MAX_ATTEMPTS; $iAttempt++) {
            try {
                $this->oConnection = $this->createPDO(
                    $this->sDataSourceName,
                    $this->sUser,
                    $this->sPassword,
                    $this->aConnectionAttributes
                );
            } catch (\Exception $oException) {
                $oLastException = $oException;
                Log::info([
                    'message' => 'Could not connect via RDb.',
                    'dataSourceName' => $this->sDataSourceName,
                    'attemptsRemaining' => (self::MAX_ATTEMPTS - $iAttempt),
                    'exception' => $oException,
                ]);
            }
            if (!empty($this->oConnection)) {
                return $this->oConnection;
            }
        }
        // If we're here, no connection could be established
        throw new DatabaseException('Could not establish database connection after ' . self::MAX_ATTEMPTS . ' attempts. Exception: ' . $oLastException->getMessage());
    }

    /**
     * Prepares a SQL statement
     *
     * E.g.
     * $oRDbConnection->prepare(
     *      "SELECT amount FROM thrust WHERE thrustid = :thrustid",
     *      [ 'thrustid' => 1 ]
     * );
     *
     * @param string $sSql
     * @param array $aParams
     *
     * @return \icf\core\rdb\Statement Wrapper to a PDOStatement
     */
    public function prepare($sSql, array $aParams = [])
    {
        $oPDOConnection = $this->getConnection();
        $oPDOStatement = $oPDOConnection->prepare($sSql);
        return new Statement($oPDOStatement, $aParams);
    }

    /**
     * Prepares and executes a statement returning a Results object
     *
     * E.g.
     * $oRDbConnection->query(
     *      "SELECT amount FROM thrust WHERE thrustid = :thrustid",
     *      [ 'thrustid' => 1 ]
     * )->fetchAll();
     *
     * @param string $sSql
     * @param array $aParams
     * @param array $aTableNames What tables are in use for this query (for StatsD)
     *
     * @return Result Result set
     *
     * @throws RuntimeException
     */
    public function query($sSql, array $aParams = [], array $aTableNames = [])
    {
        $oStatement = $this->prepare($sSql, $aParams);

        if ($this->bLogTiming) {
            $sKey = $this->getTimingKey($aTableNames);
            $fStartTime = microtime(true);
        }

        // $oStatement->execute returns a rdb\Result object
        try {
            $oResult = $oStatement->execute();

            if (isset($fStartTime) && isset($sKey)) {
                $iQueryTime = (int)((microtime(true) - $fStartTime) * 1000);
                StatsD::timing($sKey, $iQueryTime);

                if ($iQueryTime > self::SLOW_QUERY_THRESHOLD) {
                    Log::info([
                        'message' => 'RDb: Slow query exceeded threshold',
                        'threshold' => self::SLOW_QUERY_THRESHOLD,
                        'duration' => $iQueryTime,
                        'query' => $sSql,
                    ]);
                }
            }

            return $oResult;
        } catch (\PDOException $oPdoException) {
            /*
             * $oStatement->execute will throw a PDOException if a query fails.
             * We will wrap the details of this into a standard RuntimeException
             * and allow the client to handle the failure without having to be
             * aware of PDOException.
             */
            throw new RuntimeException($oPdoException->getMessage(), 0, $oPdoException);
        }
    }

    /**
     * Starts a DB transaction.
     *
     * @return bool
     */
    public function startTransaction()
    {
        $oPDOConnection = $this->getConnection();
        return $oPDOConnection->beginTransaction();
    }

    /**
     * Commits the current DB transaction.
     *
     * @return bool
     */
    public function commit()
    {
        $oPDOConnection = $this->getConnection();
        return $oPDOConnection->commit();
    }

    /**
     * Rolls back the current DB transaction.
     *
     * @return bool
     */
    public function rollback()
    {
        $oPDOConnection = $this->getConnection();
        return $oPDOConnection->rollBack();
    }

    /**
     * Get the last insert id
     *
     * @param string $sName explicitly get last insert for sequence name
     * @return int
     */
    public function lastInsertId($sName = null)
    {
        $oPDOConnection = $this->getConnection();
        return $oPDOConnection->lastInsertId($sName);
    }

    /**
     * Returns a quoted string that is theoretically safe to pass in
     * an SQL statement. May return FALSE if this is not supported
     * by the driver.
     *
     * Note that this is deprecated in favor of prepared statements
     *
     * @param string $sString
     * @return string|false
     * @deprecated
     */
    public function quote($sString)
    {
        $oPDOConnection = $this->getConnection();
        return $oPDOConnection->quote($sString);
    }

    /**
     * Gets the key used for StatsD logging
     *
     * @param $aTableNames
     *
     * @return string|null Key to use or null if we cannot StatsD
     */
    private function getTimingKey($aTableNames)
    {
        $oServiceLocator = ServiceLocator::getInstance();

        if ($oServiceLocator->isAliasRegistered('config')) {
            $oConfig = ServiceLocator::get('config');
        } else {
            Log::warning(['message' => 'Attempted to access config alias, but it was not defined. Skipping query time log.']);
            return null;
        }

        $sAppName = '';
        if (!empty($oConfig)) {
            $sAppName = $oConfig->get('appname');
        }

        if (empty($sAppName)) {
            Log::error(['message' => 'appname not set in config. Skipping query time log.']);
            return null;
        }

        if (empty($aTableNames)) {
            $aTableNames = ['unknown'];
        }

        // appname.sql.dbname.tables_we_query_or_join_against
        $aParts = [
            $sAppName,
            'sql',
            $this->sRDbName,
            implode('_', $aTableNames)
        ];

        return implode('.', $aParts);
    }

    /**
     * Gets the RDb name for this RDb
     *
     * @return string
     */
    public function getRDbName()
    {
        return $this->sRDbName;
    }

    /**
     * Closes the database connection
     */
    public function close()
    {
        if (!empty($this->oConnection)) {
            // Send a command to kill the connection
            try {
                $this->query('KILL CONNECTION_ID()');
            } catch (\Exception $oException) {
                // We ignore the exception because this can be used for sqlite and other implementations that do not have this command
            }

            // Close connection
            $this->oConnection = null;
        }
    }
}
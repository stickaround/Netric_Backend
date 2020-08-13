<?php

declare(strict_types=1);

namespace Netric\Db\Relational;

class RelationalDbContainer implements RelationalDbContainerInterface
{
    /**
     * Right now we use the same database for all accounts and the application
     *
     * @param string $accountId
     * @return RelationalDbInterface
     */
    private $database = null;

    /**
     * Constructor
     *
     * @param RelationalDbInterface $database
     */
    public function __construct(RelationalDbInterface $database)
    {
        $this->database = $database;
    }

    /**
     * Retrieve database connection for a given account by id
     *
     * Different accounts can be stored on different servers at a later point.
     * Currently, this will always return the same database handle for all accounts,
     * but we are utilizing this in order to make the inevitable change later possible.
     *
     * @param string $accountId
     * @return RelationalDbInterface
     */
    public function getDbHandleForAccountId(string $accountId): RelationalDbInterface
    {
        return $this->database;
    }

    /**
     * Get the handle used for the application database
     *
     * @return RelationalDbInterface
     */
    public function getDbConnectionForApplication(): RelationalDbInterface
    {
        return $this->database;
    }
}

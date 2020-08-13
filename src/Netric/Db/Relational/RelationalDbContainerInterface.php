<?php

declare(strict_types=1);

namespace Netric\Db\Relational;

interface RelationalDbContainerInterface
{
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
    public function getDbHandleForAccountId(string $accountId): RelationalDbInterface;

    /**
     * Get the handle used for the application database
     *
     * @return RelationalDbInterface
     */
    public function getDbConnectionForApplication(): RelationalDbInterface;
}

<?php
namespace NetricTest\Db\Relational;

use Netric\Db\Relational\PgsqlDb;

/**
 * This file should be extended to test any database adapters/interfaces
 * @group integration
 */
class PgsqlDbTest extends AbstractRelationalDbTests
{
    /**
     * Must be implemented in all driver classes
     *
     * @return RelationalDbInterface
     */
    protected function getDatabase()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $config = $sm->get('Config');

        // Create a connection to the template to create a new test database
        $dbh = pg_connect(
            "host=" . $config->db->syshost . " " .
                "dbname=template1 " .
                "user=" . $config->db->user . " " .
                "port=" . $config->db->port . " " .
                "password=" . $config->db->password
        );

        // Try creating the unit test database (use @ to suppress error if already exists)
        @pg_query($dbh, 'CREATE DATABASE automatedtests');

        return new PgsqlDb(
            $config->db->syshost,
            'automatedtests',
            $config->db->user,
            $config->db->password
        );
    }
}

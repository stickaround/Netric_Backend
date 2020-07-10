<?php

namespace Netric\Application;

use Netric\Application\DataMapperInterface as ApplicationDataMapperInterface;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\Application\Schema\SchemaRdbDataMapper;
use RuntimeException;

class DatabaseSetup
{
    /**
     * Database handle
     *
     * @var RelationalDbInterface
     */
    private $database = null;

    /**
     * Schema data defintiion
     *
     * @var array
     */
    private $schemaData = [];

    /**
     * Application data mapper
     *
     * @var ApplicationDataMapperInterface
     */
    private $appDataMapper = null;

    public function __construct(
        RelationalDbInterface $database,
        array $schemaData,
        ApplicationDataMapperInterface $appDataMapper
    ) {
        $this->database = $database;
        $this->schemaData = $schemaData;
        $this->appDataMapper = $appDataMapper;
    }

    /**
     * Update the schemas for the databases
     *
     * This function is idempotent so it can be called over and over
     * without consequence. It will make sure that the schema is always updated.
     *
     * @return void
     */
    public function updateDatabaseSchema()
    {
        $appSchemaDm = new SchemaRdbDataMapper($this->database, $this->schemaData);

        // Make sure we can connect to the database
        if (!$this->database->checkConnection()) {
            throw new RuntimeException("Could not connect to the applicaiton database. Please make sure you created it.");
        }

        // Update the database schema
        if (!$appSchemaDm->update()) {
            // This should never fail, but just in case throw an exception so we can address it
            throw new RuntimeException("Failed to update the schema");
        }
    }
}

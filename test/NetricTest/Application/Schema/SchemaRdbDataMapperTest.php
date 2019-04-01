<?php
/**
 * Test managing a schema in PostgreSQL database
 */
namespace NetricTest\Application\Schema;

use Netric\Application\Application;
use Netric\Config\ConfigLoader;
use Netric\Db\Relational\PgsqlDb;
use Netric\Application\Schema\SchemaRdbDataMapper;
use Netric\Db\Relational\RelationalDbInterface;

class SchemaRdbDataMapperTest extends AbstractSchemaDataMapperTests
{
    /**
     * Handle to current database
     *
     * @var RelationalDbInterface
     */
    private $database = null;

    /**
     * Get the Relational Database DataMapper
     *
     * @param array $schemaDefinition
     * @param string $accountId THe account we will be managing the schema for
     * @return SchemaDataMapperPgsql
     */
    protected function getDataMapper(array $schemaDefinition, $accountId)
    {
        $configLoader = new ConfigLoader();
        $applicationEnvironment = (getenv('APPLICATION_ENV')) ? getenv('APPLICATION_ENV') : "production";

        // Setup the new config
        $config = $configLoader->fromFolder(__DIR__ . "/../../../../config", $applicationEnvironment);

        $this->database = new PgsqlDb(
            $config->db['host'],
            $config->db['accdb'],
            $config->db['user'],
            $config->db['password']
        );

        // Set the schema we will be interacting with
        $this->database->setNamespace("acc_" . $accountId);

        return new SchemaRdbDataMapper($this->database, $schemaDefinition);
    }

    /**
     * Test a created bucket by inserting data
     *
     * @param string $bucketName The name of the table/document/collection to save data to
     * @param array $data The data to insert and verify
     * @return bool true if data could be inserted and read from the data store
     */
    protected function insertIntoBucket($bucketName, array $data)
    {
        // Return true if we were able to insert successfully
        return ($this->database->insert($bucketName, $data)) ? true : false;
    }

    /**
     * Make sure that a field with a primary key is set
     *
     * @param string $bucketName The name of the table/document/collection to test
     * @param string|array $propertyOrProperties A property name or array of property names
     * @return bool true if the key exists
     */
    protected function primaryKeyExists($bucketName, $propertyOrProperties)
    {
        return $this->database->isColumnPrimaryKey($bucketName, $propertyOrProperties);
    }

    /**
     * Assure that there is an index on a given property for a bucket
     *
     * @param string $bucketName The name of the table/document/collection to test
     * @param string|array $propertyOrProperties A property name or array of property names
     * @return bool true if the index exists
     */
    protected function indexExists($bucketName, $propertyOrProperties)
    {
        return $this->database->indexExists($bucketName . "_" . $propertyOrProperties . "_idx");
    }
}

<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright Copyright (c) 2015-2016 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\Account\Schema;

use Netric\Db\Pgsql;
use Netric\Error\Error;

/**
 * PostgreSQL implementation of the schema DataMapper
 */
class SchemaDataMapperPgsql extends AbstractSchemaDataMapper
{
    /**
     * Handle to the database where the account is located
     *
     * @var Pgsql
     */
    private $dbh = null;

    /**
     * Construct this DataMapper
     *
     * @param Pgsql $dbh A handle to the PostgreSQL account database
     * @param array $schemaDefinition The latest schema definition
     */
    public function __construct(Pgsql $dbh, array $schemaDefinition)
    {
        $this->dbh = $dbh;
        $this->schemaDefinition = $schemaDefinition;
    }

    /**
     * Make sure a namespace/schema exists for this tenant
     *
     * @param int $accountId The unique account id to create a schema for
     * @return bool true on success, false on failure with $this->getLastError set
     */
    protected function createSchemaIfNotExists($accountId)
    {
        if (!$this->dbh->schemaExists("acc_" . $accountId))
        {
            if (!$this->dbh->query("CREATE SCHEMA acc_" . $accountId . ";", false))
            {
                // We failed for some reason
                $this->errors[] = new Error("Could not create schema: " . $this->dbh->getLastError());
                return false;
            }

            // Switch to the new schema
            $this->dbh->setSchema("acc_" . $accountId);
        }

        // Schema exists
        return true;
    }

    /**
     * Create and update a table for each data bucket
     *
     * A bucket is simply an abstract name for a table/collection/file/document
     * or whatever the particular data-store calls a collection of data with similar
     * properties.
     *
     * @param string $bucketName The name of the table/document/collection
     * @param array $bucketDefinition The definition of data that is stored in the bucket
     * @return bool true on success, false on failure with this->getLastError set
     */
    protected function applyBucketDefinition($bucketName, array $bucketDefinition)
    {
        // First make sure the table exists
        // ----------------------------------------
        $sql = "CREATE TABLE IF NOT EXISTS $bucketName()";

        // Does this table inherit?
        if (isset($bucketDefinition['INHERITS']))
        {
            $sql .= " INHERITS (".$bucketDefinition['INHERITS'].")";
        }

        $sql .= ";";

        // Create the table
        $this->dbh->query($sql);

        // Create or update columns
        // -----------------------------------------------
        foreach ($bucketDefinition['PROPERTIES'] as $columnName=>$columnDefinition)
        {
            if (!$this->applyColumn($bucketName, $columnName, $columnDefinition))
            {
                // Something went wrong, leave and return an error
                $this->errors[] = new Error($this->dbh->getLastError());
                return false;
            }
        }

        // Create primary key
        // -----------------------------------------------
        if (isset($bucketDefinition['PRIMARY_KEY']))
        {
           if (!$this->applyPrimaryKey($bucketName, $bucketDefinition['PRIMARY_KEY']))
           {
               // Something went wrong, leave and return an error
               $this->errors[] = new Error($this->dbh->getLastError());
               return false;
           }
        }

        // Create keys if supported by the database
        // -----------------------------------------------
        if (isset($bucketDefinition['KEYS']))
        {
            foreach ($bucketDefinition['KEYS'] as $key_name => $key_data)
            {
                if ($key_data[0] == 'FKEY')
                    $this->applyForeignKey($bucketName, $key_name, $key_data);
                else
                    $this->applyIndexOld($bucketName, $key_name, $key_data);
            }
        }

        // Create indexes
        // -----------------------------------------------
        if (isset($bucketDefinition['INDEXES']))
        {
            foreach ($bucketDefinition['INDEXES'] as $indexData)
            {
                $this->applyIndex($bucketName, $indexData);
            }
        }

        return true;
    }

    /**
     * Apply definition to a column
     *
     * @param $tableName
     * @param $columnName
     * @param array $columnDefinition
     * @return bool true on success, false on failure
     */
    private function applyColumn($tableName, $columnName, array $columnDefinition)
    {
        // Make sure the column names are not too long
        if (strlen($columnName) > 64)
            throw new \RuntimeException("Column name '$columnName' on table '$tableName' is too long.");

        if (isset($columnDefinition['default']) && $columnDefinition['default'] == 'auto_increment' && strlen($columnName) > 61) // "${column_name}_gen"
            throw new \RuntimeException("Auto increment column name '$columnName' on table '$tableName' is too long.");

        // Return true if the column already exists
        if ($this->dbh->columnExists($tableName, $columnName))
            return true;

        // Determine the column type
        if (isset($columnDefinition['default']) && $columnDefinition['default'] == 'auto_increment')
        {
            $columnType = ($columnDefinition['type'] == 'bigint') ? 'bigserial' : 'serial';
        }
        else if (isset($columnDefinition['subtype']) && $columnDefinition['subtype'])
        {
            $columnType = $columnDefinition['type'] . " " . $columnDefinition['subtype'];
        }
        else
        {
            $columnType = $columnDefinition['type'];
        }

        // Add column definition
        $sql = "ALTER TABLE $tableName ADD COLUMN {$columnName} {$columnType}";

        // Add column defaults
        if (isset($columnDefinition['default']) && $columnDefinition['default'] != 'auto_increment')
        {
            $sql .= " DEFAULT '{$columnDefinition['default']}'";
        }

        return ($this->dbh->query($sql)) ? true : false;
    }

    /**
     * Apply definition to a primary key
     *
     * @param string $tableName The name of the table we are creating
     * @param string|string[] $columnNameOrNames Either a single column name or an array of columns
     * @return true on success, false on failure
     */
    private function applyPrimaryKey($tableName, $columnNameOrNames)
    {
        // Normalize to an array so we can implode below
        if (!is_array($columnNameOrNames))
            $columnNameOrNames = array($columnNameOrNames);

        // First check to see if the primary key already exists
        $allSet = true;
        foreach ($columnNameOrNames as $columnName)
        {
            $allSet = $this->dbh->isPrimaryKey($tableName, $columnName);
            if (!$allSet)
                break;
        }

        // Primary keys are already set, nothing to do
        if ($allSet)
            return true;

        // Run the SQL
        $sql = "ALTER TABLE $tableName ADD PRIMARY KEY (" . implode(', ', $columnNameOrNames) . ");";
        return ($this->dbh->query($sql)) ? true : false;
    }

    /**
     * Add a foreign key to a table
     *
     * @param string $tableName
     * @param string $foreignKeyName
     * @param array $keyDefinition
     * @return bool true on sucess, false on failure
     */
    private function applyForeignKey($tableName, $foreignKeyName, $keyDefinition)
    {
        // TODO: right now we don't do anything with keys
        return true;

        // The first element of the definition should be an array of columns
        if (!is_array($keyDefinition[1]))
        {
            $keyDefinition[1] = array($keyDefinition[1]);
        }

        if (strlen($tableName . $foreignKeyName) > 63)
        {
            throw new \RuntimeException("Key name '${$tableName}_$foreignKeyName' on table '$tableName' is too long");
        }

        $sql = ($keyDefinition[0] == 'UNIQUE') ?  'CREATE UNIQUE INDEX' : 'CREATE INDEX';
        $sql .= " {$tableName}_{$foreignKeyName}_idx ON {$tableName} (" . implode(', ', $keyDefinition[1]) . ");";
        return ($this->dbh->query($sql)) ? true : false;
    }

    /**
     * @deprecated We replaced this with new INDEXES settings seen in applyIndex
     *
     * Old index was in keys
     *
     * @param string $tableName
     * @param string $foreignKeyName
     * @param array $keyDefinition
     * @return bool true on sucess, false on failure
     */
    private function applyIndexOld($tableName, $foreignKeyName, $keyDefinition)
    {
        // TODO: right now we don't do anything with keys
        return true;

        // The first element of the definition should be an array of columns
        if (!is_array($keyDefinition[1]))
        {
            $keyDefinition[1] = array($keyDefinition[1]);
        }

        if (strlen($tableName . $foreignKeyName) > 63)
        {
            throw new \RuntimeException("Key name '${$tableName}_$foreignKeyName' on table '$tableName' is too long");
        }

        $sql = ($keyDefinition[0] == 'UNIQUE') ?  'CREATE UNIQUE INDEX' : 'CREATE INDEX';
        $sql .= " {$tableName}_{$foreignKeyName}_idx ON {$tableName} (" . implode(', ', $keyDefinition[1]) . ");";
        return ($this->dbh->query($sql)) ? true : false;
    }

    /**
     * Add an index to the table
     *
     * @param string $tableName
     * @param array $indexData
     * @return bool true on sucess, false on failure
     */
    private function applyIndex($tableName, $indexData)
    {
        $indexName = implode("_", $indexData['properties']);

        if (strlen($tableName . $indexName) > 63)
        {
            throw new \RuntimeException("Key name '${$tableName}_$indexName' on table '$tableName' is too long");
        }

        // Return true if the index already exists
        if ($this->dbh->indexExists("{$tableName}_{$indexName}_idx")) {
            return true;
        }

        $sql = (isset($indexData['type']) && $indexData['type'] == 'UNIQUE') ?  'CREATE UNIQUE INDEX' : 'CREATE INDEX';
        $sql .= " {$tableName}_{$indexName}_idx ON {$tableName} (" . implode(', ',  $indexData['properties']) . ");";

        return ($this->dbh->query($sql)) ? true : false;
    }

    /**
     * Add a constraint to the table
     *
     * @param string $tableName The name of the table we are editing
     * @param string $constraintName Unique name of the constraint
     * @param string $constraint
     * @return bool true on success, false on failure
     */
    private function applyConstraint($tableName, $constraintName, $constraint)
    {
        // If already exists do nothing
        if ($this->dbh->constraintExists($tableName, $tableName . "_" . $constraintName))
            return true;

        $sql = "ALTER $tableName ADD CONSTRAINT {$tableName}_".$constraintName." CHECK (" . $constraint . ")";
        return ($this->dbh->query($sql)) ? true : false;
    }
}

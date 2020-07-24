<?php

namespace Netric\Application\Schema;

use Netric\Error\Error;
use Netric\Db\Relational\RelationalDbInterface;

/**
 * PostgreSQL implementation of the schema DataMapper
 */
class SchemaRdbDataMapper extends AbstractSchemaDataMapper
{
    /**
     * Handle to database
     *
     * @var RelationalDbInterface
     */
    private $database = null;

    /**
     * Construct this DataMapper
     *
     * @param RelationalDbInterface $database The Relational Database Interface that will handle the db actions
     * @param array $schemaDefinition The latest schema definition
     */
    public function __construct(RelationalDbInterface $database, array $schemaDefinition)
    {
        $this->database = $database;
        $this->schemaDefinition = $schemaDefinition;
    }

    /**
     * Get the last applied schema revision
     *
     * This is just a hash of the shmea defined in the source code
     * and we are re-using the system-registry which is created the
     * first time the schema definition gets applied.
     *
     * If it does not exit we fail gracefully.
     *
     * @return string
     */
    public function getLastAppliedSchemaHash(): string
    {
        // We want to fail gracefully since a schema that has not been created is a valid state
        if (!$this->database->tableExists("settings")) {
            return '';
        }

        $sql = "SELECT value FROM settings WHERE name=:name";
        $result = $this->database->query($sql, ["name" => "system/last_applied_definition"]);

        if ($result->rowCount()) {
            $row = $result->fetch();
            return $row['value'];
        }

        // Not found, default to empty string
        return '';
    }

    /**
     * Set the last applied schema revision hash
     *
     * @param String $schemaHash The schema that will be set
     * @throws \RuntimeException if there is a problem with the schema
     * @return void
     */
    public function setLastAppliedSchemaHash(string $schemaHash)
    {
        if (!$this->database->tableExists("settings")) {
            throw new \RuntimeException(
                "Tried to set schma hash on a schema that does not " .
                    "have a settings table: " . $this->database->getNamespace()
            );
        }

        // First delete the old value if it already exists
        $this->database->delete("settings", ["name" => "system/last_applied_definition"]);

        $insertData = [
            "name" => "system/last_applied_definition",
            "value" => $schemaHash
        ];

        // Insert the new value
        $this->database->insert("settings", $insertData);
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
        $tableExists = $this->database->tableExists($bucketName);

        // Create or update columns
        // -----------------------------------------------

        /*
         * If this is a new table, we expect applyColumns to return true and add the
         * column definition string to $createColumns array so that the CREATE TABLE
         * statement below this can use the columns added to first create the table.
         *
         * If the table already exists then applyColumn will run an ALTER TABLE query.
         * This is done because creating the table all at once is about 2x as fast as
         * creating an empty table and altering it to add each column.
         */
        $createColumns = ($tableExists) ? false : [];

        // Loop through each column and either queue it to be added to a new table or alter existing
        foreach ($bucketDefinition['PROPERTIES'] as $columnName => $columnDefinition) {
            if (!$this->applyColumn($bucketName, $columnName, $columnDefinition, $createColumns)) {
                // Something went wrong, leave and return an error
                $this->errors[] = new Error("Error saving column $columnName in $bucketName");
                return false;
            }
        }

        // Create the table if it does not exist
        if (is_array($createColumns)) {
            $sql = "CREATE TABLE IF NOT EXISTS $bucketName(" . implode(',', $createColumns) . ")";

            // Does this table inherit?
            if (isset($bucketDefinition['INHERITS'])) {
                $sql .= " INHERITS (" . $bucketDefinition['INHERITS'] . ")";
            }

            // Create the table
            if (!$this->database->query($sql)) {
                throw new \RuntimeException("Could not create table $bucketName");
            }
        }

        // Create primary key
        if (isset($bucketDefinition['PRIMARY_KEY'])) {
            if (!$this->applyPrimaryKey($bucketName, $bucketDefinition['PRIMARY_KEY'])) {
                // Something went wrong, leave and return an error
                $this->errors[] = new Error("Error on creating primary key in $bucketName");
                return false;
            }
        }

        // Create keys if supported by the database
        if (isset($bucketDefinition['KEYS'])) {
            foreach ($bucketDefinition['KEYS'] as $keyData) {
                $this->applyForeignKey($bucketName, $keyData);
            }
        }

        // Create indexes
        if (isset($bucketDefinition['INDEXES'])) {
            foreach ($bucketDefinition['INDEXES'] as $indexData) {
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
     * @param array|bool $createColumns If new table this will be an array to add statements to
     * @return bool true on success, false on failure
     */
    private function applyColumn($tableName, $columnName, array $columnDefinition, &$createColumns = false)
    {
        // Make sure the column names are not too long
        if (strlen($columnName) > 64) {
            throw new \RuntimeException("Column name '$columnName' on table '$tableName' is too long.");
        }

        if (!empty($columnDefinition["default"])
            && $columnDefinition["default"] === "auto_increment"
            && strlen($columnName) > 61
        ) {
            throw new \RuntimeException("Auto increment column name '$columnName' on table '$tableName' is too long.");
        }

        // Return true if the column already exists
        if ($createColumns === false) {
            if ($this->database->columnExists($tableName, $columnName)) {
                return true;
            }
        }

        // Determine the column type
        if (!empty($columnDefinition["default"]) && $columnDefinition["default"] === 'auto_increment') {
            $columnType = ($columnDefinition["type"] === "bigint") ? "bigserial" : "serial";
        } elseif (!empty($columnDefinition["subtype"]) && $columnDefinition["subtype"]) {
            $columnType = $columnDefinition["type"] . " " . $columnDefinition["subtype"];
        } elseif (!empty($columnDefinition["type"])) {
            $columnType = $columnDefinition["type"];
        } else {
            throw new \RuntimeException("Could not add $columnName to $tableName because missing type " . var_export($columnDefinition, true));
        }

        // Add column defaults
        $default = "";
        if (!empty($columnDefinition["default"]) && $columnDefinition["default"] !== "auto_increment") {
            $default = " DEFAULT '{$columnDefinition["default"]}'";
        }

        // Add constraint
        $constraint = "";
        if (isset($columnDefinition['notnull']) && $columnDefinition['notnull'] === true) {
            $constraint = 'NOT NULL';
        }

        // Add constraint
        if (isset($columnDefinition['unique']) && $columnDefinition['unique'] === true) {
            $constraint .= ' UNIQUE';
        }

        /*
         * If this is a new table we do not want to run an alter, but rather just add
         * the column name so that it can be added to a create statement and return true.
         */
        if (is_array($createColumns)) {
            $createColumns[] = "{$columnName} {$columnType} $default $constraint";
            return true;
        }

        // Add column definition
        $sql = "ALTER TABLE $tableName ADD COLUMN {$columnName} {$columnType} $default $constraint";

        return ($this->database->query($sql)) ? true : false;
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
        // Get the primary key available for this table
        $primaryKey = $this->database->getPrimaryKeys($tableName);

        // If the table already has a primary key, then leave it alone
        if (sizeof($primaryKey) > 0) {
            return true;
        }

        // Normalize to an array so we can implode below
        if (!is_array($columnNameOrNames)) {
            $columnNameOrNames = [$columnNameOrNames];
        }

        // Run the SQL
        $sql = "ALTER TABLE $tableName ADD PRIMARY KEY (" . implode(', ', $columnNameOrNames) . ")";
        return ($this->database->query($sql)) ? true : false;
    }

    /**
     * Add a foreign key to a table
     *
     * @param string $tableName
     * @param array $keyDefinition
     * @return bool true on sucess, false on failure
     */
    private function applyForeignKey($tableName, $keyDefinition)
    {
        // Make sure the definition is valid
        if (empty($keyDefinition["property"])
            || !empty($keyDefinition["references_bucket"])
            || !empty($keyDefinition["references_property"])
        ) {
            $this->errors[] = new Error("Key definition for $tableName is invalid" . var_export($keyDefinition, true));
            return false;
        }

        // Set the key name
        $foreignKeyName = "{$tableName}_{$keyDefinition["property"]}_fkey";

        if (strlen($foreignKeyName) > 63) {
            throw new \RuntimeException("Key name '$foreignKeyName' on table '$tableName' is too long");
        }

        /*
         * TODO: What should we do with the keys?
         *
         * $sql = ($keyDefinition[0] === "UNIQUE") ? "CREATE UNIQUE INDEX" : "CREATE INDEX";
         * $sql .= " {$tableName}_{$foreignKeyName}_idx ON {$tableName} (" . implode(', ', $keyDefinition[1]) . ")";
         * return ($this->database->query($sql)) ? true : false;
         */
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

        if (strlen($tableName . $indexName) > 63) {
            throw new \RuntimeException("Key name '${$tableName}_$indexName' on table '$tableName' is too long");
        }

        // Return true if the index already exists
        if ($this->database->indexExists("{$tableName}_{$indexName}_idx")) {
            return true;
        }

        $sql = (isset($indexData['type']) && $indexData['type'] == 'UNIQUE') ? 'CREATE UNIQUE INDEX' : 'CREATE INDEX';
        $sql .= " {$tableName}_{$indexName}_idx ON {$tableName} ";
        if (isset($indexData['type']) && $indexData['type'] != 'UNIQUE') {
            $sql .= 'USING ' . $indexData['type'];
        }
        $sql .= "(" . implode(', ', $indexData['properties']) . ");";

        return ($this->database->query($sql)) ? true : false;
    }
}

<?php

/**
 * Create uuid and path fields for object_groupings.
 * Create indexes for the new fields
 * Populate the path with obj_type/field_name and /user_guid only if the grouping field is a private entity (like notes)
 */
use Netric\Db\Relational\RelationalDbFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);

$tableName = "object_groupings";

// Create field guid if it does not exists
if (!$db->columnExists($tableName, "guid")) {
    $db->query("ALTER TABLE $tableName ADD COLUMN guid uuid;");
}

// Create field path if it does not exists
if (!$db->columnExists($tableName, "path")) {
    $db->query("ALTER TABLE $tableName ADD COLUMN path character varying(256);");
}

// Create index for path if it does not exists
if (!$db->indexExists("{$tableName}_path_idx")) {
    $db->query("CREATE INDEX {$tableName}_path_idx
    ON $tableName (path);");
}

// Function that will execute the sql statement and setting the statement_timeout to 0
$updateGroupingsPath = function ($sql, $database) {
    // Do the same thing when updating the path
    $database->beginTransaction();

    // Do not timeout for this long query
    $database->query('set statement_timeout to 0');

    $database->query($sql);

    // Commit the transaction
    $database->commitTransaction();
};

// First create all UUIDs in the gid field
$updateGroupingsPath("UPDATE $tableName SET guid = CAST(LPAD(TO_HEX(id), 32, '0') AS UUID) WHERE guid IS NULL", $db);

// Sql statement for updating the groupings path where user id is null
$sql = "UPDATE $tableName AS groupings
    SET path = CONCAT(obj_types.name, '/', obj_fields.name)

    FROM $tableName AS obj_groupings 
    INNER JOIN app_object_types AS obj_types
    ON obj_types.id = obj_groupings.object_type_id

    INNER JOIN app_object_type_fields AS obj_fields
    ON obj_fields.id = obj_groupings.field_id

    WHERE groupings.guid = obj_groupings.guid 
    AND groupings.guid IS NOT NULL
    AND groupings.path IS NULL
    AND groupings.user_id IS NULL
    ";

$updateGroupingsPath($sql, $db);

// Sql statement for updating the groupings path for private entities (where user id is set)
$sql = "UPDATE $tableName AS groupings
    SET path = CONCAT(obj_types.name, '/', obj_fields.name, '/', groupings.user_id)

    FROM $tableName AS obj_groupings 
    INNER JOIN app_object_types AS obj_types
    ON obj_types.id = obj_groupings.object_type_id

    INNER JOIN app_object_type_fields AS obj_fields
    ON obj_fields.id = obj_groupings.field_id

    WHERE groupings.guid = obj_groupings.guid 
    AND groupings.guid IS NOT NULL
    AND groupings.path IS NULL
    AND groupings.user_id IS NOT NULL
    ";

$updateGroupingsPath($sql, $db);


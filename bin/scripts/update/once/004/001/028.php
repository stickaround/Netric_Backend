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

// Wrap this into a transaction so that we can extend the statement timeout (it takes a while)
$db->beginTransaction();

// Do not timeout for this long query
$db->query('set statement_timeout to 0');

// First create all UUIDs in the gid field
$db->query("UPDATE $tableName SET guid = CAST(LPAD(TO_HEX(id), 32, '0') AS UUID) WHERE guid IS NULL");

// Commit the transaction
$db->commitTransaction();


// Do the same thing when updating the path
$db->beginTransaction();

// Do not timeout for this long query
$db->query('set statement_timeout to 0');

// Update the groupings path
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
";
$db->query($sql);

// Commit the transaction
$db->commitTransaction();

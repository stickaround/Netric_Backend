<?php

/**
 * This is a fix for the bug issue in 029.php where it uses user_id instead of user_guid for private groupings
 */
use Netric\Db\Relational\RelationalDbFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);

$tableName = "object_groupings";

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

// Sql statement for updating the groupings path for private entities (where user guidid is set)
$sql = "UPDATE $tableName AS groupings
    SET path = CONCAT(obj_types.name, '/', obj_fields.name, '/', users.guid)

    FROM $tableName AS obj_groupings 

    INNER JOIN app_object_types AS obj_types
    ON obj_types.id = obj_groupings.object_type_id

    INNER JOIN app_object_type_fields AS obj_fields
    ON obj_fields.id = obj_groupings.field_id

    INNER JOIN objects_user AS users
    ON users.id = obj_groupings.user_id

    WHERE groupings.guid = obj_groupings.guid 
    AND groupings.guid IS NOT NULL    
    AND groupings.user_id IS NOT NULL
    ";

$updateGroupingsPath($sql, $db);


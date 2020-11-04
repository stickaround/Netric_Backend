<?php

/**
 * This file is just to initialize the .1 point in the version. All updates will
 * begin with 5/1/002.php onward.
 */

/*
 The following queries were run manually in production to upgrade to new v4 database,
 but left here for a record in case it comes in handy later

// Used to convert into to uuid in postgresql
guid = CAST(LPAD(TO_HEX(id), 32, '0') AS UUID)

select CAST(LPAD(COALESCE(TO_HEX(id), TO_HEX(object_type_id)), 32, '0') AS UUID) as guid from objects limit 10;
*/


<?php
/**
 * notification object definition
 */
$obj_revision = 2;

$isPrivate = true;
$defaultActivityLevel = 1;
$storeRevisions = true;

$obj_fields = array(
    // Textual name of the account
    'name' => array(
        'title'=>'Title',
        'type'=>'text',
        'subtype'=>'256',
        'readonly'=>false,
        'require'=>true,
    ),

    // TODO: Add the rest of the fields here
);
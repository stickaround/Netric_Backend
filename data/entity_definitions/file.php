<?php
namespace data\entity_definitions;

return array(
    'parent_field' => 'folder_id',
    'fields' => array(
        'name' => array(
            'title'=>'Name',
            'type'=>'text',
            'subtype'=>'512',
            'readonly'=>false,
        ),

        // Size in bytes
        'file_size' => array(
            'title'=>'Size',
            'type'=>'number',
            'subtype'=>'',
            'readonly'=>true,
        ),

        // The filetype extension
        'filetype' => array(
            'title'=>'Name',
            'type'=>'text',
            'subtype'=>'32',
            'readonly'=>true,
        ),

        // where the file is stored in the storage engine
        'storage_path' => array(
            'title'=>'Storage Path',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true,
        ),

        // Deprecated - path to local file on server
        'dat_local_path' => array(
            'title'=>'Lcl Path',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true,
        ),

        // Deprecated - key used on ANS server
        'dat_ans_key' => array(
            'title'=>'ANS Key',
            'type'=>'text',
            'subtype'=>'',
            'readonly'=>true,
        ),
        'folder_id' => array(
            'title'=>'Folder',
            'type'=>'object',
            'subtype'=>'folder'
        ),
        'owner_id' => array(
            'title'=>'Owner',
            'type'=>'object',
            'subtype'=>'user',
            'default'=>array("value"=>"-3", "on"=>"null"),
        ),
    ),
);

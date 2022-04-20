<?php

/**
 * Return browser views for entity of object type 'email_thread'
 */

namespace data\browser_views;

use Netric\EntityQuery\Where;
use Netric\Entity\ObjType\UserEntity;

return array(
    'email_threads' => array(
        'obj_type' => 'email_thread',
        'name' => 'Email Threads',
        'description' => '',
        'default' => true,
        'conditions' => array(
            'user' => array(
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'owner_id',
                'operator' => Where::OPERATOR_EQUAL_TO,
                'value' => UserEntity::USER_CURRENT,
            ),
        ),
        'order_by' => array(
            'date' => array(
                'field_name' => 'ts_delivered',
                'direction' => 'desc',
            ),
        ),
        'table_columns' => array('senders', 'subject', 'ts_delivered', 'is_seen', 'f_flagged', 'num_attachments', 'num_messages')
    ),
);

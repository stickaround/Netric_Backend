<?php

/**
 * Return browser views for entity of object type 'email_message'
 */

namespace data\browser_views;


return array(
    'email_messages' => array(
        'obj_type' => 'email_message',
        'name' => 'Messages',
        'description' => '',
        'default' => true,
        'order_by' => array(
            'date' => array(
                'field_name' => 'message_date',
                'direction' => 'desc',
            ),
        ),
        'table_columns' => array('subject', 'message_date', 'from', 'to', 'priority')
    ),
);

<?php
/**
 * Return browser views for entity of object type 'note'
 */
namespace objects\browser_views;

use Netric\EntityQuery\Where;

return array(
    'my_tasks'=> array(
        'obj_type' => 'task',
		'default' => true,
        'conditions' => array(
            'user' => array(
                'blogic' => Where::COMBINED_BY_AND,
                'field_name' => 'user_id',
                'operator' => Where::OPERATOR_EQUAL_TO,
                'value' => -3
            ),
			'done' => array(
				'blogic' => Where::COMBINED_BY_AND,
				'field_name' => 'done',
				'operator' => Where::OPERATOR_NOT_EQUAL_TO,
        		'value' => true
        	),
        ),
    	'order_by' => array(
			'date' => array(
				'field_name' => 'date_entered',
				'direction' => 'desc',    		
    		),
    		'deadline' => array(
				'field_name' => 'deadline',
    			'direction' => 'asc'
			),
		),
    	'table_columns' => array('name', 'project', 'priority',  'deadline', 'done', 'user_id')
    ),
	'my_tasks_due_today' => array(
		'obj_type' => 'task',
		'conditions' => array(
				'user' => array(
						'blogic' => Where::COMBINED_BY_AND,
						'field_name' => 'user_id',
						'operator' => Where::OPERATOR_EQUAL_TO,
						'value' => -3
				),
				'done' => array(
						'blogic' => Where::COMBINED_BY_AND,
						'field_name' => 'done',
						'operator' => Where::OPERATOR_NOT_EQUAL_TO,
						'value' => true
				),
				'deadline' => array(
						'blogic' => Where::COMBINED_BY_AND,
						'field_name' => 'deadline',
						'operator' => Where::OPERATOR_LESS_THAN_OR_EQUAL_TO,
						'value' => 'now'
				),
		),
		'order_by' => array(
				'date' => array(
						'field_name' => 'date_entered',
						'direction' => 'desc',
				),
				'deadline' => array(
						'field_name' => 'deadline',
						'direction' => 'asc'
				),
		),
		'table_columns' => array('name', 'project', 'priority',  'deadline', 'done', 'user_id')
	),
	'all_my_tasks' => array(
		'obj_type' => 'task',
		'conditions' => array(
				'user' => array(
						'blogic' => Where::COMBINED_BY_AND,
						'field_name' => 'user_id',
						'operator' => Where::OPERATOR_EQUAL_TO,
						'value' => -3
				),
		),
		'order_by' => array(
				'date' => array(
						'field_name' => 'date_entered',
						'direction' => 'desc',
				),
				'deadline' => array(
						'field_name' => 'deadline',
						'direction' => 'asc'
				),
		),
		'table_columns' => array('name', 'project', 'priority',  'deadline', 'done', 'user_id')
	),
	'tasks_i_have_assigned' => array(
		'obj_type' => 'task',
		'conditions' => array(
				'creator' => array(
						'blogic' => Where::COMBINED_BY_AND,
						'field_name' => 'creator_id',
						'operator' => Where::OPERATOR_EQUAL_TO,
						'value' => -3
				),
				'user' => array(
						'blogic' => Where::COMBINED_BY_AND,
						'field_name' => 'user_id',
						'operator' => Where::OPERATOR_NOT_EQUAL_TO,
						'value' => -3
				),
				'done' => array(
						'blogic' => Where::COMBINED_BY_AND,
						'field_name' => 'done',
						'operator' => Where::OPERATOR_NOT_EQUAL_TO,
						'value' => true
				),
		),
		'order_by' => array(
				'date' => array(
						'field_name' => 'date_entered',
						'direction' => 'desc',
				),
				'deadline' => array(
						'field_name' => 'deadline',
						'direction' => 'asc'
				),
		),
		'table_columns' => array('name', 'project', 'priority',  'deadline', 'done', 'user_id')
	),
	'all_incomplete_tasks' => array(
		'obj_type' => 'task',
		'conditions' => array(
				'done' => array(
						'blogic' => Where::COMBINED_BY_AND,
						'field_name' => 'done',
						'operator' => Where::OPERATOR_NOT_EQUAL_TO,
						'value' => true
				),
		),
		'order_by' => array(
				'date' => array(
						'field_name' => 'date_entered',
						'direction' => 'desc',
				),
				'deadline' => array(
						'field_name' => 'deadline',
						'direction' => 'asc'
				),
		),
		'table_columns' => array('name', 'project', 'priority',  'deadline', 'done', 'user_id')
	),
	'all_tasks' => array(
		'obj_type' => 'task',
		'order_by' => array(
				'date' => array(
						'field_name' => 'date_entered',
						'direction' => 'desc',
				),
				'deadline' => array(
						'field_name' => 'deadline',
						'direction' => 'asc'
				),
		),
		'table_columns' => array('name', 'project', 'priority',  'deadline', 'done', 'user_id')
	),
);

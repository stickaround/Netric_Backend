 -- We can probably delete this pretty soon, but it is just a record of all the
 -- manual queries run in order to move the old aereus account over to the new system
 
-- public | async_device_states     | table | postgres
-- public | async_users             | table | postgres

-- public | entity_definition       | table | postgres
INSERT INTO entity_definition(
	account_id, 
	entity_definition_id,
	name, 
	title, 
	revision, 
	f_system, 
	capped, 
	head_commit_id, 
	dacl, 
	is_private, 
	recur_rules,
	inherit_dacl_ref
) SELECT 
	'00000000-0000-0000-0000-00000000000c' as account_id, 
	CAST(LPAD(TO_HEX(id), 32, '0') AS UUID) as entity_definition_id,
	name, 
	title, 
	revision, 
	f_system, 
	capped, 
	head_commit_id, 
	dacl, 
	is_private, 
	recur_rules,
	inherit_dacl_ref
FROM acc_12.app_object_types;

-- public | entity                  | table | postgres
--------------------------------------------------------

-- Move duplicates we care about into a temp table
SELECT * INTO objects_dup FROM objects WHERE guid in (SELECT guid FROM objects WHERE f_deleted is false AND( acc_12.objects.object_type_id=23 OR acc_12.objects.object_type_id=32 OR acc_12.objects.object_type_id=74 OR acc_12.objects.object_type_id=76 OR acc_12.objects.object_type_id=77 OR acc_12.objects.object_type_id=78 OR acc_12.objects.object_type_id=58 OR acc_12.objects.object_type_id=31595 OR acc_12.objects.object_type_id=31596 OR acc_12.objects.object_type_id=34 OR acc_12.objects.object_type_id=74 OR acc_12.objects.object_type_id=37 OR acc_12.objects.object_type_id=87 OR acc_12.objects.object_type_id=3 OR acc_12.objects.object_type_id=3 OR acc_12.objects.object_type_id=26 OR acc_12.objects.object_type_id=2 OR acc_12.objects.object_type_id=47 OR acc_12.objects.object_type_id=27 OR acc_12.objects.object_type_id=1 OR acc_12.objects.object_type_id=56 OR acc_12.objects.object_type_id=63 OR acc_12.objects.object_type_id=64 OR acc_12.objects.object_type_id=82 OR acc_12.objects.object_type_id=43 OR acc_12.objects.object_type_id=54 OR acc_12.objects.object_type_id=36 OR acc_12.objects.object_type_id=40 OR acc_12.objects.object_type_id=44 OR acc_12.objects.object_type_id=38 OR acc_12.objects.object_type_id=35 OR acc_12.objects.object_type_id=25 OR acc_12.objects.object_type_id=50) GROUP BY guid HAVING count(*) > 1);

-- Now copy entities
INSERT INTO entity (
	account_id,
	entity_id,
	uname,
	entity_definition_id,
	ts_entered,
	ts_updated,
	f_deleted,
	commit_id,
	field_data,
	schema_version,
	tsv_fulltext
) SELECT
	'00000000-0000-0000-0000-00000000000c' as account_id,
	guid,
	uname,
	CAST(LPAD(TO_HEX(object_type_id), 32, '0') AS UUID) as entity_definition_id,
	ts_entered,
	ts_updated,
	f_deleted,
	commit_id,
	field_data,
	'4' as schema_version,
	tsv_fulltext
FROM acc_12.objects
WHERE
	f_deleted is false AND
	(
		acc_12.objects.object_type_id=23 OR
		acc_12.objects.object_type_id=32 OR
		acc_12.objects.object_type_id=74 OR
		acc_12.objects.object_type_id=76 OR
		acc_12.objects.object_type_id=77 OR
		acc_12.objects.object_type_id=78 OR
		acc_12.objects.object_type_id=58 OR
		acc_12.objects.object_type_id=31595 OR
		acc_12.objects.object_type_id=31596 OR
		acc_12.objects.object_type_id=34 OR
		acc_12.objects.object_type_id=74 OR
		acc_12.objects.object_type_id=37 OR
		acc_12.objects.object_type_id=87 OR
		acc_12.objects.object_type_id=3 OR
		acc_12.objects.object_type_id=3 OR
		acc_12.objects.object_type_id=26 OR
		acc_12.objects.object_type_id=2 OR
		acc_12.objects.object_type_id=47 OR
		acc_12.objects.object_type_id=27 OR
		acc_12.objects.object_type_id=1 OR
		acc_12.objects.object_type_id=56 OR
		acc_12.objects.object_type_id=63 OR
		acc_12.objects.object_type_id=64 OR
		acc_12.objects.object_type_id=82 OR
		acc_12.objects.object_type_id=43 OR
		acc_12.objects.object_type_id=54 OR
		acc_12.objects.object_type_id=36 OR
		acc_12.objects.object_type_id=40 OR
		acc_12.objects.object_type_id=44 OR
		acc_12.objects.object_type_id=38 OR
		acc_12.objects.object_type_id=35 OR
		acc_12.objects.object_type_id=25 OR
		acc_12.objects.object_type_id=50
	) AND
	guid NOT IN (select guid from acc_12.objects_dup);


-- public | entity_group            | table | postgres
INSERT INTO entity_group (
	account_id,
	group_id,
	name,
	entity_definition_id,
	color,
	sort_order,
	f_system,
	f_closed,
	commit_id,
	filter_values,
	path
) SELECT
	'00000000-0000-0000-0000-00000000000c' as account_id,
	guid,
	name,
	CAST(LPAD(TO_HEX(object_type_id), 32, '0') AS UUID) as entity_definition_id,
	color,
	sort_order,
	f_system,
	f_closed,
	commit_id,
	filter_values,
	path
FROM acc_12.object_groupings;


 

 public | entity_sync_collection  | table | postgres
 public | entity_sync_commit_head | table | postgres
 public | entity_sync_export      | table | postgres
 public | entity_sync_import      | table | postgres
 public | entity_sync_partner     | table | postgres

-- no
 public | settings                | table | postgres
 public | entity_form             | table | postgres





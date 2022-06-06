-- We are renaming "infocenter_document" to "document"
UPDATE entity_definition SET name='wall_post' WHERE name='status_update';

-- Update the obj_type in the entities
UPDATE 
    entity 
  SET 
    field_data=field_data || jsonb_build_object('obj_type', 'wall_post') 
  WHERE field_data->>'obj_type'='status_update';
-- Copy old infocenter values
UPDATE 
    entity 
  SET 
    field_data=field_data || jsonb_build_object('obj_type', 'document') 
  WHERE field_data->>'obj_type'='infocenter_document';
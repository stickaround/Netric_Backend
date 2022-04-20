-- remote old f_seen index if exists
DROP INDEX IF EXISTS idx_entity_f_seen;

-- add index is_seen
CREATE INDEX IF NOT EXISTS idx_entity_is_seen ON entity USING BTREE 
    (((field_data->'is_seen')::boolean), account_id, entity_definition_id)
    WHERE field_data->'is_seen' IS NOT NULL AND field_data->>'is_seen'!='';

-- Copy old f_seen values into new field is_seen
UPDATE 
        entity 
    SET 
        field_data=field_data || jsonb_build_object('is_seen', true) 
    WHERE field_data->>'f_seen'='true';
UPDATE 
        entity 
    SET 
        field_data=field_data || jsonb_build_object('is_seen', false) 
    WHERE field_data->>'f_seen'='false';
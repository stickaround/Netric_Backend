-- index seen_by
CREATE INDEX IF NOT EXISTS idx_entity_seen_by 
    ON entity USING GIN ((field_data->'seen_by') jsonb_path_ops);

-- index f_seen
CREATE INDEX IF NOT EXISTS idx_entity_f_seen ON entity USING BTREE 
    (((field_data->'f_seen')::boolean), account_id, entity_definition_id)
    WHERE field_data->'f_seen' IS NOT NULL AND field_data->>'f_seen'!='';

-- index chat_room for messages
CREATE INDEX IF NOT EXISTS idx_entity_chat_room ON entity USING BTREE 
    (((field_data->'chat_room')::text), account_id, entity_definition_id)
    WHERE field_data->'chat_room' IS NOT NULL AND field_data->>'chat_room'!='';

-- index obj_reference - mostly for activity and comment lists
CREATE INDEX IF NOT EXISTS idx_entity_obj_reference ON entity USING BTREE 
    (((field_data->'obj_reference')::text), account_id, entity_definition_id)
    WHERE field_data->'obj_reference' IS NOT NULL AND field_data->>'obj_reference'!='';
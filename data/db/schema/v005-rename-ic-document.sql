-- We are renaming "infocenter_document" to "document"
UPDATE entity_definition SET name='document' WHERE name='infocenter_document';
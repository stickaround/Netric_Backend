-- worker_job is no longe ra supported entity type - we handle it throug jobqueue
DELETE FROM entity WHERE entity_definition_id IN (
    SELECT entity_definition_id FROM entity_definition WHERE name='worker_job'
);
DELETE FROM entity_definition WHERE name='worker_job';
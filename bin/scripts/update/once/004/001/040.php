<?php
/*
insert into entities(
    id, 
    account_id, 
    guid, 
    uname, 
    object_type_id, 
    ts_entered, 
    ts_updated, 
    f_deleted, 
    commit_id, 
    field_data, 
    tsv_fulltext
) 
select 
    id, 
    12 as account_id, 
    guid, 
    uname, 
    object_type_id, 
    ts_entered, 
    ts_updated, 
    false, 
    commit_id, 
    field_data, 
    tsv_fulltext 
from 
    objects 
where 
    object_type_id=25 and f_deleted=false;
*/
INSERT INTO account (account_id, name, ts_started)
    VALUES('00000000-0000-0000-0000-00000000000c', 'aereus_main_account', 'now')
    ON CONFLICT (account_id) DO NOTHING;
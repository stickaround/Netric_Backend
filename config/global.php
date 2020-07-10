<?php

return [
    // Determine if secure pages should be offered (not forced) in https
    'use_https' => true,
    // This is the root domain. Ant Accounts usually use third levels to parse
    // accounts which will be defined in {localhost} below
    'localhost_root' => 'netric.com',
    // Default account to use if we cannot find any other accounts
    'default_account' => 'aereus',
    // This is the url of the netric app. This is usually used when creating the
    // link of an entity in the email notification contents.
    'application_url' => 'app.netric.com',
    // Set application path - default to constant defined in AntConfig.php
    'application_path' => '/var/www/html',
    // Set path to store data
    'data_path' => '/var/www/html/data',
    // Limit accounts with certain version
    'version' => 'beta',
    // Set the id file used to track to netric daemon
    'pidfile' => '/var/run/netricd',
    // Where secret files are stored
    'vault_dir' => '/var/run/secrets',
    // Log settings
    'log' => [
        'writer' => 'gelf',
        // Set log level - 5 = NOTICE (DEFAULT), 7 = DEBUG
        'level' => 6,
        // Optional remote server for logging if writer supports it
        'server' => '10.4.2.134',
        // Optional remote server port - 12201=gelf
        'port' => '12201',
    ],
    // Email server settings
    'email' => [
        // If set to true, no emails will be sent
        'supress' => false,
        'mode' => 'smtp',
        'server' => "in-v3.mailjet.com",
        'dropbox' => "incoming@sys.netric.com",
        'dropbox_catchall' => "@sys.netric.com",
        'noreply' => "no-reply@netric.com",
        'username' => "b112da3342c636e002eda3c51355a51f",
        'password' => "834e70cabc642b54d30de418809066c8",
        'port' => 587,

        // Set to imap for system backend. If these options are non-null then
        // by default ANT will retrieve email messages from this backend using the
        // user password and the email_address of the email account.
        'default_type' => "imap",
        'backend_host' => "10.4.26.26",

        // These alternate settings will be used when bulk email messages are sent to try
        // and keep spam/blacklist issues on our main SMTP servers to a minimal
        'bulk_server' => "",
        'bulk_user' => "",
        'bulk_password' => "",
        'bulk_port' => "",
    ],
    // Stats service
    'stats' => [
        'enabled' => true,
        'engine' => "STATSD",
        'host' => "10.4.27.82",
        'port' => "8125",
        'prefix' => "netric.production",
    ],
    // Database settigs
    'db' => [
        'type' => "pgsql",
        'port' => "5432",
        'host' => "10.4.6.22",
        'user' => "aereus",
        'password' => "kryptos78",
        'syshost' => "10.4.26.26",
        'sysdb' => "antsystem",
        'accdb' => "netric",
    ],
    // Files settings
    'files' => [
        'store' => 'mogile',
        'server' => '10.4.26.26',
        'account' => 'netric',
        'password' => 'n/a',
        'port' => 7001,
    ],
    // Cache settings
    'cache' => [
        'driver' => 'memcache',
        'host' => '10.4.26.26',
    ],
    // Profiler settings
    'profile' => [
        // If enabled the xhprof profiles will be created for every request made
        'enabled' => false,
        // minimum time to record in micrseconds - if 0 then everything will be recorded.
        // This can have big performance impact. Default is to log requests longer than 1 second.
        'min_wall' => 1000000,
        'save_profiles' => false,
    ],
    // Background worker settings
    'workers' => [
        'background_enabled' => true,
        'queue' => 'gearman',
        'server' => '10.4.26.26',
    ],
];
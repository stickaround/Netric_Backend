<?php

use \net\authorize\api\constants\ANetEnvironment;

return [
    // This is the url of the netric app. This is usually used when creating
    // the link of an entity in the email notification contents.
    'application_url' => 'http://localhost:8080',
    // Where secret files are stored - we keep this in the source code for local development
    'vault_dir' => "/var/www/html/data/vault_secrets",
    // Log settings
    'log' => [
        'writer' => 'php_error',
        // Set log level - 5 = NOTICE (DEFAULT), 7 = DEBUG
        'level' => 7,
    ],
    // Email server settings
    'email' => [
        // Do not actually send any email in development mode
        'supress' => true,
        'server' => 'smtp',
        'backend_host' => 'mail',
    ],
    // Stats service
    'stats' => [
        'enabled' => false,
    ],
    // Database settigs
    'db' => [
        'type' => "pgsql",
        'port' => "5432",
        'host' => "db1",
        'user' => "vagrant",
        'password' => "vagrant",
        'syshost' => "db1",
        'dbname' => 'netric',
    ],
    // Files settings
    'files' => [
        'store' => 'mogile',
        'server' => 'mogilefs',
        'account' => 'netric',
        'password' => 'n/a',
        'port' => 7001,
    ],
    // Cache settings
    'cache' => [
        'driver' => 'memcache',
        'host' => 'memcached',
    ],
    // Profiler settings
    'profile' => [
        // If enabled the xhprof profiles will be created for every request made
        'enabled' => false,
        // minimum time to record in micrseconds - if 0 then everything will be recorded.
        // This can have big performance impact. Default is to log requests longer than 1 second.
        'min_wall' => 1000,
        'save_profiles' => true,
    ],
    // Background worker settings
    'workers' => [
        'queue' => 'gearman',
        'server' => 'gearmand',
    ],
    'billing' => [
        'anet_url' => ANetEnvironment::SANDBOX,
        'anet_login' => '47zCW38But',
        // The key is in the vault
    ]
];

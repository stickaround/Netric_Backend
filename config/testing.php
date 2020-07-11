<?php

return [
    // The default account (db) to load if no third level domain
    'default_account' => 'local',
    // Inerval vault file
    'vault_dir' => "/var/www/html/data/vault_secrets",
    // Log settings
    'log' => [
        'writer' => 'null',
    ],
    // Email server settings
    'email' => [
        // If set to true, no emails will be sent
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
        'accdb' => "netricacc",
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
    // Background worker settings
    'workers' => [
        'background_enabled' => false,
        'queue' => 'gearman',
        'server' => 'gearmand',
    ],
];

<?php

use \net\authorize\api\constants\ANetEnvironment;

return [
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
        'dbname' => 'netric',
    ],
    // Files settings
    'files' => [
        'osAccount' => 'netric',
        'osServer' => 'objectstorage',
        'osSecret' => 'YCzL6bmR9rNt5MTWuRpQukE7BQ7b9PYm',
        //'store' => 'mogile',
        //'server' => '10.4.26.26',
        'mogileServer' => 'mogilefs',
        'mogileAccount' => 'netric',
        // 'password' => 'n/a',
        // 'port' => 7001,
    ],
    // Cache settings
    'cache' => [
        'driver' => 'redis',
        'host' => 'redis',
    ],
    // Background worker settings
    'workers' => [
        // The in-memory worker is really just an envent queue that
        // executes the 'background' jobs immediately
        'queue' => 'memory',
        // We leave this for unit tests since we test gearman
        'server' => 'gearmand',
    ],
    'notifications' => [
        'push' => [
            'server' => 'notificationpusher',
            'account' => 'test',
            'secret' => 'testsecret',
        ],
    ],
    'billing' => [
        'anet_url' => ANetEnvironment::SANDBOX,
        'anet_login' => '47zCW38But',
        // The key is in the vault
    ]
];

<?php

use \net\authorize\api\constants\ANetEnvironment;

return [
    'localhost_root' => 'integ.netric.com',
    // Log settings
    // Log settings
    'log' => [
        'writer' => 'gelf',
        // Set log level - 5 = NOTICE (DEFAULT), 7 = DEBUG
        'level' => 8,
        // Optional remote server for logging if writer supports it
        'server' => 'dev1.aereus.com',
        // Optional remote server port - 12201=gelf
        'port' => '12201',
    ],
    // Stats service
    'stats' => [
        'enabled' => true,
        'engine' => "STATSD",
        'host' => "dev1.aereus.com",
        'port' => "8125",
        'prefix' => "netric.integration",
    ],
    // Database settigs
    'db' => [
        'type' => "pgsql",
        'port' => "5432",
        'host' => "dat1-int-locsea.aereus.com",
        'user' => "aereus",
        'password' => "kryptos78",
        'syshost' => "dat1-int-locsea.aereus.com",
        'dbname' => 'netric',
    ],
    // Files settings
    'files' => [
        'store' => 'mogile',
        'server' => 'dev1.aereus.com',
        'account' => 'netric',
        'password' => 'n/a',
        'port' => 7001,
    ],
    // Cache settings
    'cache' => [
        'driver' => 'memcache',
        'host' => '1dev1.aereus.com',
    ],
    // Background worker settings
    'workers' => [
        'queue' => 'gearman',
        'server' => 'dev1.aereus.com',
    ],
    'billing' => [
        'anet_url' => ANetEnvironment::SANDBOX,
        'anet_login' => '47zCW38But',
        // The key is in the vault
    ]
];

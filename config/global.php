<?php

use \net\authorize\api\constants\ANetEnvironment;
use Netric\FileSystem\FileStore\ObjectStorageStoreFactory;

return [
    // Determine if secure pages should be offered (not forced) in https
    'use_https' => true,
    // This is used any time we need to prepend an account name
    // for something like email mailboxes: aereus.netric.com will route
    // to the 'aereus' account in netric.
    'localhost_root' => 'netric.com',
    // Unique application name
    'application_name' => 'netri.svc',
    // This is the url of the netric app. This is usually used when creating the
    // link of an entity in the email notification contents.
    'application_url' => 'https://app.netric.com',
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
    // Netric account used for billing and support - netric supported by netric
    'main_account_id' => '00000000-0000-0000-0000-00000000000c',
    // Log settings
    'log' => [
        'writer' => 'gelf',
        // Set log level - 5 = NOTICE, 6 = INFO, 7 = DEBUG
        'level' => 6,
        // Optional remote server for logging if writer supports it
        'server' => 'pvt-logstash.aereus.com',
        // Optional remote server port - 12201=gelf
        'port' => '12201',
    ],
    // Email server settings
    'email' => [
        // If set to true, no emails will be sent
        'supress' => false,
        'mode' => 'smtp',
        // Service name for smtp server
        'server' => "smtp_netric",
        'username' => 'outbound@aereus.com',
        'password' => '5Y2mgv4f5G2ExeRP',
        'dropbox' => "incoming@sys.netric.com",
        'dropbox_catchall' => "@sys.netric.com",
        'noreply' => "no-reply@netric.com",
        'port' => 25,
        // 'server' => "in-v3.mailjet.com",
        // 'dropbox' => "incoming@sys.netric.com",
        // 'dropbox_catchall' => "@sys.netric.com",
        // 'noreply' => "no-reply@netric.com",
        // 'username' => "b112da3342c636e002eda3c51355a51f",
        // 'password' => "834e70cabc642b54d30de418809066c8",
        // 'port' => 587,

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
        'host' => "pvt-statsd.aereus.com",
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
        'dbname' => 'aereus_ant',
        'syshost' => "10.4.26.26",
    ],
    // Files settings
    'files' => [
        'store' => ObjectStorageStoreFactory::class,
        'osAccount' => 'netric',
        'osServer' => 'objectstorage',
        'osSecret' => 'YCzL6bmR9rNt5MTWuRpQukE7BQ7b9PYm',
        // This will be used to generate links with tokens so clients can download files.
        // It is preferrable to have files on a different domain than the app so that
        // we do not max out connections when there is a lot of media to load.
        'publicServer' => 'https://api.netric.com',
        //'store' => 'mogile',
        //'server' => '10.4.26.26',
        'mogileServer' => 'mogilefs_legacy',
        'mogileAccount' => 'netric',
        // 'password' => 'n/a',
        'mogilePort' => 7001,
    ],
    // Cache settings
    'cache' => [
        'driver' => 'redis',
        'host' => 'redis',
    ],
    'notifications' => [
        'push' => [
            'server' => 'notificationpusher',
            'account' => 'netric',
            'secret' => 'YCzL6bmR9rNt5MTWuRpQukE7BQ7b9PYm',
        ],
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
        'queue' => 'jobqueue',
        // on netric_service network in swarm
        'server' => 'jobqueue_svc',
        // Worker clients run in a separate container and need to make http calls to the service
        'worker_gearman' => "gearman",
        'service_name' => 'netricservice'
    ],
    'billing' => [
        'anet_url' => ANetEnvironment::PRODUCTION,
        'anet_login' => '6yEB38QFsp3E',
        // The key is in the vault
    ]
];
